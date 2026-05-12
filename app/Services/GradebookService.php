<?php

namespace App\Services;

use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\CourseAttendance;
use App\Models\CourseSubmission;
use Illuminate\Support\Collection;

class GradebookService
{
    /**
     * Menghitung ulang dan memperbarui hasil belajar (Rapor)
     *
     * @param CourseEnrollment $enrollment
     * @param CourseClass|null $class
     * @return void
     */
    public function updateLearningOutcome(CourseEnrollment $enrollment, ?CourseClass $class = null): void
    {
        $class = $class ?? $enrollment->course;
        if (! $class) {
            return;
        }

        $this->applyAutoScoresFromSubmissions($enrollment, $class);
        $attendanceRate = $this->calculateAttendanceRate($enrollment, $class);
        $finalGrade = $this->calculateFinalGrade($enrollment, $class);

        $enrollment->attendance_rate = $attendanceRate;
        $enrollment->final_grade = $finalGrade;

        if ($finalGrade !== null && $attendanceRate !== null) {
            $isCompetent = $finalGrade >= ($class->min_score ?? 0)
                && $attendanceRate >= ($class->min_attendance ?? 0);
            $enrollment->competency_status = $isCompetent ? 'competent' : 'not_competent';
        } else {
            $enrollment->competency_status = 'pending';
        }

        $enrollment->save();
    }

    private function applyAutoScoresFromSubmissions(CourseEnrollment $enrollment, CourseClass $class): void
    {
        $submissions = $this->gradedSubmissionsForClass($enrollment, $class);
        if ($submissions->isEmpty()) {
            $enrollment->post_test_score = null;
            $enrollment->practice_score = null;
            return;
        }

        $quizItems = collect();
        $finalExamItems = collect();
        $practiceItems = collect();

        foreach ($submissions as $submission) {
            if (($submission->quiz_scope ?? null) === 'selection') {
                continue;
            }

            $score = $this->normalizedSubmissionScore($submission);
            if ($score === null) {
                continue;
            }

            $assessmentType = $submission->assessment_type ?? 'regular';
            $assignmentType = $submission->assignment_type ?? $submission->type ?? null;
            $weight = (float) ($submission->weight ?? 0);

            $item = [
                'score' => $score,
                'weight' => $weight > 0 ? $weight : null,
            ];

            if ($assignmentType === 'quiz' || in_array($assessmentType, ['module_quiz', 'final_exam'], true)) {
                $quizItems->push($item);
                if ($assessmentType === 'final_exam') {
                    $finalExamItems->push($item);
                }
            }

            if ($assignmentType !== 'quiz' || $assessmentType === 'final_project') {
                $practiceItems->push($item);
            }
        }

        $postScore = $finalExamItems->isNotEmpty()
            ? $this->weightedAverage($finalExamItems)
            : $this->weightedAverage($quizItems);
        $practiceScore = $this->weightedAverage($practiceItems);

        $enrollment->post_test_score = $postScore;
        $enrollment->practice_score = $practiceScore;
    }

    private function calculateAttendanceRate(CourseEnrollment $enrollment, CourseClass $class): ?float
    {
        $sessionCount = $class->sessions()->count();
        if ($sessionCount === 0) {
            return null;
        }

        $presentCount = CourseAttendance::join('course_sessions', 'course_sessions.id', '=', 'course_attendances.course_session_id')
            ->where('course_sessions.course_class_id', $class->id)
            ->where('course_attendances.user_id', $enrollment->user_id)
            ->whereIn('course_attendances.status', ['hadir', 'telat', 'izin'])
            ->count();

        return round(($presentCount / $sessionCount) * 100, 2);
    }

    private function calculateFinalGrade(CourseEnrollment $enrollment, CourseClass $class): ?float
    {
        $theory = $enrollment->post_test_score ?? $enrollment->written_score;
        $practice = $enrollment->practice_score;
        $attitude = $enrollment->attitude_score;

        if ($theory === null || $practice === null || $attitude === null) {
            return null;
        }

        $weightTheory = $class->weight_theory ?? 30;
        $weightPractice = $class->weight_practice ?? 60;
        $weightAttitude = $class->weight_attitude ?? 10;
        $weightTotal = $weightTheory + $weightPractice + $weightAttitude;
        if ($weightTotal <= 0) {
            return null;
        }

        $score = ($theory * $weightTheory)
            + ($practice * $weightPractice)
            + ($attitude * $weightAttitude);

        return round($score / 100, 2);
    }

    private function gradedSubmissionsForClass(CourseEnrollment $enrollment, CourseClass $class): Collection
    {
        return CourseSubmission::select(
                'course_submissions.*',
                'course_assignments.type as assignment_type',
                'course_assignments.assessment_type',
                'course_assignments.quiz_scope',
                'course_assignments.weight',
                'course_assignments.max_score'
            )
            ->join('course_assignments', 'course_assignments.id', '=', 'course_submissions.course_assignment_id')
            ->where('course_assignments.course_class_id', $class->id)
            ->where('course_submissions.user_id', $enrollment->user_id)
            ->where('course_submissions.status', 'graded')
            ->get();
    }

    private function normalizedSubmissionScore(CourseSubmission $submission): ?float
    {
        $rawScore = $submission->total_score ?? $submission->quiz_score;
        if ($rawScore === null) {
            return null;
        }

        $maxScore = (float) ($submission->max_score ?? 0);
        $score = (float) $rawScore;
        $percent = $maxScore > 0 ? ($score / $maxScore) * 100 : $score;
        $percent = max(0, min(100, $percent));

        return round($percent, 2);
    }

    private function weightedAverage(Collection $items): ?float
    {
        $items = $items->filter(fn ($item) => $item['score'] !== null);
        if ($items->isEmpty()) {
            return null;
        }

        $weighted = $items->filter(fn ($item) => ! empty($item['weight']));
        if ($weighted->isNotEmpty()) {
            $sumWeight = $weighted->sum('weight');
            if ($sumWeight > 0) {
                $total = $weighted->sum(function ($item) {
                    return $item['score'] * $item['weight'];
                });
                return round($total / $sumWeight, 2);
            }
        }

        return round($items->avg('score'), 2);
    }
}
