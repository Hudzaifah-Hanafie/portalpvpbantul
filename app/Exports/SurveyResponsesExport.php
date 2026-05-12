<?php

namespace App\Exports;

use App\Models\Survey;
use App\Models\SurveyResponse;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SurveyResponsesExport implements FromQuery, WithHeadings, WithMapping
{
    protected Survey $survey;

    public function __construct(Survey $survey)
    {
        $this->survey = $survey;
        $this->survey->load('questions');
    }

    public function query()
    {
        return SurveyResponse::query()
            ->where('survey_id', $this->survey->id)
            ->with(['answers', 'user']);
    }

    public function map($response): array
    {
        $row = [
            $response->id,
            $response->submitted_at,
            optional($response->user)->email ?? 'anon',
        ];

        foreach ($this->survey->questions as $question) {
            $answer = $response->answers->firstWhere('survey_question_id', $question->id);
            $row[] = $answer?->answer_text ?? $answer?->answer_numeric ?? ($answer?->answer_json ? json_encode($answer->answer_json) : '');
        }

        return $row;
    }

    public function headings(): array
    {
        return array_merge(['response_id', 'submitted_at', 'user'], $this->survey->questions->pluck('question')->toArray());
    }
}
