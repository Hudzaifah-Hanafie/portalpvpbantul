<?php

namespace App\Services;

use App\Models\CourseAssignment;
use App\Models\CourseAttendance;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\CourseSession;
use App\Models\CourseSubmission;
use App\Models\KejuruanModule;
use App\Models\TrainingDocumentation;
use App\Models\TrainingSchedule;
use Illuminate\Support\Collection;

class LearningReportService
{
    public function generateReport(string $groupBy, ?string $classFilter, \Carbon\Carbon $today): array
    {
        $classQuery = CourseClass::query()->orderBy('title');
        if ($classFilter) {
            $classQuery->where('id', $classFilter);
        }

        $classes = $classQuery->get();
        $classIds = $classes->pluck('id')->all();

        $schedules = TrainingSchedule::with('program')
            ->whereIn('id', $classIds)
            ->get()
            ->keyBy('id');
        $programIds = $schedules->pluck('program_id')->filter()->unique()->values();

        $enrollments = CourseEnrollment::with('user')->whereIn('course_class_id', $classIds)->get();
        $enrollmentGroups = $enrollments->groupBy('course_class_id');

        $sessionStats = CourseSession::selectRaw('course_class_id, count(*) as total')
            ->selectRaw('sum(case when end_at is not null and end_at <= ? then 1 else 0 end) as completed', [$today])
            ->whereIn('course_class_id', $classIds)
            ->groupBy('course_class_id')
            ->get()
            ->keyBy('course_class_id');

        $attendanceCounts = CourseAttendance::join('course_sessions', 'course_sessions.id', '=', 'course_attendances.course_session_id')
            ->whereIn('course_sessions.course_class_id', $classIds)
            ->selectRaw("course_sessions.course_class_id, sum(case when course_attendances.status in ('hadir','telat','izin') then 1 else 0 end) as present")
            ->groupBy('course_sessions.course_class_id')
            ->pluck('present', 'course_sessions.course_class_id');

        $assignmentCounts = CourseAssignment::selectRaw('course_class_id, count(*) as total')
            ->whereIn('course_class_id', $classIds)
            ->groupBy('course_class_id')
            ->pluck('total', 'course_class_id');

        $submissions = CourseSubmission::select(
            'course_submissions.course_assignment_id',
            'course_submissions.user_id',
            'course_submissions.status',
            'course_submissions.quiz_score',
            'course_submissions.total_score',
            'course_assignments.course_class_id',
            'course_assignments.type'
        )
            ->join('course_assignments', 'course_assignments.id', '=', 'course_submissions.course_assignment_id')
            ->whereIn('course_assignments.course_class_id', $classIds)
            ->get()
            ->groupBy('course_class_id');

        $documentationCounts = TrainingDocumentation::selectRaw('training_schedule_id, count(*) as total')
            ->whereIn('training_schedule_id', $classIds)
            ->groupBy('training_schedule_id')
            ->pluck('total', 'training_schedule_id');

        $moduleCounts = KejuruanModule::selectRaw('program_id, count(*) as total')
            ->whereIn('program_id', $programIds)
            ->groupBy('program_id')
            ->pluck('total', 'program_id');

        $rows = $classes->map(function ($class) use (
            $enrollmentGroups,
            $sessionStats,
            $attendanceCounts,
            $assignmentCounts,
            $submissions,
            $schedules,
            $documentationCounts,
            $moduleCounts,
            $today
        ) {
            $enrollmentSet = $enrollmentGroups->get($class->id, collect());
            $learningEnrollments = $enrollmentSet->filter(function ($enrollment) {
                return in_array($enrollment->status, ['active', 'approved', 'completed'], true);
            })->values();
            $participantCount = $learningEnrollments->count();

            $scores = $this->scoreSummary($learningEnrollments);
            $classSubmissions = $submissions->get($class->id, collect());
            $assignmentScores = $this->assignmentSummary($classSubmissions);
            $submissionStats = $this->submissionSummary($classSubmissions);
            
            $participantsDetail = $enrollmentSet->map(function ($enroll) {
                return [
                    'name' => $enroll->user?->name ?? '-',
                    'nik' => $enroll->user?->nik ?? '-',
                    'status' => $enroll->status,
                    'admin_status' => $enroll->admin_status,
                    'pre_test_score' => $enroll->pre_test_score,
                    'post_test_score' => $enroll->post_test_score ?? $enroll->written_score,
                    'practice_score' => $enroll->practice_score,
                    'attitude_score' => $enroll->attitude_score,
                    'attendance_rate' => $enroll->attendance_rate,
                    'final_grade' => $enroll->final_grade,
                    'competency_status' => $enroll->competency_status,
                ];
            })->values();

            $sessionStat = $sessionStats->get($class->id);
            $sessionTotal = (int) ($sessionStat->total ?? 0);
            $sessionCompleted = (int) ($sessionStat->completed ?? 0);
            $attendancePresent = (int) ($attendanceCounts[$class->id] ?? 0);
            $attendancePossible = $sessionTotal * $participantCount;
            $assignmentTotal = (int) ($assignmentCounts[$class->id] ?? 0);
            $submissionExpected = $assignmentTotal * $participantCount;
            $submissionCompletion = $this->percent($submissionStats['submitted'], $submissionExpected);
            $gradingCompletion = $this->percent($submissionStats['graded'], $submissionStats['submitted']);

            $schedule = $schedules->get($class->id);
            $programId = $schedule?->program_id;
            $kejuruan = $class->competencies[0] ?? $schedule?->program?->judul ?? '-';
            $phase = $this->resolvePhase($schedule, $sessionTotal, $sessionCompleted, $today);
            $attendanceRate = $this->percent($attendancePresent, $attendancePossible);
            $avgFinal = $this->avg($scores['final_sum'], $scores['final_count']);
            $documentationTotal = (int) ($documentationCounts[$class->id] ?? 0);
            $moduleTotal = (int) ($moduleCounts[$programId] ?? 0);
            $monitoring = $this->evaluateMonitoring($phase, [
                'participants' => $participantCount,
                'attendance_rate' => $attendanceRate,
                'submission_rate' => $submissionCompletion,
                'grading_rate' => $gradingCompletion,
                'avg_final' => $avgFinal,
                'documentation_total' => $documentationTotal,
                'module_total' => $moduleTotal,
                'pending' => $scores['pending'],
            ]);

            return [
                'class_id' => $class->id,
                'class_title' => $class->title,
                'kejuruan' => $kejuruan,
                'phase' => $phase,
                'participants' => $participantCount,
                'pre_sum' => $scores['pre_sum'],
                'pre_count' => $scores['pre_count'],
                'post_sum' => $scores['post_sum'],
                'post_count' => $scores['post_count'],
                'practice_sum' => $scores['practice_sum'],
                'practice_count' => $scores['practice_count'],
                'attitude_sum' => $scores['attitude_sum'],
                'attitude_count' => $scores['attitude_count'],
                'final_sum' => $scores['final_sum'],
                'final_count' => $scores['final_count'],
                'quiz_sum' => $assignmentScores['quiz_sum'],
                'quiz_count' => $assignmentScores['quiz_count'],
                'attendance_present' => $attendancePresent,
                'attendance_possible' => $attendancePossible,
                'attendance_rate' => $attendanceRate,
                'session_total' => $sessionTotal,
                'session_completed' => $sessionCompleted,
                'assignment_total' => $assignmentTotal,
                'submission_expected' => $submissionExpected,
                'submission_total' => $submissionStats['submitted'],
                'graded_total' => $submissionStats['graded'],
                'submission_completion_rate' => $submissionCompletion,
                'grading_completion_rate' => $gradingCompletion,
                'documentation_total' => $documentationTotal,
                'module_total' => $moduleTotal,
                'monitoring_status' => $monitoring['status'],
                'monitoring_score' => $monitoring['score'],
                'monitoring_notes' => $monitoring['notes'],
                'lulus' => $scores['lulus'],
                'tidak_lulus' => $scores['tidak_lulus'],
                'pending' => $scores['pending'],
                'participants_detail' => $participantsDetail,
            ];
        })->values();

        return [
            'rows' => $rows,
            'classes' => $classes,
        ];
    }

