<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\CourseAnnouncement;
use App\Models\CourseAssignment;
use App\Models\CourseAttendance;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\CourseSession;
use App\Models\CourseSubmission;
use App\Services\ParticipantDashboardService;
use App\Traits\ParticipantHelper;
use Illuminate\Http\Request;

class ParticipantClassController extends Controller
{
    use ParticipantHelper;

    public function dashboard(Request $request, ParticipantDashboardService $dashboardService)
    {
        $user = $request->user()->load('profile');
        $enrolledIds = $this->enrolledClassIds($user);

        $data = $dashboardService->getDashboardData($user, $enrolledIds);
        
        $this->shareConsentBanner($request, collect($data['classes']));

        return view('participant.dashboard', $data);
    }

    public function myApplications(Request $request)
    {
        $user = $request->user();
        $enrollments = CourseEnrollment::with([
            'course',
            'trainingSchedule',
            'interviewAllocations.session',
            'interviewAllocations.score',
        ])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $classIds = $enrollments->pluck('course_class_id')->filter()->unique()->values();
        $classes = CourseClass::with([
            'sessions' => fn ($q) => $q->where('status', 'published')->where('is_active', true),
            'assignments' => fn ($q) => $q->where('status', 'published')
                ->where('is_active', true)
                ->where(function ($inner) {
                    $inner->whereNull('quiz_scope')
                        ->orWhere('quiz_scope', '!=', 'selection');
                }),
        ])
            ->whereIn('id', $classIds)
            ->get()
            ->keyBy('id');

        $assignmentIds = $classes->flatMap(fn ($class) => $class->assignments->pluck('id'))->unique()->values();
        $submissionMap = CourseSubmission::where('user_id', $user->id)
            ->whereIn('course_assignment_id', $assignmentIds)
            ->orderByDesc('version')
            ->get()
            ->groupBy('course_assignment_id')
            ->map->first();

        $sessionIds = $classes->flatMap(fn ($class) => $class->sessions->pluck('id'))->unique()->values();
        $attendanceMap = CourseAttendance::where('user_id', $user->id)
            ->whereIn('course_session_id', $sessionIds)
            ->get()
            ->groupBy('course_session_id')
            ->map->first();
        $quizAssignments = CourseAssignment::whereIn('course_class_id', $classIds)
            ->where('type', 'quiz')
            ->where('quiz_scope', 'selection')
            ->where('status', 'published')
            ->where('is_active', true)
            ->get();

        $quizAssignmentsByClass = $quizAssignments->groupBy('course_class_id');
        $cbtInfoByClass = [];
        foreach ($classIds as $classId) {
            $cbtInfoByClass[$classId] = $this->describeCbtWindow(
                $quizAssignmentsByClass->get($classId, collect())
            );
        }

        $classTimeline = [];
        foreach ($classIds as $classId) {
            $class = $classes->get($classId);
            if (! $class) {
                continue;
            }

            $assignments = $class->assignments ?? collect();
            $sessions = $class->sessions ?? collect();
            $assignmentTotal = $assignments->count();
            $submittedCount = $assignments->filter(fn ($a) => $submissionMap->get($a->id))->count();
            $gradedCount = $assignments->filter(fn ($a) => ($submissionMap->get($a->id)?->status === 'graded'))->count();

            $finalProject = $assignments->firstWhere('assessment_type', 'final_project');
            $finalExam = $assignments->firstWhere('assessment_type', 'final_exam');
            $finalProjectDone = $finalProject && ($submissionMap->get($finalProject->id)?->status === 'graded');
            $finalExamDone = $finalExam && ($submissionMap->get($finalExam->id)?->status === 'graded');

            $sessionTotal = $sessions->count();
            $attended = $sessions->filter(function ($session) use ($attendanceMap) {
                $attendance = $attendanceMap->get($session->id);
                return $attendance?->status === 'hadir';
            })->count();
            $attendanceRate = $sessionTotal > 0 ? round(($attended / $sessionTotal) * 100, 1) : null;

            $classTimeline[$classId] = [
                'assignment_total' => $assignmentTotal,
                'assignment_submitted' => $submittedCount,
                'assignment_graded' => $gradedCount,
                'session_total' => $sessionTotal,
                'session_attended' => $attended,
                'attendance_rate' => $attendanceRate,
                'final_project_done' => $finalProjectDone,
                'final_exam_done' => $finalExamDone,
            ];
        }

        $statusOptions = CourseEnrollment::statuses();
        $adminStatuses = CourseEnrollment::adminStatuses();

        return view('participant.applications.index', compact(
            'enrollments',
            'cbtInfoByClass',
            'classTimeline',
            'statusOptions',
            'adminStatuses'
        ));
    }

