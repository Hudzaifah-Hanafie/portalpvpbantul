<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\CourseAnnouncement;
use App\Models\CourseClass;
use App\Models\CourseSession;
use Illuminate\Http\Request;

class InstructorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $classes = CourseClass::where('instructor_id', $user->id)
            ->withCount(['sessions', 'assignments'])
            ->orderBy('title')
            ->get();

        $classIds = $classes->pluck('id');
        $upcomingSessions = CourseSession::with('course')
            ->whereIn('course_class_id', $classIds)
            ->where('status', 'published')
            ->where('is_active', true)
            ->orderBy('start_at')
            ->take(5)
            ->get();
        $recentAnnouncements = CourseAnnouncement::with('course')
            ->whereIn('course_class_id', $classIds)
            ->orderByDesc('published_at')
            ->take(5)
            ->get();

        return view('instructor.dashboard', compact('classes', 'upcomingSessions', 'recentAnnouncements'));
    }

    public function storeSession(Request $request)
    {
        $data = $request->validate([
            'course_class_id' => 'required|exists:course_classes,id',
            'title' => 'required|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'meeting_link' => 'nullable|string|max:255',
            'attendance_code_expires_at' => 'nullable|date|after_or_equal:start_at',
        ]);

        $class = CourseClass::where('id', $data['course_class_id'])
            ->where('instructor_id', $request->user()->id)
            ->firstOrFail();

        CourseSession::create([
            'course_class_id' => $class->id,
            'title' => $data['title'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'] ?? null,
            'meeting_link' => $data['meeting_link'] ?? null,
            'attendance_code_expires_at' => $data['attendance_code_expires_at'] ?? null,
            'status' => 'published',
            'is_active' => true,
            'created_by' => $request->user()->id,
            'published_at' => now(),
        ]);

        return redirect()->route('instructor.dashboard')->with('success', 'Sesi berhasil dibuat.');
    }

    public function storeAnnouncement(Request $request)
    {
        $data = $request->validate([
            'course_class_id' => 'required|exists:course_classes,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $class = CourseClass::where('id', $data['course_class_id'])
            ->where('instructor_id', $request->user()->id)
            ->firstOrFail();

        CourseAnnouncement::create([
            'course_class_id' => $class->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'status' => 'published',
            'published_at' => now(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('instructor.dashboard')->with('success', 'Pengumuman berhasil dikirim.');
    }
}
