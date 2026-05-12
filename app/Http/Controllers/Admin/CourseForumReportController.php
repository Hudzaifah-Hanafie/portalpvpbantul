<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RestrictsToInstructorClasses;
use App\Models\CourseEnrollment;
use App\Models\CourseForumReport;
use App\Notifications\ForumPostRemoved;
use App\Notifications\ForumReportResolved;
use App\Notifications\ForumUserMuted;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CourseForumReportController extends Controller
{
    use RestrictsToInstructorClasses;

    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $statusOptions = CourseForumReport::statuses();
        $statusFilter = $request->input('status');

        $query = CourseForumReport::with(['post.topic.course', 'post.user', 'reporter'])
            ->orderByDesc('created_at');
        if ($this->isInstructorUser($request->user())) {
            $query->whereHas('post.topic.course', function ($builder) use ($request) {
                $builder->where('instructor_id', $request->user()->id);
            });
        }

        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }

        $reports = $query->paginate(20)->withQueryString();

        return view('admin.course_forum_report.index', compact('reports', 'statusOptions', 'statusFilter'));
    }

    public function resolve(Request $request, CourseForumReport $course_forum_report)
    {
        $courseClassId = $course_forum_report->post?->topic?->course_class_id;
        if ($courseClassId) {
            $this->ensureInstructorOwnsClassId($request->user(), $courseClassId);
        }
        $course_forum_report->update(['status' => 'resolved']);
        $reporter = $course_forum_report->reporter;
        $post = $course_forum_report->post;
        $topicTitle = $post?->topic?->title ?? '-';
        $courseTitle = $post?->topic?->course?->title ?? null;

        if ($reporter) {
            $reporter->notify(new ForumReportResolved($topicTitle, $courseTitle, 'Ditandai selesai'));
        }
        if ($post?->user && $post->user->isNot($reporter)) {
            $post->user->notify(new ForumReportResolved($topicTitle, $courseTitle, 'Tidak ditemukan pelanggaran'));
        }

        $this->logger->log(
            $request->user(),
            'course.forum.report.resolve',
            "Laporan forum '{$course_forum_report->id}' ditandai selesai",
            $course_forum_report,
            [
                'report_id' => $course_forum_report->id,
                'topic' => $topicTitle,
                'course' => $courseTitle,
                'action' => 'resolved',
            ]
        );

        return back()->with('success', 'Laporan ditandai selesai.');
    }

    public function deletePost(Request $request, CourseForumReport $course_forum_report)
    {
        $courseClassId = $course_forum_report->post?->topic?->course_class_id;
        if ($courseClassId) {
            $this->ensureInstructorOwnsClassId($request->user(), $courseClassId);
        }
        $post = $course_forum_report->post;
        if (! $post) {
            return back()->with('error', 'Post sudah tidak ditemukan.');
        }

        $topic = $post->topic;
        $postTitle = $topic?->title;
        $courseTitle = $topic?->course?->title;
        $author = $post->user;
        $reporter = $course_forum_report->reporter;

        $post->delete();
        $course_forum_report->update(['status' => 'resolved']);

        if ($author) {
            $author->notify(new ForumPostRemoved($postTitle ?? '-', $courseTitle, $course_forum_report->reason));
        }
        if ($reporter && $reporter->isNot($author)) {
            $reporter->notify(new ForumReportResolved($postTitle ?? '-', $courseTitle, 'Postingan dihapus'));
        }

        $this->logger->log(
            $request->user(),
            'course.forum.post.deleted',
            "Post forum '{$postTitle}' dihapus melalui laporan '{$course_forum_report->id}'",
            $course_forum_report,
            [
                'report_id' => $course_forum_report->id,
                'topic' => $postTitle,
                'course' => $courseTitle,
                'action' => 'deleted_post',
                'report_reason' => $course_forum_report->reason,
            ]
        );

        return redirect()->route($this->getRoutePrefix() . 'course-forum-reports.index')->with('success', 'Post dihapus dan laporan tertutup.');
    }

    public function mute(Request $request, CourseForumReport $course_forum_report)
    {
        $courseClassId = $course_forum_report->post?->topic?->course_class_id;
        if ($courseClassId) {
            $this->ensureInstructorOwnsClassId($request->user(), $courseClassId);
        }
        $data = $request->validate([
            'duration_days' => 'nullable|integer|min:1|max:365',
            'mute_until' => 'nullable|date|after:now',
        ]);

        $post = $course_forum_report->post;
        $topic = $post?->topic;
        if (! $post || ! $topic) {
            return back()->with('error', 'Post atau topik sudah tidak tersedia.');
        }

        $enrollment = CourseEnrollment::where('course_class_id', $topic->course_class_id)
            ->where('user_id', $post->user_id)
            ->first();

        if (! $enrollment) {
            return back()->with('error', 'Enrollment peserta tidak ditemukan.');
        }

        $until = $data['mute_until']
            ? Carbon::parse($data['mute_until'])
            : now()->addDays($data['duration_days'] ?? 7);
        $enrollment->muted_until = $until;
        $enrollment->save();

        $course_forum_report->update(['status' => 'resolved']);

        if ($enrollment->user) {
            $enrollment->user->notify(new ForumUserMuted($topic->course->title, $until, $topic->title ?? null));
        }
        if ($course_forum_report->reporter && $course_forum_report->reporter->isNot($enrollment->user)) {
            $course_forum_report->reporter->notify(new ForumReportResolved($topic->title ?? '-', $topic->course->title ?? null, 'Peserta dibatasi sementara'));
        }

        $this->logger->log(
            $request->user(),
            'course.forum.user.muted',
            "Peserta '{$enrollment->user->name}' dibatasi di forum kelas '{$topic->course->title}' hingga {$until}",
            $enrollment,
            [
                'report_id' => $course_forum_report->id,
                'topic' => $topic->title ?? null,
                'course' => $topic->course->title ?? null,
                'action' => 'muted_user',
                'until' => $until->toDateTimeString(),
            ]
        );

        return back()->with('success', 'Peserta dibatasi hingga ' . $until->format('d M Y H:i'));
    }
}