    public function myClasses(Request $request)
    {
        $enrolledIds = $this->enrolledClassIds($request->user());
        $classes = CourseClass::whereIn('id', $enrolledIds)
            ->withCount(['assignments' => fn ($q) => $q->where('status', 'published'), 'sessions'])
            ->orderBy('title')
            ->get();

        $sessions = CourseSession::whereIn('course_class_id', $enrolledIds)
            ->where('status', 'published')
            ->where('is_active', true)
            ->orderBy('start_at')
            ->get();
        $nextSessionByClass = $sessions->filter(function ($session) {
            $now = now();
            if ($session->end_at) {
                return $session->end_at->gte($now);
            }
            return $session->start_at && $session->start_at->gte($now);
        })->groupBy('course_class_id')->map->first();
        $totalSessionsByClass = $sessions->groupBy('course_class_id')->map->count();
        $attendances = CourseAttendance::with('session')
            ->where('user_id', $request->user()->id)
            ->whereIn('course_session_id', $sessions->pluck('id'))
            ->get();
        $attendanceByClass = $attendances
            ->where('status', 'hadir')
            ->groupBy(fn ($row) => $row->session?->course_class_id)
            ->map->count();

        $this->shareConsentBanner($request, $classes);

        return view('participant.classes.index', compact('classes', 'nextSessionByClass', 'totalSessionsByClass', 'attendanceByClass'));
    }

    public function showClass(Request $request, CourseClass $class)
    {
        $this->enforceEnrollment($request->user(), $class->id);

        $class->load('instructor');
        $modules = $class->modules()
            ->with(['materials' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            }])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        $assignments = $class->assignments()
            ->where('status', 'published')
            ->where('is_active', true)
            ->get();
        $sessions = $class->sessions()
            ->where('status', 'published')
            ->where('is_active', true)
            ->get();
        $announcements = $class->announcements()
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->take(5)
            ->get();

        $submissionMap = CourseSubmission::where('user_id', $request->user()->id)
            ->whereIn('course_assignment_id', $assignments->pluck('id'))
            ->orderByDesc('version')
            ->get()
            ->keyBy('course_assignment_id');

        $attendanceMap = CourseAttendance::where('user_id', $request->user()->id)
            ->whereIn('course_session_id', $sessions->pluck('id'))
            ->get()
            ->keyBy('course_session_id');

        $attended = $attendanceMap->where('status', 'hadir')->count();
        $totalSessions = $sessions->count();
        $attendanceRate = $totalSessions > 0 ? round(($attended / $totalSessions) * 100, 1) : null;

        $gradedSubmissions = $submissionMap->filter(fn ($submission) => $submission->status === 'graded');
        $averageScore = $gradedSubmissions->isNotEmpty()
            ? round($gradedSubmissions->avg('total_score'), 1)
            : null;
        $finalProject = $assignments->firstWhere('assessment_type', 'final_project');
        $finalExam = $assignments->firstWhere('assessment_type', 'final_exam');
        $finalProjectDone = $finalProject
            ? ($submissionMap->get($finalProject->id)?->status === 'graded')
            : null;
        $finalExamDone = $finalExam
            ? ($submissionMap->get($finalExam->id)?->status === 'graded')
            : null;

        $rubricAssignments = $assignments->filter(fn ($assignment) => ! empty($assignment->rubric));
        $rubricCompleted = $rubricAssignments->filter(function ($assignment) use ($submissionMap) {
            return $submissionMap->get($assignment->id)?->status === 'graded';
        })->count();

        $competencies = $class->competencies ?? [];
        $competencyAchieved = $averageScore !== null && $averageScore >= 70 && ($finalProjectDone !== false);
        $competencyChecklist = collect($competencies)->map(fn ($item) => [
            'label' => $item,
            'done' => $competencyAchieved,
        ])->values();

