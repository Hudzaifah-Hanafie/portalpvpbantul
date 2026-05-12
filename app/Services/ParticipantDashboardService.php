<?php

namespace App\Services;

use App\Models\User;
use App\Models\CourseClass;
use App\Models\CourseSession;
use App\Models\CourseAssignment;
use App\Models\CourseSubmission;
use App\Models\CourseAttendance;
use App\Models\CourseEnrollment;

class ParticipantDashboardService
{
    public function getDashboardData(User $user, array $enrolledIds): array
    {
        $classes = CourseClass::whereIn('id', $enrolledIds)
            ->with('instructor')
            ->withCount([
                'assignments' => fn ($q) => $q->where('status', 'published')->where('is_active', true),
                'sessions' => fn ($q) => $q->where('status', 'published')->where('is_active', true),
            ])
            ->orderBy('title')
            ->get();

        $now = now();

        $sessions = CourseSession::with('course')
            ->whereIn('course_class_id', $enrolledIds)
            ->where('status', 'published')
            ->where('is_active', true)
            ->orderBy('start_at')
            ->get();

        $upcomingSessions = CourseSession::with('course')
            ->whereIn('course_class_id', $enrolledIds)
            ->where('status', 'published')
            ->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->where('end_at', '>=', $now)
                      ->orWhere(function ($q) use ($now) {
                          $q->whereNull('end_at')->where('start_at', '>=', $now);
                      });
            })
            ->orderBy('start_at')
            ->take(5)
            ->get();

        // Optimize Next Session By Class
        $nextSessionByClass = $sessions->filter(function ($session) use ($now) {
            if ($session->end_at) {
                return $session->end_at->gte($now);
            }
            return $session->start_at && $session->start_at->gte($now);
        })->groupBy('course_class_id')->map->first();

        $totalSessionsByClass = $sessions->groupBy('course_class_id')->map->count();

        $attendances = CourseAttendance::with('session')
            ->where('user_id', $user->id)
            ->whereIn('course_session_id', $sessions->pluck('id'))
            ->get();
            
        $attendanceByClass = collect();
        foreach ($attendances as $row) {
            if ($row->status === 'hadir' && $row->session) {
                $classId = $row->session->course_class_id;
                $attendanceByClass[$classId] = ($attendanceByClass[$classId] ?? 0) + 1;
            }
        }

        $assignmentQuery = CourseAssignment::with('course')
            ->whereIn('course_class_id', $enrolledIds)
            ->where('status', 'published')
            ->where('is_active', true);
            
        $assignments = $assignmentQuery->get();
        
        $submittedAssignmentIds = CourseSubmission::where('user_id', $user->id)
            ->whereIn('course_assignment_id', $assignments->pluck('id'))
            ->pluck('course_assignment_id')
            ->unique();
            
        $pendingAssignmentsCount = $assignments->pluck('id')->diff($submittedAssignmentIds)->count();

        $upcomingAssignments = CourseAssignment::with('course')
            ->whereIn('course_class_id', $enrolledIds)
            ->where('status', 'published')
            ->where('is_active', true)
            ->whereNotNull('due_at')
            ->orderBy('due_at')
            ->take(5)
            ->get();
            
        $upcomingSubmissionMap = CourseSubmission::where('user_id', $user->id)
            ->whereIn('course_assignment_id', $upcomingAssignments->pluck('id'))
            ->orderByDesc('version')
            ->get()
            ->keyBy('course_assignment_id');

        $totalSessions = $sessions->count();
        $attendedSessions = $attendances->where('status', 'hadir')->count();
        $attendanceRate = $totalSessions > 0 ? round(($attendedSessions / $totalSessions) * 100, 1) : null;
        $pendingCount = CourseEnrollment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $hasEnrollment = CourseEnrollment::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved', 'active', 'completed'])
            ->exists();
            
        $hasSubmission = false;
        if ($assignments->isNotEmpty()) {
            $hasSubmission = CourseSubmission::where('user_id', $user->id)
                ->whereIn('course_assignment_id', $assignments->pluck('id'))
                ->exists();
        }
        
        $hasAttendance = false;
        if ($sessions->isNotEmpty()) {
            $hasAttendance = CourseAttendance::where('user_id', $user->id)
                ->whereIn('course_session_id', $sessions->pluck('id'))
                ->exists();
        }
        
        $profileComplete = (bool) ($user->profile?->phone);
        
        $latestEnrollment = CourseEnrollment::with(['course', 'interviewAllocations.session'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->first();
            
        $selectionQuizExists = false;
        if ($latestEnrollment?->course_class_id) {
            $selectionQuizExists = CourseAssignment::where('course_class_id', $latestEnrollment->course_class_id)
                ->where('type', 'quiz')
                ->where('quiz_scope', 'selection')
                ->where('status', 'published')
                ->where('is_active', true)
                ->exists();
        }
        
        $adminVerified = $latestEnrollment?->admin_status === 'verified';
        $adminRejected = $latestEnrollment?->admin_status === 'rejected';
        $cbtDone = $latestEnrollment?->written_score !== null || ! $selectionQuizExists;
        $interviewDone = $latestEnrollment?->interview_score !== null;
        $hasActiveClass = $latestEnrollment && in_array($latestEnrollment->status, ['approved', 'active', 'completed'], true);

        $onboardingSteps = [
            [
                'key' => 'profile',
                'label' => 'Selesaikan profil',
                'done' => $profileComplete,
                'note' => $profileComplete ? 'Profil lengkap' : 'Lengkapi nomor HP & biodata',
                'cta' => 'Lengkapi Profil',
                'url' => route('profile.show'),
            ],
            [
                'key' => 'enroll',
                'label' => 'Ikut kelas pelatihan',
                'done' => $hasEnrollment,
                'note' => $hasEnrollment ? 'Pendaftaran tercatat' : 'Pilih program pelatihan',
                'cta' => 'Cari Pelatihan',
                'url' => route('program'),
            ],
        ];
        if ($latestEnrollment) {
            $onboardingSteps[] = [
                'key' => 'admin_verify',
                'label' => 'Verifikasi Administrasi',
                'done' => $adminVerified,
                'note' => $adminRejected ? 'Ditolak admin' : ($adminVerified ? 'Terverifikasi' : 'Menunggu verifikasi'),
                'cta' => 'Lihat Status',
                'url' => route('participant.applications'),
                'status' => $adminRejected ? 'danger' : ($adminVerified ? 'success' : 'warning'),
            ];
            if ($selectionQuizExists) {
                $onboardingSteps[] = [
                    'key' => 'cbt',
                    'label' => 'CBT Seleksi',
                    'done' => $cbtDone,
                    'note' => $cbtDone ? 'CBT selesai' : 'Mulai CBT',
                    'cta' => 'Mulai CBT',
                    'url' => route('participant.assignments', ['class_id' => $latestEnrollment->course_class_id]),
                ];
            }
            $onboardingSteps[] = [
                'key' => 'interview',
                'label' => 'Wawancara',
                'done' => $interviewDone,
                'note' => $interviewDone ? 'Wawancara selesai' : 'Menunggu jadwal',
                'cta' => 'Lihat Jadwal',
                'url' => route('participant.interviews'),
            ];
        }
        if ($hasActiveClass) {
            $onboardingSteps[] = [
                'key' => 'assignment',
                'label' => 'Kerjakan tugas/quiz',
                'done' => $hasSubmission,
                'note' => $hasSubmission ? 'Sudah mulai mengerjakan' : 'Cek tugas pertama',
                'cta' => 'Lihat Tugas',
                'url' => route('participant.assignments'),
            ];
            $onboardingSteps[] = [
                'key' => 'attendance',
                'label' => 'Isi presensi sesi pertama',
                'done' => $hasAttendance,
                'note' => $hasAttendance ? 'Presensi terisi' : 'Isi presensi sesi aktif',
                'cta' => 'Isi Presensi',
                'url' => route('participant.sessions.index'),
            ];
        }
        $onboardingProgress = collect($onboardingSteps)->filter(fn ($step) => $step['done'])->count();

        return [
            'classes' => $classes,
            'nextSessionByClass' => $nextSessionByClass,
            'totalSessionsByClass' => $totalSessionsByClass,
            'attendanceByClass' => $attendanceByClass,
            'upcomingSessions' => $upcomingSessions,
            'upcomingAssignments' => $upcomingAssignments,
            'upcomingSubmissionMap' => $upcomingSubmissionMap,
            'classesCount' => $classes->count(),
            'pendingAssignmentsCount' => $pendingAssignmentsCount,
            'attendanceRate' => $attendanceRate,
            'pendingCount' => $pendingCount,
            'onboardingSteps' => $onboardingSteps,
            'onboardingProgress' => $onboardingProgress,
            'latestEnrollment' => $latestEnrollment,
        ];
    }
}
