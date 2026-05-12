<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RestrictsToInstructorClasses;
use App\Models\CourseAttendance;
use App\Models\CourseClass;
use App\Models\CourseSession;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class CourseAttendanceController extends Controller
{
    use RestrictsToInstructorClasses;

    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index()
    {
        $statusOptions = CourseAttendance::statuses();
        $statusFilter = request('status');
        $classFilter = request('class_id');
        $sessionFilter = request('session_id');

        $query = CourseAttendance::with(['session.course', 'user'])->orderBy('checked_at', 'desc');
        if ($this->isInstructorUser(request()->user())) {
            $classIds = CourseClass::where('instructor_id', request()->user()->id)->pluck('id');
            $query->whereHas('session', fn ($q) => $q->whereIn('course_class_id', $classIds));
        }

        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }
        if ($sessionFilter) {
            $query->where('course_session_id', $sessionFilter);
        } elseif ($classFilter) {
            $query->whereHas('session', fn ($q) => $q->where('course_class_id', $classFilter));
        }

        $attendances = $query->paginate(25)->withQueryString();
        $classes = $this->scopedClassOptions(request()->user());
        $sessions = CourseSession::orderBy('start_at')
            ->when($this->isInstructorUser(request()->user()), function ($q) {
                $classIds = CourseClass::where('instructor_id', request()->user()->id)->pluck('id');
                $q->whereIn('course_class_id', $classIds);
            })
            ->pluck('title', 'id');

        return view('admin.course_attendance.index', compact('attendances', 'statusOptions', 'statusFilter', 'classes', 'classFilter', 'sessions', 'sessionFilter'));
    }

    public function create()
    {
        $classes = $this->scopedClassOptions(request()->user());
        $sessions = CourseSession::orderBy('start_at')
            ->when($this->isInstructorUser(request()->user()), function ($q) {
                $classIds = CourseClass::where('instructor_id', request()->user()->id)->pluck('id');
                $q->whereIn('course_class_id', $classIds);
            })
            ->pluck('title', 'id');

        return view('admin.course_attendance.form', [
            'attendance' => new CourseAttendance(),
            'classes' => $classes,
            'sessions' => $sessions,
            'action' => route($this->getRoutePrefix() . 'course-attendance.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $session = CourseSession::find($data['course_session_id']);
        if ($session) {
            $this->ensureInstructorOwnsClassId($request->user(), $session->course_class_id);
        }
        $data['created_by'] = $request->user()->id;
        $data['recorded_by'] = $request->user()->id;
        $data['recorded_source'] = 'operator';
        $data['checked_at'] = $data['checked_at'] ?? now();

        $attendance = CourseAttendance::create($data);

        $this->logger->log(
            $request->user(),
            'course.attendance.created',
            "Presensi '{$attendance->user_id}' untuk sesi '{$attendance->session?->title}' ditambahkan",
            $attendance
        );

        return redirect()->route($this->getRoutePrefix() . 'course-attendance.index')->with('success', 'Presensi ditambahkan.');
    }

    public function edit(CourseAttendance $course_attendance)
    {
        $sessionClassId = $course_attendance->session?->course_class_id;
        if ($sessionClassId) {
            $this->ensureInstructorOwnsClassId(request()->user(), $sessionClassId);
        }
        $classes = $this->scopedClassOptions(request()->user());
        $sessions = CourseSession::orderBy('start_at')
            ->when($this->isInstructorUser(request()->user()), function ($q) {
                $classIds = CourseClass::where('instructor_id', request()->user()->id)->pluck('id');
                $q->whereIn('course_class_id', $classIds);
            })
            ->pluck('title', 'id');

        return view('admin.course_attendance.form', [
            'attendance' => $course_attendance,
            'classes' => $classes,
            'sessions' => $sessions,
            'action' => route($this->getRoutePrefix() . 'course-attendance.update', $course_attendance->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, CourseAttendance $course_attendance)
    {
        $data = $this->validateData($request);
        $session = CourseSession::find($data['course_session_id']);
        if ($session) {
            $this->ensureInstructorOwnsClassId($request->user(), $session->course_class_id);
        }
        $course_attendance->update($data);

        $this->logger->log(
            $request->user(),
            'course.attendance.updated',
            "Presensi '{$course_attendance->user_id}' untuk sesi '{$course_attendance->session?->title}' diperbarui",
            $course_attendance
        );

        return redirect()->route($this->getRoutePrefix() . 'course-attendance.index')->with('success', 'Presensi diperbarui.');
    }

    public function destroy(CourseAttendance $course_attendance)
    {
        $sessionClassId = $course_attendance->session?->course_class_id;
        if ($sessionClassId) {
            $this->ensureInstructorOwnsClassId(request()->user(), $sessionClassId);
        }
        $this->logger->log(
            request()->user(),
            'course.attendance.deleted',
            "Presensi '{$course_attendance->user_id}' dihapus",
            $course_attendance
        );
        $course_attendance->delete();
        return redirect()->route($this->getRoutePrefix() . 'course-attendance.index')->with('success', 'Presensi dihapus.');
    }

    public function exportCsv(Request $request)
    {
        $query = CourseAttendance::with(['session.course', 'user']);
        if ($this->isInstructorUser($request->user())) {
            $classIds = CourseClass::where('instructor_id', $request->user()->id)->pluck('id');
            $query->whereHas('session', fn ($q) => $q->whereIn('course_class_id', $classIds));
        }
        if ($request->filled('class_id')) {
            $query->whereHas('session', fn ($q) => $q->where('course_class_id', $request->input('class_id')));
        }
        if ($request->filled('session_id')) {
            $query->where('course_session_id', $request->input('session_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $filename = 'attendance-' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['user_id', 'nama', 'kelas', 'sesi', 'status', 'checked_at', 'reason', 'proof_url']);
            
            // Menggunakan cursor() agar iterasi data langsung dilakukan dari driver DB 
            // tanpa memuat koleksi besar (ribuan array) ke dalam RAM. Memori server akan stabil.
            foreach ($query->cursor() as $row) {
                fputcsv($out, [
                    $row->user_id,
                    $row->user->name ?? '',
                    $row->session?->course?->title ?? '',
                    $row->session?->title ?? '',
                    $row->status,
                    $row->checked_at,
                    $row->reason,
                    $row->proof_url,
                ]);
            }
            
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv']);
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'course_session_id' => 'required|exists:course_sessions,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:' . implode(',', array_keys(CourseAttendance::statuses())),
            'reason' => 'nullable|string',
            'proof_url' => 'nullable|string|max:255',
            'checked_at' => 'nullable|date',
        ]);

        return $data;
    }
}
