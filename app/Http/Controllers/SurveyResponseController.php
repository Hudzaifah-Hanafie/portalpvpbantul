<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\SurveyResponseDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

class SurveyResponseController extends Controller
{
    public function show(Survey $survey)
    {
        $survey->load(['sections.questions.options', 'questions.options', 'skipRules'])->loadCount('responses');
        if (data_get($survey->settings, 'shuffle_questions')) {
            $survey->setRelation('questions', $survey->questions->shuffle()->values());
        }
        $isOpen = $survey->isOpen();
        $draft = SurveyResponseDraft::where('survey_id', $survey->id)
            ->where(function ($q) {
                if (auth()->check()) {
                    $q->where('user_id', auth()->id());
                }
                $q->orWhere('session_id', session()->getId());
            })
            ->latest('saved_at')
            ->first();

        return view('surveys.show', [
            'survey' => $survey,
            'isOpen' => $isOpen,
            'draftData' => $draft?->data ?? [],
        ]);
    }

    public function embed(string $token)
    {
        $survey = Survey::where('embed_token', $token)->firstOrFail();
        if (! $survey->allow_embed) {
            abort(404);
        }
        return $this->show($survey);
    }

    public function store(Request $request, Survey $survey)
    {
        $survey->load(['questions.options', 'skipRules']);

        if (! $survey->isOpen()) {
            return back()->with('error', 'Survey ini sudah ditutup untuk respons baru.');
        }

        if ($survey->require_login && ! auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login untuk mengisi survey ini.');
        }

        if (! $survey->allow_multiple_responses && $this->hasResponded($survey, $request)) {
            return back()->withInput()->with('error', 'Anda sudah pernah mengirim respons untuk survey ini.');
        }

        if ($request->filled('hp_token')) {
            return back()->withInput()->with('error', 'Terjadi kesalahan, silakan coba lagi.');
        }

        [$rules, $messages] = $this->buildValidationRules($survey);
        // binding optional: class_id & instructor_id serta survey_instance_id (untuk survey kelas/instruktur)
        $rules['class_id'] = 'nullable|exists:course_classes,id';
        $rules['instructor_id'] = 'nullable|exists:users,id';
        $rules['survey_instance_id'] = [
            'nullable',
            Rule::exists('survey_instances', 'id')->where('survey_id', $survey->id),
        ];
        $validated = $request->validate($rules, $messages);
        $this->verifyRecaptcha($request);
        $answersInput = $validated['responses'] ?? [];
        $instanceId = $validated['survey_instance_id'] ?? null;
        $classId = $validated['class_id'] ?? null;
        $instructorId = $validated['instructor_id'] ?? null;

        DB::transaction(function () use ($survey, $answersInput, $request, $instanceId, $classId, $instructorId) {
            $boundInstanceId = $instanceId;
            $boundClassId = $classId;
            $boundInstructorId = $instructorId;

            // Jika instance diberikan, tarik binding class/instructor dari instance
            if ($boundInstanceId) {
                $instance = \App\Models\SurveyInstance::whereKey($boundInstanceId)
                    ->where('survey_id', $survey->id)
                    ->first();
                if ($instance && $instance->survey_id === $survey->id) {
                    $boundClassId = $boundClassId ?: $instance->course_class_id;
                    $boundInstructorId = $boundInstructorId ?: $instance->instructor_id;
                } else {
                    $boundInstanceId = null;
                }
            }

            $response = SurveyResponse::create([
                'survey_id' => $survey->id,
                'survey_instance_id' => $boundInstanceId,
                'user_id' => auth()->id(),
                'course_class_id' => $boundClassId,
                'instructor_id' => $boundInstructorId,
                'session_id' => $request->session()->getId(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'submitted_at' => now(),
                'meta' => [
                    'referer' => $request->headers->get('referer'),
                ],
                'audit' => [
                    'device' => $request->header('User-Agent'),
                    'referrer' => $request->headers->get('referer'),
                    'fingerprint' => substr(hash('sha256', $request->ip() . $request->userAgent()), 0, 16),
                    'finished_at' => now(),
                ],
            ]);

            $optionMap = $survey->questions->flatMap(function ($question) {
                return $question->options->mapWithKeys(fn ($option) => [$option->id => $option]);
            });

            foreach ($survey->questions as $question) {
                $value = $answersInput[$question->id] ?? null;
                $answerData = [
                    'survey_question_id' => $question->id,
                ];

                switch ($question->type) {
                    case 'choice_single':
                    case 'dropdown':
                    case 'choice_single_other':
                        $optionId = $value ? [$value] : [];
                        $answerData['selected_option_ids'] = $optionId;
                        $answerData['answer_text'] = $value && isset($optionMap[$value]) ? $optionMap[$value]->label : null;
                        if ($question->type === 'choice_single_other') {
                            $answerData['answer_text'] = $request->input("responses_other.{$question->id}") ?: $answerData['answer_text'];
                        }
                        break;
                    case 'choice_multiple':
                        $selected = array_values(array_filter((array) $value));
                        $answerData['selected_option_ids'] = $selected;
                        $labels = collect($selected)->map(fn ($id) => $optionMap[$id]->label ?? null)->filter()->values();
                        $answerData['answer_text'] = $labels->implode(', ');
                        break;
                    case 'grid_single':
                    case 'grid_multiple':
                        $answerData['answer_json'] = $value;
                        break;
                    case 'linear_scale':
                    case 'rating':
                        $answerData['answer_numeric'] = $value !== null ? (float) $value : null;
                        $answerData['answer_text'] = $value !== null ? (string) $value : null;
                        break;
                    case 'file_upload':
                        if ($request->hasFile("responses.{$question->id}")) {
                            $file = $request->file("responses.{$question->id}");
                            // simpan di storage privat untuk menghindari akses publik langsung
                            $path = $file->store('survey_uploads', 'local');
                            $answerData['file_path'] = $path;
                            $answerData['answer_text'] = $file->getClientOriginalName();
                            $this->scanAttachment($path);
                        }
                        break;
                    case 'date':
                    case 'time':
                        $answerData['answer_text'] = $value ? (string) $value : null;
                        break;
                    case 'long_text':
                    case 'short_text':
                    default:
                        $answerData['answer_text'] = $value ? trim((string) $value) : null;
                        break;
                }

                $response->answers()->create($answerData);
            }

            $this->dispatchWebhooks($survey, $response);
            SurveyResponseDraft::where('survey_id', $survey->id)
                ->where(function ($q) use ($request) {
                    if (auth()->check()) {
                        $q->where('user_id', auth()->id());
                    }
                    $q->orWhere('session_id', $request->session()->getId());
                })
                ->delete();
        });

        return redirect()
            ->route('surveys.show', $survey)
            ->with('success', $survey->thank_you_message ?: 'Terima kasih! Respons Anda sudah tercatat.');
    }

    private function hasResponded(Survey $survey, Request $request): bool
    {
        return SurveyResponse::where('survey_id', $survey->id)
            ->where(function ($query) use ($request) {
                if (auth()->check()) {
                    $query->where('user_id', auth()->id());
                }
                $query->orWhere('session_id', $request->session()->getId());
                $query->orWhere('ip_address', $request->ip());
            })
            ->exists();
    }

    private function buildValidationRules(Survey $survey): array
    {
        $rules = [
            'responses' => 'array',
            'responses_other' => 'array',
            'hp_token' => 'nullable|string|size:0',
        ];
        $messages = [];
        $provider = config('survey.captcha_provider', 'recaptcha');
        $hasCaptcha = match ($provider) {
            'hcaptcha' => config('services.hcaptcha.site_key') && config('services.hcaptcha.secret_key'),
            'turnstile' => config('services.turnstile.site_key') && config('services.turnstile.secret_key'),
            default => config('services.recaptcha.site_key') && config('services.recaptcha.secret_key'),
        };
        if ($hasCaptcha) {
            $rules['g-recaptcha-response'] = ['required', 'string'];
            $messages['g-recaptcha-response.required'] = 'Verifikasi captcha diperlukan.';
        }

        foreach ($survey->questions as $question) {
            $key = "responses.{$question->id}";
            $requiredRule = $question->is_required ? 'required' : 'nullable';

            switch ($question->type) {
                case 'short_text':
                case 'long_text':
                    $max = $question->settings['max_length'] ?? ($question->type === 'short_text' ? 255 : 2000);
                    $rules[$key] = [$requiredRule, 'string', 'max:' . $max];
                    if ($question->validation) {
                        if (! empty($question->validation['regex'])) {
                            $rules[$key][] = 'regex:' . $question->validation['regex'];
                        }
                        if (($question->validation['format'] ?? '') === 'email') {
                            $rules[$key][] = 'email';
                        }
                        if (($question->validation['format'] ?? '') === 'phone') {
                            $rules[$key][] = 'regex:/^[0-9+\\-()\\s]+$/';
                        }
                    }
                    break;
                case 'choice_single':
                case 'dropdown':
                case 'choice_single_other':
                    $rules[$key] = [$requiredRule];
                    $rules[$key][] = function ($attribute, $value, $fail) use ($question) {
                        if ($value === '__other') {
                            return;
                        }
                        $exists = \App\Models\SurveyQuestionOption::where('survey_question_id', $question->id)->where('id', $value)->exists();
                        if (! $exists) {
                            $fail('Jawaban tidak valid.');
                        }
                    };
                    if ($question->type === 'choice_single_other') {
                        $rules["responses_other.{$question->id}"] = ['nullable', 'string', 'max:255'];
                    }
                    break;
                case 'choice_multiple':
                    $rules[$key] = array_filter([
                        $requiredRule,
                        'array',
                        $question->is_required ? 'min:1' : null,
                        $question->settings['min_choices'] ?? null ? 'min:' . $question->settings['min_choices'] : null,
                        $question->settings['max_choices'] ?? null ? 'max:' . $question->settings['max_choices'] : null,
                    ]);
                    $rules["{$key}.*"] = [
                        'uuid',
                        Rule::exists('survey_question_options', 'id')->where('survey_question_id', $question->id),
                    ];
                    break;
                case 'grid_single':
                    $rules[$key] = [$requiredRule, 'array'];
                    break;
                case 'grid_multiple':
                    $rules[$key] = [$requiredRule, 'array'];
                    $rules["{$key}.*"] = ['array'];
                    break;
                case 'linear_scale':
                case 'rating':
                    $min = $question->settings['min'] ?? 1;
                    $max = $question->settings['max'] ?? 5;
                    $rules[$key] = [$requiredRule, 'numeric', "min:$min", "max:$max"];
                    break;
                case 'file_upload':
                    $globalMaxMb = (int) config('survey.file_max_mb', 20);
                    $maxPerQuestion = (int) ($question->settings['max_size'] ?? $globalMaxMb);
                    $maxSize = min($globalMaxMb, $maxPerQuestion) * 1024;
                    $mime = $question->settings['mime'] ?? config('survey.file_mimes', 'jpeg,png,pdf,doc,docx');
                    $rules[$key] = [$requiredRule, 'file', "max:$maxSize", "mimes:$mime"];
                    break;
                case 'date':
                    $rules[$key] = [$requiredRule, 'date'];
                    break;
                case 'time':
                    $rules[$key] = [$requiredRule, 'date_format:H:i'];
                    break;
                default:
                    $rules[$key] = [$requiredRule];
                    break;
            }

            if ($question->is_required) {
                $messages["{$key}.required"] = "Pertanyaan \"{$question->question}\" wajib diisi.";
            }
        }

        return [$rules, $messages];
    }

    private function dispatchWebhooks(Survey $survey, SurveyResponse $response): void
    {
        $payload = [
            'survey' => $survey->title,
            'survey_slug' => $survey->slug,
            'response_id' => $response->id,
            'submitted_at' => $response->submitted_at,
            'user' => optional($response->user)->email,
        ];

        if ($url = config('survey.webhooks.slack')) {
            Http::post($url, [
                'text' => "*Survey:* {$survey->title}\n*Response:* {$response->id}\nAt: {$response->submitted_at}",
            ]);
        }

        if ($url = config('survey.webhooks.sheets')) {
            Http::post($url, $payload);
        }

        if ($token = config('survey.telegram.bot_token')) {
            $chatId = config('survey.telegram.chat_id');
            if ($chatId) {
                Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => "Survey: {$survey->title}\nResponse: {$response->id}\nAt: {$response->submitted_at}",
                ]);
            }
        }
    }

    public function saveDraft(Request $request, Survey $survey)
    {
        $survey->load('questions');
        $data = $request->validate(['responses' => 'array']);
        $draft = SurveyResponseDraft::firstOrNew([
            'survey_id' => $survey->id,
            'user_id' => auth()->id(),
            'session_id' => $request->session()->getId(),
        ]);
        $draft->data = $data['responses'] ?? [];
        $draft->saved_at = now();
        $draft->save();

        return response()->json(['status' => 'ok']);
    }

    private function verifyRecaptcha(Request $request): void
    {
        $provider = config('survey.captcha_provider', 'recaptcha');
        $secret = match ($provider) {
            'hcaptcha' => config('services.hcaptcha.secret_key'),
            'turnstile' => config('services.turnstile.secret_key'),
            default => config('services.recaptcha.secret_key'),
        };
        if (! $secret) {
            return;
        }
        $token = $request->input('g-recaptcha-response') ?? $request->input('h-captcha-response') ?? $request->input('cf-turnstile-response');
        if (! $token) {
            abort(422, 'Captcha gagal divalidasi.');
        }

        [$endpoint, $tokenField] = match ($provider) {
            'hcaptcha' => ['https://hcaptcha.com/siteverify', 'response'],
            'turnstile' => ['https://challenges.cloudflare.com/turnstile/v0/siteverify', 'response'],
            default => ['https://www.google.com/recaptcha/api/siteverify', 'response'],
        };

        $response = Http::asForm()->post($endpoint, [
            'secret' => $secret,
            $tokenField => $token,
            'remoteip' => $request->ip(),
        ]);

        $success = data_get($response->json(), 'success');
        if (! $response->ok() || ! $success) {
            abort(422, 'Captcha tidak valid. Silakan coba lagi.');
        }
    }

    private function scanAttachment(string $path): void
    {
        $webhook = config('survey.webhooks.attachment_scan');
        if (! $webhook) {
            return;
        }
        try {
            Http::post($webhook, ['path' => $path]);
        } catch (\Throwable $e) {
            // silent fail; could log if needed
        }
    }
}
