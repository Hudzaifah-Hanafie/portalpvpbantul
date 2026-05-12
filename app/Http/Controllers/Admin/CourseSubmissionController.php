<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RestrictsToInstructorClasses;
use App\Models\CourseAssignment;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\CourseSubmission;
use App\Models\CourseSubmissionGrade;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\TrainingSchedule;

class CourseSubmissionController extends Controller
{
    use RestrictsToInstructorClasses;

    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index()
    {
        $statusOptions = CourseSubmission::statuses();
        $statusFilter = request('status');
        $assignmentFilter = request('assignment_id');
        $classFilter = request('class_id');

        $query = CourseSubmission::with(['assignment.course', 'user'])->orderBy('submitted_at', 'desc');
        if ($this->isInstructorUser(request()->user())) {
            $classIds = CourseClass::where('instructor_id', request()->user()->id)->pluck('id');
            $query->whereHas('assignment', fn ($q) => $q->whereIn('course_class_id', $classIds));
        }

        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }
        if ($assignmentFilter) {
            $query->where('course_assignment_id', $assignmentFilter);
        } elseif ($classFilter) {
            $query->whereHas('assignment', fn ($q) => $q->where('course_class_id', $classFilter));
        }

        $submissions = $query->get();
        $classes = $this->scopedClassOptions(request()->user());
        $assignments = CourseAssignment::orderBy('title')
            ->when($this->isInstructorUser(request()->user()), function ($q) {
                $classIds = CourseClass::where('instructor_id', request()->user()->id)->pluck('id');
                $q->whereIn('course_class_id', $classIds);
            })
            ->pluck('title', 'id');

        $submissionGroups = $this->buildSubmissionGroups($submissions);

