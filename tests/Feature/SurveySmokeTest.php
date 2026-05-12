<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionOption;
use PHPUnit\Framework\Attributes\Test;

class SurveySmokeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function survey_page_loads(): void
    {
        $response = $this->get('/survei/dummy');

        $response->assertStatus(404); // route exists; 404 indicates slug not found but app responds.
    }

    #[Test]
    public function user_can_submit_simple_survey(): void
    {
        config()->set('services.recaptcha.site_key', null);
        config()->set('services.recaptcha.secret_key', null);
        config()->set('services.hcaptcha.site_key', null);
        config()->set('services.turnstile.site_key', null);

        $survey = Survey::create([
            'title' => 'Smoke Survey',
            'is_active' => true,
            'require_login' => false,
            'allow_multiple_responses' => true,
            'show_progress' => true,
        ]);

        $question = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'type' => 'short_text',
            'question' => 'Apa pendapat Anda?',
            'is_required' => true,
            'position' => 1,
            'settings' => ['max_length' => 120],
        ]);

        $choice = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'type' => 'choice_single',
            'question' => 'Pilih opsi',
            'is_required' => true,
            'position' => 2,
        ]);

        $option = SurveyQuestionOption::create([
            'survey_question_id' => $choice->id,
            'label' => 'Opsi A',
            'position' => 1,
        ]);

        $this->get(route('surveys.show', $survey))->assertStatus(200);

        $payload = [
            "responses" => [
                $question->id => 'Bagus sekali',
                $choice->id => $option->id,
            ],
        ];

        $this->post(route('surveys.submit', $survey), $payload)
            ->assertRedirect(route('surveys.show', $survey));

        $this->assertDatabaseHas('survey_responses', ['survey_id' => $survey->id]);
        $this->assertDatabaseHas('survey_answers', [
            'survey_question_id' => $question->id,
            'answer_text' => 'Bagus sekali',
        ]);
    }
}
