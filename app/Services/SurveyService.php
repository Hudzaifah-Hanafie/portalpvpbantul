<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SurveyService
{
    private const QUESTION_TYPES = [
        'short_text',
        'long_text',
        'choice_single',
        'choice_multiple',
        'dropdown',
        'linear_scale',
        'date',
        'time',
        'file_upload',
        'grid_single',
        'grid_multiple',
        'rating',
        'choice_single_other',
    ];

    public function validateSurvey(Request $request, ?Survey $survey = null): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'welcome_message' => 'nullable|string',
            'thank_you_message' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'require_login' => 'nullable|boolean',
            'allow_multiple_responses' => 'nullable|boolean',
            'show_progress' => 'nullable|boolean',
            'max_responses' => 'nullable|integer|min:1',
            'opens_at' => 'nullable|date',
            'closes_at' => 'nullable|date|after_or_equal:opens_at',
            'questions_payload' => 'required|string',
            'sections_payload' => 'required|string',
            'skip_rules_payload' => 'nullable|string',
            'theme_primary' => 'nullable|string|max:20',
            'theme_font' => 'nullable|string|max:100',
            'theme_cover' => 'nullable|url',
            'restrict_to_logged_in' => 'nullable|boolean',
            'allow_embed' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['require_login'] = $request->boolean('require_login');
        $data['allow_multiple_responses'] = $request->boolean('allow_multiple_responses');
        $data['show_progress'] = $request->boolean('show_progress');
        $data['settings'] = [
            'shuffle_questions' => $request->boolean('shuffle_questions'),
        ];
        $data['theme'] = [
            'primary' => $request->input('theme_primary'),
            'font' => $request->input('theme_font'),
            'cover' => $request->input('theme_cover'),
        ];
        $data['restrict_to_logged_in'] = $request->boolean('restrict_to_logged_in');
        $data['allow_embed'] = $request->boolean('allow_embed', true);

        if ($survey) {
            unset($data['questions_payload']);
            unset($data['sections_payload'], $data['skip_rules_payload']);
        }

        return $data;
    }

    public function validateSectionsPayload(?string $payload): array
    {
        $decoded = json_decode($payload ?? '', true);
        if (! is_array($decoded) || ! count($decoded)) {
            throw ValidationException::withMessages(['sections_payload' => 'Minimal satu section diperlukan.']);
        }

        $sections = [];
        foreach ($decoded as $index => $item) {
            $validator = Validator::make($item ?? [], [
                'id' => 'nullable|string',
                'key' => 'required|string',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'position' => 'nullable|integer|min:0',
            ]);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $data = $validator->validated();
            $data['position'] = $data['position'] ?? $index;
            $sections[] = $data;
        }

        return $sections;
    }

    public function validateQuestionsPayload(?string $payload, array $sections = []): array
    {
        $decoded = json_decode($payload ?? '', true);

        if (! is_array($decoded)) {
            throw ValidationException::withMessages(['questions_payload' => 'Struktur pertanyaan tidak valid. Ulangi simpan.']);
        }

        $sectionKeys = collect($sections)->pluck('key')->all();

        $questions = [];
        foreach ($decoded as $index => $item) {
            $validator = Validator::make($item ?? [], [
                'id' => 'nullable|string',
                'question' => 'required|string|max:1000',
                'description' => 'nullable|string',
                'type' => 'required|string|in:' . implode(',', self::QUESTION_TYPES),
                'is_required' => 'boolean',
                'placeholder' => 'nullable|string|max:255',
                'position' => 'nullable|integer|min:0',
                'settings.min' => 'nullable|integer',
                'settings.max' => 'nullable|integer',
                'settings.left_label' => 'nullable|string|max:100',
                'settings.right_label' => 'nullable|string|max:100',
                'settings.max_length' => 'nullable|integer|min:1|max:1000',
                'settings.max_size' => 'nullable|integer|min:1', 
                'settings.mime' => 'nullable|string',
                'settings.min_choices' => 'nullable|integer|min:0',
                'settings.max_choices' => 'nullable|integer|min:0',
                'settings.rows' => 'nullable|array',
                'settings.columns' => 'nullable|array',
                'settings.rows.*' => 'nullable|string|max:255',
                'settings.columns.*' => 'nullable|string|max:255',
                'options' => 'array',
                'options.*.id' => 'nullable|string',
                'options.*.label' => 'required_with:options|string|max:255',
                'options.*.value' => 'nullable|string|max:255',
                'options.*.is_other' => 'boolean',
                'options.*.position' => 'nullable|integer|min:0',
                'section_key' => 'nullable|string',
                'validation.regex' => 'nullable|string',
                'validation.format' => 'nullable|string|in:email,phone',
                'visibility_rules' => 'nullable|array',
                'visibility_rules.*.question_id' => 'required_with:visibility_rules|string',
                'visibility_rules.*.action' => 'required_with:visibility_rules|string|in:show,hide',
                'visibility_rules.*.equals' => 'nullable|string',
                'visibility_rules.*.in' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $question = $validator->validated();
            $question['position'] = $question['position'] ?? $index;
            $question['options'] = $question['options'] ?? [];
            $question['is_required'] = (bool) ($question['is_required'] ?? false);
            if (! empty($sectionKeys) && (! isset($question['section_key']) || ! in_array($question['section_key'], $sectionKeys, true))) {
                $question['section_key'] = $sectionKeys[0];
            }

            if (in_array($question['type'], ['choice_single', 'choice_multiple', 'dropdown'], true) && count($question['options']) < 1) {
                throw ValidationException::withMessages(['questions_payload' => 'Pertanyaan pilihan ganda/daftar harus memiliki minimal 1 opsi.']);
            }

            if ($question['type'] === 'linear_scale') {
                $min = $question['settings']['min'] ?? 1;
                $max = $question['settings']['max'] ?? 5;
                if ($min >= $max) {
                    throw ValidationException::withMessages(['questions_payload' => 'Pengaturan skala harus memiliki rentang minimum < maksimum.']);
                }
                $question['settings']['min'] = $min;
                $question['settings']['max'] = $max;
            }

            if ($question['type'] === 'grid_multiple' || $question['type'] === 'grid_single') {
                if (empty($question['settings']['rows']) || empty($question['settings']['columns'])) {
                    throw ValidationException::withMessages(['questions_payload' => 'Pertanyaan grid wajib punya baris dan kolom.']);
                }
            }

            $questions[] = $question;
        }

        return $questions;
    }

    public function validateSkipRulesPayload(?string $payload, array $sections, array $questions): array
    {
        if (! $payload) {
            return [];
        }

        $decoded = json_decode($payload, true);
        if (! is_array($decoded)) {
            throw ValidationException::withMessages(['skip_rules_payload' => 'Format skip logic tidak valid.']);
        }

        $questionIds = collect($questions)->pluck('id')->filter()->all();
        $sectionKeys = collect($sections)->pluck('key', 'id');

        $rules = [];
        foreach ($decoded as $rule) {
            $validator = Validator::make($rule ?? [], [
                'question_id' => 'required|string',
                'target_section_key' => 'required|string',
                'conditions' => 'required|array',
                'conditions.selected_option_ids' => 'nullable|array',
                'conditions.equals_text' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $data = $validator->validated();
            if ($questionIds && ! in_array($data['question_id'], $questionIds, true)) {
                continue;
            }
            $rules[] = $data;
        }

        return $rules;
    }

    public function syncSections(Survey $survey, array $sections): array
    {
        $existing = $survey->sections()->get()->keyBy('id');
        $kept = [];
        $map = [];

        foreach ($sections as $section) {
            $model = $section['id'] && $existing->has($section['id'])
                ? $existing->get($section['id'])
                : $survey->sections()->make();

            $model->fill([
                'title' => $section['title'],
                'description' => $section['description'] ?? null,
                'position' => $section['position'] ?? 0,
            ]);
            $model->save();
            $kept[] = $model->id;
            $map[$section['key']] = $model->id;
        }

        $survey->sections()->whereNotIn('id', $kept)->delete();

        return $map;
    }

    public function syncQuestions(Survey $survey, array $questions, array $sectionMap): void
    {
        $existingQuestions = $survey->questions()->with('options')->get()->keyBy('id');
        $keptQuestionIds = [];

        foreach ($questions as $questionData) {
            $question = $questionData['id'] && $existingQuestions->has($questionData['id'])
                ? $existingQuestions->get($questionData['id'])
                : new SurveyQuestion(['survey_id' => $survey->id]);

            $question->fill([
                'type' => $questionData['type'],
                'question' => $questionData['question'],
                'description' => $questionData['description'] ?? null,
                'is_required' => $questionData['is_required'] ?? false,
                'position' => $questionData['position'] ?? 0,
                'settings' => $questionData['settings'] ?? [],
                'placeholder' => $questionData['placeholder'] ?? null,
                'survey_section_id' => $sectionMap[$questionData['section_key']] ?? null,
                'validation' => $questionData['validation'] ?? null,
                'visibility_rules' => $questionData['visibility_rules'] ?? [],
            ]);
            $question->save();

            $keptQuestionIds[] = $question->id;

            $existingOptions = $question->options()->get()->keyBy('id');
            $keptOptionIds = [];

            foreach ($questionData['options'] as $idx => $optionData) {
                $optionId = $optionData['id'] ?? null;
                $option = $optionId && $existingOptions->has($optionId)
                    ? $existingOptions->get($optionId)
                    : new SurveyQuestionOption(['survey_question_id' => $question->id]);

                $option->fill([
                    'label' => $optionData['label'] ?? '',
                    'value' => $optionData['value'] ?? null,
                    'is_other' => $optionData['is_other'] ?? false,
                    'position' => $optionData['position'] ?? $idx,
                ]);
                $option->save();

                $keptOptionIds[] = $option->id;
            }

            $question->options()->whereNotIn('id', $keptOptionIds)->delete();
        }

        $survey->questions()->whereNotIn('id', $keptQuestionIds)->delete();
    }

    public function syncSkipRules(Survey $survey, array $rules, array $sectionMap): void
    {
        $existing = $survey->skipRules()->get()->keyBy('id');
        $surveyQuestionMap = $survey->questions()->pluck('id')->all();
        $kept = [];

        foreach ($rules as $rule) {
            if (! in_array($rule['question_id'], $surveyQuestionMap, true)) {
                continue;
            }
            $targetSectionId = $sectionMap[$rule['target_section_key']] ?? null;
            if (! $targetSectionId) {
                continue;
            }
            $model = $survey->skipRules()->make();
            $model->fill([
                'survey_id' => $survey->id,
                'survey_question_id' => $rule['question_id'],
                'target_section_id' => $targetSectionId,
                'conditions' => $rule['conditions'],
            ]);
            $model->save();
            $kept[] = $model->id;
        }

        $survey->skipRules()->whereNotIn('id', $kept)->delete();
    }

    public function buildQuestionStats(Survey $survey, Collection $answersByQuestion): array
    {
        $stats = [];

        foreach ($survey->questions as $question) {
            $answers = $answersByQuestion->get($question->id, collect());
            $stat = [
                'question' => $question,
                'responses' => $answers->count(),
            ];

            if (in_array($question->type, ['choice_single', 'choice_multiple', 'dropdown', 'choice_single_other'], true)) {
                $optionStats = [];
                foreach ($question->options as $option) {
                    $count = $answers->filter(function (SurveyAnswer $answer) use ($option) {
                        $selected = $answer->selected_option_ids ?? [];
                        return in_array($option->id, $selected, true);
                    })->count();

                    $optionStats[] = [
                        'label' => $option->label,
                        'count' => $count,
                    ];
                }
                $stat['option_stats'] = $optionStats;
            } elseif (in_array($question->type, ['linear_scale', 'rating'], true)) {
                $values = $answers->pluck('answer_numeric')->filter();
                $distribution = $values->countBy()->map(fn ($c, $value) => ['value' => $value, 'count' => $c])->values();
                $stat['scale'] = [
                    'avg' => $values->count() ? round($values->avg(), 2) : null,
                    'min' => $values->min(),
                    'max' => $values->max(),
                    'distribution' => $distribution,
                ];
            } else {
                $stat['samples'] = $answers->pluck('answer_text')->filter()->take(3)->values();
            }

            $stats[] = $stat;
        }

        return $stats;
    }

    public function storeVersion(Survey $survey, string $note = null): void
    {
        $survey->load(['sections', 'questions.options', 'skipRules']);
        $snapshot = [
            'sections' => $survey->sections->map(fn($s) => [
                'id' => $s->id,
                'key' => $s->id,
                'title' => $s->title,
                'description' => $s->description,
                'position' => $s->position,
            ])->values()->toArray(),
            'questions' => $survey->questions->map(function ($q) {
                return [
                    'id' => $q->id,
                    'question' => $q->question,
                    'description' => $q->description,
                    'type' => $q->type,
                    'is_required' => $q->is_required,
                    'placeholder' => $q->placeholder,
                    'position' => $q->position,
                    'settings' => $q->settings,
                    'validation' => $q->validation,
                    'section_key' => $q->survey_section_id,
                    'options' => $q->options->map(fn($o) => [
                        'id' => $o->id,
                        'label' => $o->label,
                        'value' => $o->value,
                        'position' => $o->position,
                        'is_other' => $o->is_other,
                    ])->values()->toArray(),
                ];
            })->values()->toArray(),
            'skip_rules' => $survey->skipRules->map(fn($r) => [
                'question_id' => $r->survey_question_id,
                'target_section_key' => $r->target_section_id,
                'conditions' => $r->conditions,
            ])->values()->toArray(),
        ];

        $survey->versions()->create([
            'user_id' => auth()->id(),
            'snapshot' => $snapshot,
            'note' => $note,
        ]);
    }

    public function authorizeSurvey(Survey $survey, string $neededRole = 'viewer'): void
    {
        $user = auth()->user();
        if ($user->can('manage-surveys')) {
            return;
        }

        $roleRank = ['viewer' => 1, 'editor' => 2, 'owner' => 3];
        $userRoleRank = 0;

        if ($survey->created_by === $user->id) {
            $userRoleRank = $roleRank['owner'];
        } else {
            $collab = $survey->collaborators()->where('user_id', $user->id)->first();
            if ($collab) {
                $userRoleRank = $roleRank[$collab->role] ?? 0;
            }
        }

        if ($userRoleRank >= ($roleRank[$neededRole] ?? 0)) {
            return;
        }

        abort(403, 'Anda tidak memiliki akses ke survey ini.');
    }
}
