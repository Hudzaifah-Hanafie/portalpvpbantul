<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RestrictsToInstructorClasses;
use App\Models\CourseAttendance;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\CourseSubmission;
use Illuminate\Http\Request;

class CourseProgressController extends Controller
{
    use RestrictsToInstructorClasses;

    public function index()
    {
        $classFilter = request('class_id');
        $classes = $this->scopedClassOptions(request()->user());

        $records = collect();
        $selectedClass = null;
        if ($classFilter) {
            $this->ensureInstructorOwnsClassId(request()->user(), $classFilter);
            $selectedClass = CourseClass::find($classFilter);
            
            $enrollments = CourseEnrollment::with('user')
                ->where('course_class_id', $classFilter)
                ->whereIn('status', ['active', 'approved'])
                ->get();
                
            $userIds = $enrollments->pluck('user_id');

            $allAttendances = CourseAttendance::whereHas('session', fn ($q) => $q->where('course_class_id', $classFilter))
                ->whereIn('user_id', $userIds)
                ->get()
                ->groupBy('user_id');

            $allSubmissions = CourseSubmission::whereHas('assignment', fn ($q) => $q->where('course_class_id', $classFilter))
                ->whereIn('user_id', $userIds)
                ->get()
                ->groupBy('user_id');

            $records = $enrollments->map(function ($enroll) use ($allAttendances, $allSubmissions) {
                $userAttendances = $allAttendances->get($enroll->user_id, collect());
                $attended = $userAttendances->where('status', 'hadir')->count();
                $totalSessions = $userAttendances->count();

                $userSubmissions = $allSubmissions->get($enroll->user_id, collect());
                $submittedCount = $userSubmissions->count();
                $gradedCount = $userSubmissions->where('status', 'graded')->count();
                $avgScore = $userSubmissions->whereNotNull('total_score')->avg('total_score');

                return [
                    'user' => $enroll->user,
                    'attended' => $attended,
                    'total_sessions' => $totalSessions,
                    'attendance_rate' => $totalSessions > 0 ? round(($attended / $totalSessions) * 100, 1) : null,
                    'submitted' => $submittedCount,
                    'graded' => $gradedCount,
                    'avg_score' => $avgScore ? round($avgScore, 1) : null,
                ];
            });
        }

        return view('admin.course_progress.index', compact('records', 'classes', 'classFilter', 'selectedClass'));
    }
}