    public function scoreSummary(Collection $enrollments): array
    {
        $pre = $enrollments->filter(fn ($e) => $e->pre_test_score !== null)->pluck('pre_test_score');
        $post = $enrollments->map(fn ($e) => $e->post_test_score ?? $e->written_score)->filter();
        $practice = $enrollments->filter(fn ($e) => $e->practice_score !== null)->pluck('practice_score');
        $attitude = $enrollments->filter(fn ($e) => $e->attitude_score !== null)->pluck('attitude_score');
        $final = $enrollments->filter(fn ($e) => $e->final_grade !== null)->pluck('final_grade');

        return [
            'pre_sum' => $pre->sum(),
            'pre_count' => $pre->count(),
            'post_sum' => $post->sum(),
            'post_count' => $post->count(),
            'practice_sum' => $practice->sum(),
            'practice_count' => $practice->count(),
            'attitude_sum' => $attitude->sum(),
            'attitude_count' => $attitude->count(),
            'final_sum' => $final->sum(),
            'final_count' => $final->count(),
            'lulus' => $enrollments->where('competency_status', 'competent')->count(),
            'tidak_lulus' => $enrollments->where('competency_status', 'not_competent')->count(),
            'pending' => $enrollments->filter(fn ($e) => ! in_array($e->competency_status, ['competent', 'not_competent'], true))->count(),
        ];
    }

