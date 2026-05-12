<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RestrictsToInstructorClasses;
use App\Models\CourseAnnouncement;
use App\Models\CourseAssignment;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\CourseSession;
use Illuminate\Http\Request;

class LmsSearchController extends Controller
{
    use RestrictsToInstructorClasses;

    public function __invoke(Request $request)
    {
        $user = $request->user();
        if (! $user || ! $user->hasAnyRole(['admin', 'superadmin', 'instructor', 'instruktur'])) {
            abort(403);
        }

        if (is_array($request->input('q'))) {
            abort(400, 'Parameter pencarian tidak valid.');
        }
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
        ]);
        $query = trim((string) ($validated['q'] ?? ''));
        $limit = 8;

        $classQuery = $this->scopeClassesForUser(CourseClass::query(), $user);
        if ($query !== '') {
            $classQuery->where(function ($q) use ($query) {
                $q->where('title', 'ILIKE', "%{$query}%")
                    ->orWhere('description', 'ILIKE', "%{$query}%");
            });
        }
        $classes = $classQuery->orderBy('title')->limit($limit)->get();

        $enrollmentQuery = CourseEnrollment::with(['user', 'course'])->orderByDesc('created_at');
        if ($this->isInstructorUser($user)) {
            $enrollmentQuery->whereHas('course', fn ($q) => $q->where('instructor_id', $user->id));
        }
        if ($query !== '') {
            $enrollmentQuery->where(function ($q) use ($query) {
                $q->whereHas('user', function ($u) use ($query) {
                    $u->where('name', 'ILIKE', "%{$query}%")
                        ->orWhere('email', 'ILIKE', "%{$query}%")
                        ->orWhere('nik', 'ILIKE', "%{$query}%");
                })->orWhereHas('course', function ($c) use ($query) {
                    $c->where('title', 'ILIKE', "%{$query}%");
                });
            });
        }
        $enrollments = $enrollmentQuery->limit($limit)->get();

        $assignmentQuery = CourseAssignment::with('course')->orderByDesc('created_at');
        if ($this->isInstructorUser($user)) {
            $assignmentQuery->whereHas('course', fn ($q) => $q->where('instructor_id', $user->id));
        }
        if ($query !== '') {
            $assignmentQuery->where(function ($q) use ($query) {
                $q->where('title', 'ILIKE', "%{$query}%")
                    ->orWhere('type', 'ILIKE', "%{$query}%");
            });
        }
        $assignments = $assignmentQuery->limit($limit)->get();

        $sessionQuery = CourseSession::with('course')->orderBy('start_at');
        if ($this->isInstructorUser($user)) {
            $sessionQuery->whereHas('course', fn ($q) => $q->where('instructor_id', $user->id));
        }
        if ($query !== '') {
            $sessionQuery->where(function ($q) use ($query) {
                $q->where('title', 'ILIKE', "%{$query}%")
                    ->orWhere('location', 'ILIKE', "%{$query}%");
            });
        }
        $sessions = $sessionQuery->limit($limit)->get();

        $announcementQuery = CourseAnnouncement::with('course')->orderByDesc('published_at');
        if ($this->isInstructorUser($user)) {
            $announcementQuery->whereHas('course', fn ($q) => $q->where('instructor_id', $user->id));
        }
        if ($query !== '') {
            $announcementQuery->where(function ($q) use ($query) {
                $q->where('title', 'ILIKE', "%{$query}%")
                    ->orWhere('body', 'ILIKE', "%{$query}%");
            });
        }
        $announcements = $announcementQuery->limit($limit)->get();

        return view('admin.search.index', [
            'query' => $query,
            'classes' => $classes,
            'enrollments' => $enrollments,
            'assignments' => $assignments,
            'sessions' => $sessions,
            'announcements' => $announcements,
            'isInstructor' => $this->isInstructorUser($user),
        ]);
    }
}