        $attendanceOk = $attendanceRate !== null ? $attendanceRate >= 80 : null;
        $scoreOk = $averageScore !== null ? $averageScore >= 70 : null;
        $graduationEligible = [
            'attendance' => $attendanceOk,
            'score' => $scoreOk,
            'final_project' => $finalProjectDone,
            'final_exam' => $finalExamDone,
        ];

        $completedMaterialIds = \App\Models\CourseMaterialProgress::where('user_id', $request->user()->id)
            ->whereHas('courseMaterial.module', fn($q) => $q->where('course_class_id', $class->id))
            ->where('is_completed', true)
            ->pluck('course_material_id')
            ->toArray();

        return view('participant.classes.show', compact(
            'class',
            'modules',
            'assignments',
            'sessions',
            'announcements',
            'submissionMap',
            'attendanceMap',
            'attendanceRate',
            'averageScore',
            'rubricAssignments',
            'rubricCompleted',
            'competencyChecklist',
            'graduationEligible',
            'completedMaterialIds'
        ));
    }

    public function classAnnouncements(Request $request, CourseClass $class)
    {
        $this->enforceEnrollment($request->user(), $class->id);
        $announcements = CourseAnnouncement::where('course_class_id', $class->id)
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('participant.announcements.index', compact('class', 'announcements'));
    }

    public function showAnnouncement(Request $request, CourseClass $class, CourseAnnouncement $announcement)
    {
        $this->enforceEnrollment($request->user(), $class->id);
        abort_unless($announcement->course_class_id === $class->id && $announcement->status === 'published', 404);

        return view('participant.announcements.show', compact('class', 'announcement'));
    }

    public function myProgress(Request $request)
    {
        $enrolledIds = $this->enrolledClassIds($request->user());
        $classes = CourseClass::whereIn('id', $enrolledIds)->with('instructor')->get();

        $this->shareConsentBanner($request, $classes);

        $sessions = CourseSession::whereIn('course_class_id', $enrolledIds)->get();
        $attendances = CourseAttendance::with('session')
            ->whereIn('course_session_id', $sessions->pluck('id'))
            ->where('user_id', $request->user()->id)
            ->get();
            
        $assignments = CourseAssignment::whereIn('course_class_id', $enrolledIds)->get();
        $submissions = CourseSubmission::with('assignment')
            ->whereIn('course_assignment_id', $assignments->pluck('id'))
            ->where('user_id', $request->user()->id)
            ->get();
        
        $attendancesGrouped = $attendances->groupBy(fn($att) => $att->session->course_class_id ?? 0);
        $submissionsGrouped = $submissions->groupBy(fn($sub) => $sub->assignment->course_class_id ?? 0);

        $rows = $classes->map(function (CourseClass $class) use ($attendancesGrouped, $submissionsGrouped) {
            $classAttendances = $attendancesGrouped->get($class->id, collect());
            $attended = $classAttendances->where('status', 'hadir')->count();
            $totalSessions = $classAttendances->count();

            $classSubmissions = $submissionsGrouped->get($class->id, collect());

            return [
                'class' => $class,
                'attended' => $attended,
                'total_sessions' => $totalSessions,
                'attendance_rate' => $totalSessions > 0 ? round(($attended / $totalSessions) * 100, 1) : null,
                'submitted' => $classSubmissions->count(),
                'graded' => $classSubmissions->where('status', 'graded')->count(),
                'avg_score' => ($avg = $classSubmissions->whereNotNull('total_score')->avg('total_score')) ? round($avg, 1) : null,
            ];
        });

        return view('participant.progress', ['classes' => $rows]);
    }

    public function consent(Request $request)
    {
        $data = $request->validate([
            'class_id' => 'required|exists:course_classes,id',
        ]);
        $consented = collect((array) $request->session()->get('consented_classes', []));
        $consented->push($data['class_id']);
        $request->session()->put('consented_classes', $consented->unique()->values()->all());
        $request->session()->forget('consent_required');
        $request->session()->forget('consent_class');

        return back()->with('success', 'Terima kasih, persetujuan Anda tercatat.');
    }
}
