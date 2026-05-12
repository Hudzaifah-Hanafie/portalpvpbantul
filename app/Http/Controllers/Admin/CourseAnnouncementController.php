<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RestrictsToInstructorClasses;
use App\Models\CourseAnnouncement;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Notifications\NewCourseAnnouncement;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class CourseAnnouncementController extends Controller
{
    use RestrictsToInstructorClasses;

    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index()
    {
        $statusOptions = CourseAnnouncement::statuses();
        $statusFilter = request('status');
        $classFilter = request('class_id');

        $query = CourseAnnouncement::with('course')->orderByDesc('published_at')->orderByDesc('created_at');
        if ($this->isInstructorUser(request()->user())) {
            $classIds = CourseClass::where('instructor_id', request()->user()->id)->pluck('id');
            $query->whereIn('course_class_id', $classIds);
        }
        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }
        if ($classFilter) {
            $query->where('course_class_id', $classFilter);
        }

        $announcements = $query->paginate(20)->withQueryString();
        $classes = $this->scopedClassOptions(request()->user());

        return view('admin.course_announcement.index', compact('announcements', 'statusOptions', 'statusFilter', 'classes', 'classFilter'));
    }

    public function create()
    {
        $classes = $this->scopedClassOptions(request()->user());
        return view('admin.course_announcement.form', [
            'announcement' => new CourseAnnouncement(['status' => 'draft']),
            'classes' => $classes,
            'action' => route($this->getRoutePrefix() . 'course-announcement.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->ensureInstructorOwnsClassId($request->user(), $data['course_class_id']);
        $data['created_by'] = $request->user()->id;
        if ($data['status'] === 'published' && ! $data['published_at']) {
            $data['published_at'] = now();
        }
        $announcement = CourseAnnouncement::create($data);

        if ($announcement->status === 'published') {
            $this->notifyParticipants($announcement);
        }

        $this->logger->log(
            $request->user(),
            'course.announcement.created',
            "Pengumuman '{$announcement->title}' ditambahkan",
            $announcement
        );

        return redirect()->route($this->getRoutePrefix() . 'course-announcement.index')->with('success', 'Pengumuman disimpan.');
    }

    public function edit(CourseAnnouncement $course_announcement)
    {
        $this->ensureInstructorOwnsClassId(request()->user(), $course_announcement->course_class_id);
        $classes = $this->scopedClassOptions(request()->user());
        return view('admin.course_announcement.form', [
            'announcement' => $course_announcement,
            'classes' => $classes,
            'action' => route($this->getRoutePrefix() . 'course-announcement.update', $course_announcement->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, CourseAnnouncement $course_announcement)
    {
        $data = $this->validateData($request);
        $this->ensureInstructorOwnsClassId($request->user(), $course_announcement->course_class_id);
        $this->ensureInstructorOwnsClassId($request->user(), $data['course_class_id']);
        $wasPublished = $course_announcement->status === 'published';

        if ($data['status'] === 'published' && ! $data['published_at']) {
            $data['published_at'] = now();
        }

        $course_announcement->update($data);

        if ($course_announcement->status === 'published' && ! $wasPublished) {
            $this->notifyParticipants($course_announcement);
        }

        $this->logger->log(
            $request->user(),
            'course.announcement.updated',
            "Pengumuman '{$course_announcement->title}' diperbarui",
            $course_announcement
        );

        return redirect()->route($this->getRoutePrefix() . 'course-announcement.index')->with('success', 'Pengumuman diperbarui.');
    }

    public function destroy(CourseAnnouncement $course_announcement)
    {
        $this->ensureInstructorOwnsClassId(request()->user(), $course_announcement->course_class_id);
        $this->logger->log(
            request()->user(),
            'course.announcement.deleted',
            "Pengumuman '{$course_announcement->title}' dihapus",
            $course_announcement
        );
        $course_announcement->delete();
        return redirect()->route($this->getRoutePrefix() . 'course-announcement.index')->with('success', 'Pengumuman dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'course_class_id' => 'required|exists:course_classes,id',
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
            'status' => 'required|in:' . implode(',', array_keys(CourseAnnouncement::statuses())),
            'published_at' => 'nullable|date',
        ]);
    }

    private function notifyParticipants(CourseAnnouncement $announcement): void
    {
        $userIds = CourseEnrollment::where('course_class_id', $announcement->course_class_id)
            ->whereIn('status', ['active', 'approved'])
            ->pluck('user_id')
            ->all();

        if (empty($userIds)) {
            return;
        }

        $users = \App\Models\User::whereIn('id', $userIds)->get();
        foreach ($users as $user) {
            $user->notify(new NewCourseAnnouncement($announcement->course->title ?? '-', $announcement->title));
        }
    }
}