    public function assignmentSummary(Collection $submissions): array
    {
        $quizScores = $submissions->where('type', 'quiz')->pluck('quiz_score')->filter();
        $practiceScores = $submissions->where('type', '!=', 'quiz')->pluck('total_score')->filter();

        return [
            'quiz_sum' => $quizScores->sum(),
            'quiz_count' => $quizScores->count(),
            'practice_sum' => $practiceScores->sum(),
            'practice_count' => $practiceScores->count(),
        ];
    }

    public function submissionSummary(Collection $submissions): array
    {
        $pairGroups = $submissions->groupBy(function ($item) {
            return $item->course_assignment_id . '::' . $item->user_id;
        });

        $submitted = $pairGroups->count();
        $graded = $pairGroups->filter(function (Collection $attempts) {
            return $attempts->contains(function ($attempt) {
                return $attempt->status === 'graded';
            });
        })->count();

        return [
            'submitted' => $submitted,
            'graded' => $graded,
        ];
    }

    public function normalizeClassRows(Collection $rows): Collection
    {
        return $rows->map(function ($row) {
            return [
                'class_id' => $row['class_id'],
                'label' => $row['class_title'],
                'phase' => $row['phase'],
                'participants' => $row['participants'],
                'avg_pre' => $this->avg($row['pre_sum'], $row['pre_count']),
                'avg_post' => $this->avg($row['post_sum'], $row['post_count']),
                'avg_practice' => $this->avg($row['practice_sum'], $row['practice_count']),
                'avg_attitude' => $this->avg($row['attitude_sum'], $row['attitude_count']),
                'avg_final' => $this->avg($row['final_sum'], $row['final_count']),
                'avg_quiz' => $this->avg($row['quiz_sum'], $row['quiz_count']),
                'attendance_rate' => $this->percent($row['attendance_present'], $row['attendance_possible']),
                'session_total' => $row['session_total'],
                'session_completed' => $row['session_completed'],
                'session_completion_rate' => $this->percent($row['session_completed'], $row['session_total']),
                'assignment_total' => $row['assignment_total'],
                'submission_total' => $row['submission_total'],
                'submission_expected' => $row['submission_expected'],
                'submission_completion_rate' => $row['submission_completion_rate'],
                'grading_completion_rate' => $row['grading_completion_rate'],
                'documentation_total' => $row['documentation_total'],
                'module_total' => $row['module_total'],
                'monitoring_status' => $row['monitoring_status'],
                'monitoring_score' => $row['monitoring_score'],
                'monitoring_notes' => $row['monitoring_notes'],
                'lulus' => $row['lulus'],
                'tidak_lulus' => $row['tidak_lulus'],
                'pending' => $row['pending'],
                'participants_detail' => $row['participants_detail'],
            ];
        });
    }

