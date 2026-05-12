<?php

namespace App\Console\Commands;

use App\Models\CourseAssignment;
use App\Models\CourseEnrollment;
use App\Models\CourseSession;
use App\Models\CourseSubmission;
use App\Models\Survey;
use App\Models\SurveyInstance;
use App\Notifications\AssignmentDueReminder;
use App\Notifications\SessionReminder;
use App\Notifications\GradingSlaReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendCourseReminders extends Command
{
    protected $signature = 'course:reminders';

    protected $description = 'Kirim reminder tugas/sesi/grading serta otomatis trigger survey pasca kelas';

    public function handle(): int
    {
        $this->remindAssignments();
        $this->remindSessions();
        $this->remindGrading();
        $this->triggerPostClassSurvey();

        $this->info('Reminder course processed.');
        return self::SUCCESS;
    }

    private function remindAssignments(): void
    {
        $hours = (int) config('course.reminders.assignment_due_hours', 24);
        if ($hours <= 0) {
            return;
        }

        $now = now();
        $windowEnd = $now->copy()->addHours($hours);

        CourseAssignment::with('course')
            ->where('status', 'published')
            ->where('is_active', true)
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$now, $windowEnd])
            ->chunk(50, function ($assignments) use ($now) {
                $classIds = $assignments->pluck('course_class_id')->unique();
                $enrollmentsMap = CourseEnrollment::with('user')
                    ->whereIn('course_class_id', $classIds)
                    ->whereIn('status', ['active', 'approved'])
                    ->get()
                    ->groupBy('course_class_id');
                
                $assignmentIds = $assignments->pluck('id');
                $submissionsMap = CourseSubmission::whereIn('course_assignment_id', $assignmentIds)
                    ->get()
                    ->groupBy('course_assignment_id');

                foreach ($assignments as $assignment) {
                    $enrollments = $enrollmentsMap->get($assignment->course_class_id) ?? collect();
                    $assignmentSubmissions = $submissionsMap->get($assignment->id) ?? collect();
                    $submittedUserIds = $assignmentSubmissions->pluck('user_id')->unique()->flip();

                    foreach ($enrollments as $enroll) {
                        if ($submittedUserIds->has($enroll->user_id) || ! $enroll->user) {
                            continue;
                        }
                        $enroll->user->notify(new AssignmentDueReminder(
                            $assignment->title,
                            $assignment->course->title ?? '-',
                            $assignment->due_at?->format('d M Y H:i') ?? ''
                        ));
                    }
                }
            });
    }

    private function remindSessions(): void
    {
        $minutes = (int) config('course.reminders.session_start_minutes', 60);
        if ($minutes <= 0) {
            return;
        }

        $now = now();
        $windowEnd = $now->copy()->addMinutes($minutes);

        CourseSession::with('course')
            ->where('status', 'published')
            ->where('is_active', true)
            ->whereBetween('start_at', [$now, $windowEnd])
            ->chunk(50, function ($sessions) {
                $classIds = $sessions->pluck('course_class_id')->unique();
                $enrollmentsMap = CourseEnrollment::with('user')
                    ->whereIn('course_class_id', $classIds)
                    ->whereIn('status', ['active', 'approved'])
                    ->get()
                    ->groupBy('course_class_id');

                foreach ($sessions as $session) {
                    $enrollments = $enrollmentsMap->get($session->course_class_id) ?? collect();
                    foreach ($enrollments as $enroll) {
                        if (! $enroll->user) {
                            continue;
                        }
                        $enroll->user->notify(new SessionReminder(
                            $session->title,
                            $session->course->title ?? '-',
                            $session->start_at?->format('d M Y H:i') ?? '',
                            $session->meeting_link
                        ));
                    }
                }
            });
    }

    private function remindGrading(): void
    {
        $slaHours = (int) config('course.reminders.grading_sla_hours', 48);
        if ($slaHours <= 0) {
            return;
        }

        $threshold = now()->subHours($slaHours);

        CourseSubmission::with(['assignment.course.instructor'])
            ->where('status', 'submitted')
            ->where('submitted_at', '<=', $threshold)
            ->chunk(50, function ($subs) use ($slaHours) {
                foreach ($subs as $submission) {
                    $instructor = $submission->assignment?->course?->instructor;
                    if (! $instructor) {
                        continue;
                    }
                    $instructor->notify(new GradingSlaReminder(
                        $submission->assignment->title ?? '-',
                        $submission->assignment->course->title ?? '-',
                        $submission->submitted_at?->format('d M Y H:i') ?? '-',
                        $slaHours
                    ));
                }
            });
    }

    private function triggerPostClassSurvey(): void
    {
        $enabled = config('course.reminders.survey_auto_trigger_enabled', true);
        $surveyId = config('course.reminders.survey_post_class_id');
        if (! $enabled || ! $surveyId) {
            return;
        }

        $survey = Survey::where('id', $surveyId)->orWhere('slug', $surveyId)->first();
        if (! $survey) {
            return;
        }

        $closeDays = (int) config('course.reminders.survey_auto_close_days', 7);
        $now = now();

        // Cari kelas yang sesi terakhirnya sudah lewat 1 hari dan belum ada survey instance utk survey ini
        $classIds = CourseSession::select('course_class_id', DB::raw('MAX(end_at) as last_end'))
            ->groupBy('course_class_id')
            ->havingRaw('MAX(end_at) <= ?', [$now->copy()->subDay()])
            ->pluck('course_class_id')
            ->all();

        if (empty($classIds)) {
            return;
        }

        $already = SurveyInstance::where('survey_id', $survey->id)
            ->whereIn('course_class_id', $classIds)
            ->pluck('course_class_id')
            ->all();

        $targets = array_diff($classIds, $already);

        foreach ($targets as $classId) {
            SurveyInstance::create([
                'survey_id' => $survey->id,
                'course_class_id' => $classId,
                'status' => 'open',
                'opens_at' => $now,
                'closes_at' => $now->copy()->addDays($closeDays),
                'triggered_at' => $now,
                'min_responses_threshold' => 5,
                'created_by' => null,
            ]);
        }
    }
}