        return view('admin.course_submission.index', compact('submissions', 'submissionGroups', 'statusOptions', 'statusFilter', 'classes', 'classFilter', 'assignments', 'assignmentFilter'));
    }

    public function exportCsv(Request $request)
    {
        $query = CourseSubmission::with(['assignment.course', 'user']);
        if ($this->isInstructorUser($request->user())) {
            $classIds = CourseClass::where('instructor_id', $request->user()->id)->pluck('id');
            $query->whereHas('assignment', fn ($q) => $q->whereIn('course_class_id', $classIds));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('assignment_id')) {
            $query->where('course_assignment_id', $request->input('assignment_id'));
        }
        if ($request->filled('class_id')) {
            $query->whereHas('assignment', fn ($q) => $q->where('course_class_id', $request->input('class_id')));
        }

        $filename = 'submissions-' . now()->format('Ymd_His') . '.csv';
        $callback = function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['user_id', 'nama', 'kelas', 'tugas', 'status', 'total_score', 'submitted_at', 'graded_at', 'late', 'late_minutes']);
            
            // Penggunaan cursor() untuk mencegah Out Of Memory Load saat ratusan ribu data.
            foreach ($query->cursor() as $row) {
                fputcsv($out, [
                    $row->user_id,
                    $row->user->name ?? '',
                    $row->assignment?->course?->title ?? '',
                    $row->assignment?->title ?? '',
                    $row->status,
                    $row->total_score,
                    $row->submitted_at,
                    $row->graded_at,
                    $row->late ? 'yes' : 'no',
                    $row->late_minutes,
                ]);
            }
            
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv']);
    }

    public function edit(CourseSubmission $course_submission)
    {
        $statusOptions = CourseSubmission::statuses();
        $course_submission->load('assignment');
        if ($course_submission->assignment) {
            $this->ensureInstructorOwnsClassId(request()->user(), $course_submission->assignment->course_class_id);
        }
        $course_submission->load('grades.grader');
        return view('admin.course_submission.form', [
            'submission' => $course_submission,
            'statusOptions' => $statusOptions,
            'action' => route($this->getRoutePrefix() . 'course-submission.update', $course_submission->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, CourseSubmission $course_submission)
    {
        $course_submission->load('assignment');
        $assignment = $course_submission->assignment;
        if ($assignment) {
            $this->ensureInstructorOwnsClassId($request->user(), $assignment->course_class_id);
        }
        $hasRubric = $assignment && $assignment->rubric;

        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(CourseSubmission::statuses())),
            'total_score' => $hasRubric ? 'nullable' : 'nullable|integer|min:0|max:1000',
            'feedback' => 'nullable|string',
            'scores' => $hasRubric ? 'nullable|array' : 'nullable|string',
        ]);

        $rubricScores = [];
        $computedTotal = null;

        if ($hasRubric) {
            $rubric = collect($assignment->rubric);
            $inputScores = collect($request->input('rubric_scores', []));
            $sumWeight = max($rubric->sum(fn ($c) => (float) ($c['weight'] ?? 0)), 0.0001);
            $maxAssignment = $assignment->max_score ?: 100;

            $rubricScores = $rubric->map(function ($criterion, $idx) use ($inputScores, $sumWeight, $maxAssignment, $rubric) {
                $score = (float) ($inputScores[$idx]['score'] ?? 0);
                $comment = $inputScores[$idx]['comment'] ?? null;
                $maxScore = (float) ($criterion['max_score'] ?? $maxAssignment);
                $weight = (float) ($criterion['weight'] ?? 0);
                $weightRatio = $weight > 0 ? $weight / $sumWeight : (1 / max($rubric->count(), 1));
                $scoreClamped = max(0, min($score, $maxScore > 0 ? $maxScore : $score));
                $contrib = $maxScore > 0 ? ($scoreClamped / $maxScore) * $weightRatio * $maxAssignment : 0;

                return [
                    'criterion' => $criterion['criterion'] ?? 'Kriteria',
                    'weight' => $weight,
                    'max_score' => $maxScore,
                    'score' => $scoreClamped,
                    'comment' => $comment,
                    'contribution' => round($contrib, 2),
                    'description' => $criterion['description'] ?? null,
                ];
            });

            $computedTotal = (int) round($rubricScores->sum('contribution'));
        } else {
            // Parse per-kriteria scores jika dikirim sebagai JSON string atau multi-line "key:score"
            if (! empty($data['scores'])) {
                $parsed = json_decode($data['scores'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $parsed = collect(preg_split("/(\r?\n)+/", $data['scores']))
                        ->map(function ($line) {
                            if (!str_contains($line, ':')) {
                                return null;
                            }
                            [$k, $v] = array_map('trim', explode(':', $line, 2));
                            return $k !== '' ? [$k => (int) $v] : null;
                        })
                        ->filter()
                        ->collapse()
                        ->toArray();
                }
                $rubricScores = $parsed ?: null;
            }
        }

        $totalScore = $hasRubric ? $computedTotal : ($data['total_score'] ?? null);

        $updateData = [
            'status' => $data['status'],
            'total_score' => $totalScore,
            'feedback' => $data['feedback'] ?? null,
            'scores' => $hasRubric ? $rubricScores : ($rubricScores ?: null),
        ];

        if ($data['status'] === 'graded') {
            $updateData['graded_by'] = $request->user()->id;
            $updateData['graded_at'] = now();
        }

        $course_submission->update($updateData);

        if ($data['status'] === 'graded') {
            $nextVersion = ($course_submission->grades()->max('version') ?? 0) + 1;
            CourseSubmissionGrade::create([
                'course_submission_id' => $course_submission->id,
                'graded_by' => $request->user()->id,
                'total_score' => $totalScore,
                'scores' => $hasRubric ? $rubricScores : ($rubricScores ?: null),
                'rubric_meta' => $assignment?->rubric,
                'feedback' => $data['feedback'] ?? null,
                'version' => $nextVersion,
                'graded_at' => now(),
            ]);

            $this->logger->log(
                $request->user(),
                'course.submission.graded',
                "Submission '{$course_submission->id}' diperbarui",
                $course_submission,
                [
                    'version' => $nextVersion,
                    'total_score' => $totalScore,
                ]
            );
        } else {
            $this->logger->log(
                $request->user(),
                'course.submission.updated',
                "Submission '{$course_submission->id}' diperbarui tanpa nilai",
                $course_submission
            );
        }

        $this->syncEnrollmentOutcome($course_submission);

        return redirect()->route($this->getRoutePrefix() . 'course-submission.index')->with('success', 'Submission diperbarui.');
    }

    public function destroy(CourseSubmission $course_submission)
    {
        $assignment = $course_submission->assignment;
        if ($assignment) {
            $this->ensureInstructorOwnsClassId(request()->user(), $assignment->course_class_id);
        }
        $this->logger->log(
            request()->user(),
            'course.submission.deleted',
            "Submission '{$course_submission->id}' dihapus",
            $course_submission
        );
        $course_submission->delete();

        return redirect()->route($this->getRoutePrefix() . 'course-submission.index')->with('success', 'Submission dihapus.');
    }

    private function syncEnrollmentOutcome(CourseSubmission $submission): void
    {
        $assignment = $submission->assignment;
        if (! $assignment) {
            return;
        }

        $enrollment = CourseEnrollment::where('course_class_id', $assignment->course_class_id)
            ->where('user_id', $submission->user_id)
            ->first();

        if ($enrollment) {
            $enrollment->updateLearningOutcome();
        }
    }

    private function buildSubmissionGroups(Collection $submissions): Collection
    {
        if ($submissions->isEmpty()) {
            return collect();
        }

        $classIds = $submissions->map(function ($submission) {
            return $submission->assignment?->course?->id;
        })->filter()->unique()->values();

        $scheduleByClass = TrainingSchedule::with('program')
            ->whereIn('id', $classIds)
            ->get()
            ->keyBy('id');

        return $submissions->groupBy(function ($submission) use ($scheduleByClass) {
            $classId = $submission->assignment?->course?->id;
            $schedule = $classId ? $scheduleByClass->get($classId) : null;

            return $schedule?->program?->judul ?? 'LAINNYA';
        })->map(function (Collection $items) {
            return $items->groupBy(function ($submission) {
                return $submission->assignment?->course?->title ?? 'Kelas Tidak Diketahui';
            });
        });
    }
}