    public function normalizeGroupedRows(Collection $rows, string $key): Collection
    {
        return $rows->groupBy($key)->map(function ($items, $label) {
            $participants = $items->sum('participants');
            $totalClasses = $items->count();
            return [
                'label' => $label,
                'classes' => $totalClasses,
                'participants' => $participants,
                'avg_pre' => $this->avg($items->sum('pre_sum'), $items->sum('pre_count')),
                'avg_post' => $this->avg($items->sum('post_sum'), $items->sum('post_count')),
                'avg_practice' => $this->avg($items->sum('practice_sum'), $items->sum('practice_count')),
                'avg_attitude' => $this->avg($items->sum('attitude_sum'), $items->sum('attitude_count')),
                'avg_final' => $this->avg($items->sum('final_sum'), $items->sum('final_count')),
                'avg_quiz' => $this->avg($items->sum('quiz_sum'), $items->sum('quiz_count')),
                'attendance_rate' => $this->percent($items->sum('attendance_present'), $items->sum('attendance_possible')),
                'session_completion_rate' => $this->percent($items->sum('session_completed'), $items->sum('session_total')),
                'submission_completion_rate' => $this->percent($items->sum('submission_total'), $items->sum('submission_expected')),
                'grading_completion_rate' => $this->percent($items->sum('graded_total'), $items->sum('submission_total')),
                'doc_coverage_rate' => $this->percent(
                    $items->filter(fn ($row) => $row['documentation_total'] > 0)->count(),
                    $totalClasses
                ),
                'critical' => $items->where('monitoring_status', 'critical')->count(),
                'warning' => $items->where('monitoring_status', 'warning')->count(),
                'ongoing' => $items->where('phase', 'ongoing')->count(),
                'upcoming' => $items->where('phase', 'upcoming')->count(),
                'completed' => $items->where('phase', 'completed')->count(),
                'lulus' => $items->sum('lulus'),
                'tidak_lulus' => $items->sum('tidak_lulus'),
                'pending' => $items->sum('pending'),
            ];
        })->values();
    }

    public function buildOverview(Collection $rows): array
    {
        $totalClasses = $rows->count();

        return [
            'classes' => $totalClasses,
            'participants' => (int) $rows->sum('participants'),
            'ongoing' => $rows->where('phase', 'ongoing')->count(),
            'upcoming' => $rows->where('phase', 'upcoming')->count(),
            'completed' => $rows->where('phase', 'completed')->count(),
            'avg_attendance' => $this->percent($rows->sum('attendance_present'), $rows->sum('attendance_possible')),
            'submission_completion' => $this->percent($rows->sum('submission_total'), $rows->sum('submission_expected')),
            'grading_completion' => $this->percent($rows->sum('graded_total'), $rows->sum('submission_total')),
            'avg_final' => $this->avg($rows->sum('final_sum'), (int) $rows->sum('final_count')),
            'critical' => $rows->where('monitoring_status', 'critical')->count(),
            'warning' => $rows->where('monitoring_status', 'warning')->count(),
            'healthy' => $rows->where('monitoring_status', 'healthy')->count(),
            'without_docs' => $rows->where('documentation_total', 0)->count(),
        ];
    }

