<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\SurveyResponsesExport;
use App\Models\Survey;
use App\Models\SurveyVersion;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionOption;
use App\Models\SurveyCollaborator;
use App\Models\SurveyAnswer;
use App\Models\User;
use App\Services\SurveyService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SurveyController extends Controller
{
    public function __construct(private SurveyService $surveyService)
    {
        $this->middleware('permission:manage-surveys')->except('analytics');
        $this->middleware('permission:view-survey-analytics')->only('analytics');
    }

    public function index()
    {
        $user = auth()->user();
        $surveys = Survey::withCount('responses')
            ->when(! $user->can('manage-surveys'), function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('collaborators', fn($c) => $c->where('user_id', $user->id));
            })
            ->latest()
            ->paginate(15);

        return view('admin.surveys.index', compact('surveys'));
    }

    public function create()
    {
        $survey = new Survey([
            'is_active' => true,
            'allow_multiple_responses' => true,
            'show_progress' => true,
        ]);

        return view('admin.surveys.form', [
            'survey' => $survey,
            'questions' => collect(),
            'sections' => collect(),
            'skipRules' => collect(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->surveyService->validateSurvey($request);
        $sections = $this->surveyService->validateSectionsPayload($request->input('sections_payload'));
        $questions = $this->surveyService->validateQuestionsPayload($request->input('questions_payload'), $sections);
        $skipRules = $this->surveyService->validateSkipRulesPayload($request->input('skip_rules_payload'), $sections, $questions);

        DB::transaction(function () use ($data, $sections, $questions, $skipRules, &$survey) {
            $survey = Survey::create($data);
            $sectionMap = $this->surveyService->syncSections($survey, $sections);
            $this->surveyService->syncQuestions($survey, $questions, $sectionMap);
            $this->surveyService->syncSkipRules($survey, $skipRules, $sectionMap);
            $this->surveyService->storeVersion($survey, 'created');
        });

        return redirect()->route('admin.surveys.index')->with('success', 'Survey berhasil dibuat.');
    }

    public function edit(Survey $survey)
    {
        $this->surveyService->authorizeSurvey($survey, 'editor');
        $survey->load(['sections.questions.options', 'skipRules', 'versions' => fn($q) => $q->latest()->take(5), 'collaborators.user', 'creator']);

        return view('admin.surveys.form', [
            'survey' => $survey,
            'questions' => $survey->questions,
            'sections' => $survey->sections,
            'skipRules' => $survey->skipRules,
        ]);
    }

    public function update(Request $request, Survey $survey)
    {
        $this->surveyService->authorizeSurvey($survey, 'editor');
        $data = $this->surveyService->validateSurvey($request, $survey);
        $sections = $this->surveyService->validateSectionsPayload($request->input('sections_payload'));
        $questions = $this->surveyService->validateQuestionsPayload($request->input('questions_payload'), $sections);
        $skipRules = $this->surveyService->validateSkipRulesPayload($request->input('skip_rules_payload'), $sections, $questions);

        DB::transaction(function () use ($survey, $data, $sections, $questions, $skipRules) {
            $survey->update($data);
            $sectionMap = $this->surveyService->syncSections($survey, $sections);
            $this->surveyService->syncQuestions($survey, $questions, $sectionMap);
            $this->surveyService->syncSkipRules($survey, $skipRules, $sectionMap);
            $this->surveyService->storeVersion($survey, 'updated');
        });

        return redirect()->route('admin.surveys.index')->with('success', 'Survey diperbarui.');
    }

    public function destroy(Survey $survey)
    {
        $survey->delete();

        return redirect()->route('admin.surveys.index')->with('success', 'Survey dihapus.');
    }

    public function export(Survey $survey)
    {
        $this->surveyService->authorizeSurvey($survey, 'viewer');
        $survey->load('questions');
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="survey-'.$survey->slug.'-responses.csv"',
        ];

        $callback = function () use ($survey) {
            $handle = fopen('php://output', 'w');
            $questionHeaders = $survey->questions->pluck('question')->toArray();
            fputcsv($handle, array_merge(['response_id', 'submitted_at', 'user'], $questionHeaders));
            $survey->responses()->with('answers')->chunk(200, function ($chunk) use ($handle, $survey) {
                foreach ($chunk as $response) {
                    $row = [
                        $response->id,
                        $response->submitted_at,
                        optional($response->user)->email ?? 'anon',
                    ];
                    foreach ($survey->questions as $question) {
                        $answer = $response->answers->firstWhere('survey_question_id', $question->id);
                        $row[] = $answer?->answer_text ?? $answer?->answer_numeric ?? ($answer?->answer_json ? json_encode($answer->answer_json) : '');
                    }
                    fputcsv($handle, $row);
                }
            });
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportXlsx(Survey $survey)
    {
        $this->surveyService->authorizeSurvey($survey, 'viewer');
        
        dispatch(new \App\Jobs\ExportSurveyJob($survey, auth()->user()));

        return back()->with('success', 'Ekspor Excel Sedang Diproses. Hasil unduhan akan dikirimkan ke Email Anda dalam beberapa menit.');
    }

    public function analytics(Survey $survey)
    {
        $this->surveyService->authorizeSurvey($survey, 'viewer');
        $survey->load(['questions.options'])->loadCount('responses');

        $responsesCount = $survey->responses_count;
        $uniqueRespondents = $survey->responses()->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $anonymousResponses = $responsesCount - $uniqueRespondents;

        $start = request('start');
        $end = request('end');
        $dailyQuery = $survey->responses()->selectRaw('DATE(created_at) as date, COUNT(*) as total');
        if ($start) {
            $dailyQuery->whereDate('created_at', '>=', $start);
        }
        if ($end) {
            $dailyQuery->whereDate('created_at', '<=', $end);
        }
        $dailyResponses = $dailyQuery->groupBy('date')->orderBy('date')->get();

        $answersByQuestion = SurveyAnswer::whereIn('survey_question_id', $survey->questions->pluck('id'))
            ->get()
            ->groupBy('survey_question_id');

        $questionStats = $this->surveyService->buildQuestionStats($survey, $answersByQuestion);

        return view('admin.surveys.analytics', [
            'survey' => $survey,
            'responsesCount' => $responsesCount,
            'uniqueRespondents' => $uniqueRespondents,
            'anonymousResponses' => $anonymousResponses,
            'dailyResponses' => $dailyResponses,
            'questionStats' => $questionStats,
        ]);
    }

    public function duplicate(Survey $survey)
    {
        $this->surveyService->authorizeSurvey($survey, 'editor');
        DB::transaction(function () use ($survey, &$newSurvey) {
            $newSurvey = $survey->replicate(['slug', 'embed_token', 'created_at', 'updated_at']);
            $newSurvey->title = $survey->title . ' (Copy)';
            $newSurvey->slug = $survey->slug . '-' . Str::random(4);
            $newSurvey->embed_token = Str::random(24);
            $newSurvey->save();

            $sectionMap = [];
            foreach ($survey->sections as $section) {
                $newSection = $section->replicate(['id', 'created_at', 'updated_at']);
                $newSection->survey_id = $newSurvey->id;
                $newSection->save();
                $sectionMap[$section->id] = $newSection->id;
            }

            $questionMap = [];
            foreach ($survey->questions as $question) {
                $newQuestion = $question->replicate(['id', 'created_at', 'updated_at']);
                $newQuestion->survey_id = $newSurvey->id;
                $newQuestion->survey_section_id = $sectionMap[$question->survey_section_id] ?? null;
                $newQuestion->save();
                $questionMap[$question->id] = $newQuestion->id;

                foreach ($question->options as $option) {
                    $newOption = $option->replicate(['id', 'created_at', 'updated_at']);
                    $newOption->survey_question_id = $newQuestion->id;
                    $newOption->save();
                }
            }

            foreach ($survey->skipRules as $rule) {
                $newSurvey->skipRules()->create([
                    'survey_id' => $newSurvey->id,
                    'survey_question_id' => $questionMap[$rule->survey_question_id] ?? null,
                    'target_section_id' => $sectionMap[$rule->target_section_id] ?? null,
                    'conditions' => $rule->conditions,
                ]);
            }

            $this->surveyService->storeVersion($newSurvey, 'duplicate');
        });

        return redirect()->route('admin.surveys.edit', $newSurvey)->with('success', 'Survey berhasil diduplikasi.');
    }

    public function restoreVersion(Survey $survey, SurveyVersion $version)
    {
        $this->surveyService->authorizeSurvey($survey, 'editor');
        $snapshot = $version->snapshot;
        if (! $snapshot) {
            return back()->with('error', 'Snapshot tidak ditemukan.');
        }

        DB::transaction(function () use ($survey, $snapshot) {
            $sections = $snapshot['sections'] ?? [];
            $questions = $snapshot['questions'] ?? [];
            $skipRules = $snapshot['skip_rules'] ?? [];
            $sectionMap = $this->surveyService->syncSections($survey, $sections);
            $this->surveyService->syncQuestions($survey, $questions, $sectionMap);
            $this->surveyService->syncSkipRules($survey, $skipRules, $sectionMap);
        });

        return redirect()->route('admin.surveys.edit', $survey)->with('success', 'Survey dipulihkan ke versi sebelumnya.');
    }

    public function addCollaborator(Request $request, Survey $survey)
    {
        $this->surveyService->authorizeSurvey($survey, 'owner');
        $data = $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:owner,editor,viewer',
        ]);
        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            return back()->with('error', 'Pengguna tidak ditemukan.');
        }
        SurveyCollaborator::updateOrCreate(
            ['survey_id' => $survey->id, 'user_id' => $user->id],
            ['role' => $data['role']]
        );

        return back()->with('success', 'Kolaborator ditambahkan.');
    }

    public function removeCollaborator(Survey $survey, SurveyCollaborator $collaborator)
    {
        $this->surveyService->authorizeSurvey($survey, 'owner');
        if ($collaborator->survey_id !== $survey->id) {
            abort(404);
        }
        $collaborator->delete();

        return back()->with('success', 'Kolaborator dihapus.');
    }

    public function downloadAttachment(SurveyAnswer $answer)
    {
        $answer->loadMissing('response.survey');
        if (! $answer->response || ! $answer->response->survey) {
            abort(404);
        }

        $this->surveyService->authorizeSurvey($answer->response->survey, 'viewer');

        if (! $answer->file_path) {
            abort(404);
        }

        $path = $answer->file_path;
        $disk = null;

        if (Storage::disk('local')->exists($path)) {
            $disk = 'local';
        } elseif (Storage::disk('public')->exists($path)) {
            $disk = 'public';
        } elseif (Storage::exists($path)) {
            $disk = config('filesystems.default', 'local');
        } else {
            abort(404);
        }

        $name = basename($path);
        return Storage::disk($disk)->download($path, $name);
    }
}
