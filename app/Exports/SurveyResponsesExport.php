<?php

namespace App\Exports;

use App\Models\Survey;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class SurveyResponsesExport implements FromCollection, WithHeadings, Responsable
{
    use Exportable;

    public string $fileName;

    protected Survey $survey;

    public function __construct(Survey $survey)
    {
        $this->survey = $survey;
        $this->fileName = 'survey-' . $survey->slug . '-responses.xlsx';
    }

    public function collection()
    {
        $this->survey->load('questions');

        return $this->survey->responses()->with('answers', 'user')->get()->map(function ($response) {
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
        });
    }

    public function headings(): array
    {
        return array_merge(['response_id', 'submitted_at', 'user'], $this->survey->questions->pluck('question')->toArray());
    }
}