    public function buildAlerts(Collection $rows): Collection
    {
        return $rows
            ->filter(function ($row) {
                return in_array($row['monitoring_status'], ['critical', 'warning'], true);
            })
            ->sortByDesc('monitoring_score')
            ->map(function ($row) {
                return [
                    'class_title' => $row['class_title'],
                    'kejuruan' => $row['kejuruan'],
                    'phase' => $row['phase'],
                    'status' => $row['monitoring_status'],
                    'notes' => $row['monitoring_notes'],
                    'attendance_rate' => $row['attendance_rate'],
                    'submission_completion_rate' => $row['submission_completion_rate'],
                    'grading_completion_rate' => $row['grading_completion_rate'],
                ];
            })
            ->take(8)
            ->values();
    }

    public function resolvePhase(?TrainingSchedule $schedule, int $sessionTotal, int $sessionCompleted, $today): string
    {
        $start = $schedule?->mulai?->copy()->startOfDay();
        $end = $schedule?->selesai?->copy()->endOfDay();

        if ($start && $today->lt($start)) {
            return 'upcoming';
        }

        if ($end && $today->gt($end)) {
            return 'completed';
        }

        if ($sessionTotal > 0 && $sessionCompleted >= $sessionTotal) {
            return 'completed';
        }

        return 'ongoing';
    }

    public function evaluateMonitoring(string $phase, array $metrics): array
    {
        if ($phase === 'upcoming') {
            return [
                'status' => 'upcoming',
                'score' => 0,
                'notes' => ['Kelas belum berjalan.'],
            ];
        }

        $score = 0;
        $notes = [];

        if (($metrics['participants'] ?? 0) === 0) {
            $score += 2;
            $notes[] = 'Belum ada peserta aktif.';
        }

        $attendanceRate = $metrics['attendance_rate'];
        if ($attendanceRate !== null) {
            if ($attendanceRate < 75) {
                $score += 2;
                $notes[] = 'Kehadiran di bawah 75%.';
            } elseif ($attendanceRate < 85) {
                $score += 1;
                $notes[] = 'Kehadiran belum stabil.';
            }
        }

        $submissionRate = $metrics['submission_rate'];
        if ($submissionRate !== null) {
            if ($submissionRate < 70) {
                $score += 2;
                $notes[] = 'Ketuntasan tugas rendah.';
            } elseif ($submissionRate < 85) {
                $score += 1;
                $notes[] = 'Ketuntasan tugas perlu ditingkatkan.';
            }
        }

        $gradingRate = $metrics['grading_rate'];
        if ($gradingRate !== null) {
            if ($gradingRate < 70) {
                $score += 2;
                $notes[] = 'Penilaian tugas tertunda.';
            } elseif ($gradingRate < 85) {
                $score += 1;
                $notes[] = 'Penilaian tugas belum merata.';
            }
        }

        $avgFinal = $metrics['avg_final'];
        if ($avgFinal !== null) {
            if ($avgFinal < 70) {
                $score += 2;
                $notes[] = 'Rata-rata nilai akhir rendah.';
            } elseif ($avgFinal < 75) {
                $score += 1;
                $notes[] = 'Rata-rata nilai akhir borderline.';
            }
        }

        if (($metrics['documentation_total'] ?? 0) <= 0) {
            $score += 1;
            $notes[] = 'Dokumentasi pelatihan belum tersedia.';
        }

        if (($metrics['module_total'] ?? 0) <= 0) {
            $score += 1;
            $notes[] = 'Modul kejuruan belum tersedia.';
        }

        if (($metrics['pending'] ?? 0) > 0 && $phase === 'completed') {
            $score += 1;
            $notes[] = 'Masih ada peserta dengan status kompetensi pending.';
        }

        if ($score >= 4) {
            $status = 'critical';
        } elseif ($score >= 2) {
            $status = 'warning';
        } else {
            $status = 'healthy';
        }

        return [
            'status' => $status,
            'score' => $score,
            'notes' => empty($notes) ? ['Monitoring stabil.'] : $notes,
        ];
    }

    public function avg(float|int $sum, int $count): ?float
    {
        return $count > 0 ? round($sum / $count, 2) : null;
    }

    public function percent(float|int $value, float|int $total): ?float
    {
        return $total > 0 ? round(($value / $total) * 100, 2) : null;
    }
}
