<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseAnnouncement;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\TrainingSchedule;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\EnrollmentPolicy;
use Illuminate\Http\Request;
use App\Notifications\EnrollmentCreated;
use App\Notifications\EnrollmentBlocked;

class CourseEnrollmentController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index()
    {
        abort_unless(request()->user()?->hasPermission('manage-enrollment'), 403);
        $statusOptions = CourseEnrollment::statuses();
        $adminStatuses = CourseEnrollment::adminStatuses();
        $statusFilter = request('status');
        $classFilter = request('class_id');
        $userFilter = request('user_id');
        $adminFilter = request('admin_status');

        $query = CourseEnrollment::with(['course', 'user', 'interviewAllocations.session'])->orderByDesc('created_at');

        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }
        if ($adminFilter && array_key_exists($adminFilter, $adminStatuses)) {
            $query->where('admin_status', $adminFilter);
        }
        if ($classFilter) {
            $query->where('course_class_id', $classFilter);
        }
        if ($userFilter) {
            $query->where('user_id', $userFilter);
        }

        $statsQuery = clone $query;
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending_admin' => (clone $statsQuery)->where('admin_status', 'pending')->count(),
            'verified' => (clone $statsQuery)->where('admin_status', 'verified')->count(),
            'needs_cbt' => (clone $statsQuery)->where('admin_status', 'verified')->whereNull('written_score')->count(),
            'needs_interview' => (clone $statsQuery)->where('admin_status', 'verified')->whereNotNull('written_score')->whereNull('interview_score')->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
        ];

        $enrollments = $query->paginate(20)->withQueryString();
        $classes = CourseClass::orderBy('title')->pluck('title', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');

        return view('admin.course_enrollment.index', compact(
            'enrollments',
            'statusOptions',
            'statusFilter',
            'adminStatuses',
            'adminFilter',
            'classes',
            'classFilter',
            'users',
            'userFilter',
            'stats'
        ));
    }

    public function ranking(Request $request)
    {
        abort_unless($request->user()?->hasPermission('manage-enrollment'), 403);

        $classes = CourseClass::orderBy('title')->pluck('title', 'id');
        $classId = $request->input('class_id');
        $reserve = max(0, (int) $request->input('reserve', 0));
        $quota = max(0, (int) $request->input('quota', 0));

        $selectedClass = $classId ? CourseClass::find($classId) : null;
        $schedule = $selectedClass ? TrainingSchedule::find($selectedClass->id) : null;
        if (! $quota && $schedule && is_numeric($schedule->kuota)) {
            $quota = (int) $schedule->kuota;
        }

        $ranked = collect();
        if ($selectedClass) {
            $ranked = $this->buildRanking($selectedClass->id, $quota, $reserve);
        }

        return view('admin.course_enrollment.ranking', compact('classes', 'selectedClass', 'schedule', 'quota', 'reserve', 'ranked'));
    }

    public function applyRanking(Request $request)
    {
        abort_unless($request->user()?->hasPermission('manage-enrollment'), 403);
        $data = $request->validate([
            'class_id' => 'required|exists:course_classes,id',
            'quota' => 'required|integer|min:0',
            'reserve' => 'nullable|integer|min:0',
            'publish' => 'nullable|boolean',
        ]);

        $quota = max(0, (int) $data['quota']);
        $reserve = max(0, (int) ($data['reserve'] ?? 0));
        $ranked = $this->buildRanking($data['class_id'], $quota, $reserve);

        $applied = 0;
        foreach ($ranked as $enrollment) {
            $decision = $enrollment->proposed_decision ?? 'pending';
            $newStatus = match ($decision) {
                'approved' => 'approved',
                'reserve' => 'pending',
                'rejected' => 'rejected',
                default => null,
            };

            if (! $newStatus) {
                continue;
            }

            if (! in_array($enrollment->admin_status, ['verified', 'rejected'], true)) {
                continue;
            }

            if ($enrollment->status !== $newStatus) {
                $enrollment->status = $newStatus;
                $enrollment->save();
                $applied++;
            }

            if ($newStatus === 'approved'
                && EnrollmentPolicy::couponIssueMode() === EnrollmentPolicy::COUPON_APPROVED) {
                $enrollment->issueCoupon('approved', $request->user()->id);
            }
        }

        $class = CourseClass::find($data['class_id']);
        if ($request->boolean('publish') && $class) {
            $this->createSelectionAnnouncement($class, $quota, $reserve, $ranked, $request->user()->id);
        }

        if ($request->boolean('publish') && EnrollmentPolicy::couponIssueMode() === EnrollmentPolicy::COUPON_PUBLISHED) {
            CourseEnrollment::where('course_class_id', $data['class_id'])
                ->where('status', 'approved')
                ->get()
                ->each(fn ($enrollment) => $enrollment->issueCoupon('published', $request->user()->id));
        }

        $this->logger->log(
            $request->user(),
            'course.enrollment.ranking.applied',
            "Hasil seleksi kelas {$class?->title} diterapkan ({$applied} peserta diubah)",
            ['class_id' => $data['class_id'], 'quota' => $quota, 'reserve' => $reserve, 'updated' => $applied]
        );

        return redirect()
            ->route('admin.course-enrollment.ranking', ['class_id' => $data['class_id'], 'quota' => $quota, 'reserve' => $reserve])
            ->with('success', 'Keputusan seleksi diterapkan. Status peserta diperbarui.');
    }

    public function create()
    {
        abort_unless(request()->user()?->hasPermission('manage-enrollment'), 403);
        $classes = CourseClass::orderBy('title')->pluck('title', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');

        return view('admin.course_enrollment.form', [
            'enrollment' => new CourseEnrollment(['status' => 'active']),
            'classes' => $classes,
            'users' => $users,
            'adminStatuses' => CourseEnrollment::adminStatuses(),
            'action' => route('admin.course-enrollment.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()?->hasPermission('manage-enrollment'), 403);
        $data = $this->validateData($request);
        $data['admin_status'] = $data['admin_status'] ?? 'pending';
        $data['created_by'] = $request->user()->id;
        $enrollment = CourseEnrollment::create($data);

        if ($enrollment->user) {
            $enrollment->user->notify(new EnrollmentCreated($enrollment));
        }

        if ($enrollment->status === 'approved'
            && EnrollmentPolicy::couponIssueMode() === EnrollmentPolicy::COUPON_APPROVED) {
            $enrollment->issueCoupon('approved', $request->user()->id);
        }

        $this->logger->log(
            $request->user(),
            'course.enrollment.created',
            "Enrollment user '{$enrollment->user->name}' ke kelas '{$enrollment->course->title}' ditambahkan",
            $enrollment
        );

        return redirect()->route('admin.course-enrollment.index')->with('success', 'Enrollment ditambahkan.');
    }

    public function edit(CourseEnrollment $course_enrollment)
    {
        abort_unless(request()->user()?->hasPermission('manage-enrollment'), 403);
        $classes = CourseClass::orderBy('title')->pluck('title', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');

        return view('admin.course_enrollment.form', [
            'enrollment' => $course_enrollment,
            'classes' => $classes,
            'users' => $users,
            'adminStatuses' => CourseEnrollment::adminStatuses(),
            'action' => route('admin.course-enrollment.update', $course_enrollment->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, CourseEnrollment $course_enrollment)
    {
        abort_unless($request->user()?->hasPermission('manage-enrollment'), 403);
        $data = $this->validateData($request, $course_enrollment->id);
        $data['admin_status'] = $data['admin_status'] ?? $course_enrollment->admin_status ?? 'pending';
        $course_enrollment->update($data);

        if ($course_enrollment->user && $data['status'] === 'blocked') {
            $course_enrollment->user->notify(new EnrollmentBlocked($course_enrollment));
        }
        if ($course_enrollment->user && $data['status'] === 'active' && $course_enrollment->wasChanged('status')) {
            $course_enrollment->user->notify(new EnrollmentCreated($course_enrollment));
        }

        if ($course_enrollment->status === 'approved'
            && $course_enrollment->wasChanged('status')
            && EnrollmentPolicy::couponIssueMode() === EnrollmentPolicy::COUPON_APPROVED) {
            $course_enrollment->issueCoupon('approved', $request->user()->id);
        }

        $this->logger->log(
            $request->user(),
            'course.enrollment.updated',
            "Enrollment user '{$course_enrollment->user->name}' ke kelas '{$course_enrollment->course->title}' diperbarui",
            $course_enrollment
        );

        return redirect()->route('admin.course-enrollment.index')->with('success', 'Enrollment diperbarui.');
    }

    public function destroy(CourseEnrollment $course_enrollment)
    {
        abort_unless(request()->user()?->hasPermission('manage-enrollment'), 403);
        $this->logger->log(
            request()->user(),
            'course.enrollment.deleted',
            "Enrollment user '{$course_enrollment->user->name}' ke kelas '{$course_enrollment->course->title}' dihapus",
            $course_enrollment
        );
        $course_enrollment->delete();
        return redirect()->route('admin.course-enrollment.index')->with('success', 'Enrollment dihapus.');
    }

    public function verify(Request $request, CourseEnrollment $course_enrollment)
    {
        abort_unless($request->user()?->hasPermission('manage-enrollment'), 403);
        $data = $request->validate([
            'admin_status' => 'required|in:' . implode(',', array_keys(CourseEnrollment::adminStatuses())),
            'admin_note' => 'nullable|string',
        ]);

        $course_enrollment->update([
            'admin_status' => $data['admin_status'],
            'admin_note' => $data['admin_note'] ?? $course_enrollment->admin_note,
        ]);

        $this->logger->log(
            $request->user(),
            'course.enrollment.verified',
            "Verifikasi enrollment {$course_enrollment->user->name ?? 'peserta'} untuk {$course_enrollment->course->title ?? '-'} menjadi {$data['admin_status']}",
            $course_enrollment
        );

        return back()->with('success', 'Status verifikasi diperbarui.');
    }

    private function validateData(Request $request, ?string $ignoreId = null): array
    {
        $adminStatuses = CourseEnrollment::adminStatuses();
        $rules = [
            'course_class_id' => 'required|exists:course_classes,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:' . implode(',', array_keys(CourseEnrollment::statuses())),
            'muted_until' => 'nullable|date|after:now',
            'admin_status' => 'nullable|in:' . implode(',', array_keys($adminStatuses)),
            'admin_note' => 'nullable|string',
        ];

        if ($ignoreId) {
            // unique combination
            $rules['user_id'] .= '|unique:course_enrollments,user_id,' . $ignoreId . ',id,course_class_id,' . $request->input('course_class_id');
        } else {
            $rules['user_id'] .= '|unique:course_enrollments,user_id,NULL,id,course_class_id,' . $request->input('course_class_id');
        }

        return $request->validate($rules);
    }

    private function buildRanking(string $classId, int $quota, int $reserve)
    {
        $enrollments = CourseEnrollment::with('user')
            ->where('course_class_id', $classId)
            ->orderByDesc('final_score')
            ->orderByDesc('written_score')
            ->orderBy('created_at')
            ->get();

        $approvedCount = 0;
        $reserveCount = 0;
        $verifiedRank = 0;

        return $enrollments->values()->map(function (CourseEnrollment $enrollment, int $index) use (&$approvedCount, &$reserveCount, &$verifiedRank, $quota, $reserve) {
            $decision = 'pending';

            if ($enrollment->admin_status === 'verified') {
                $verifiedRank++;
                if ($approvedCount < $quota) {
                    $decision = 'approved';
                    $approvedCount++;
                } elseif ($reserveCount < $reserve) {
                    $decision = 'reserve';
                    $reserveCount++;
                } else {
                    $decision = 'rejected';
                }
            } elseif ($enrollment->admin_status === 'rejected') {
                $decision = 'rejected';
            }

            $enrollment->proposed_decision = $decision;
            $enrollment->rank = $index + 1;
            $enrollment->verified_rank = $enrollment->admin_status === 'verified' ? $verifiedRank : null;

            return $enrollment;
        });
    }

    private function createSelectionAnnouncement(?CourseClass $class, int $quota, int $reserve, $ranked, string $createdBy): void
    {
        if (! $class) {
            return;
        }

        $approved = $ranked->where('proposed_decision', 'approved')->count();
        $reserveCount = $ranked->where('proposed_decision', 'reserve')->count();

        CourseAnnouncement::create([
            'course_class_id' => $class->id,
            'title' => 'Pengumuman Hasil Seleksi ' . ($class->title ?? ''),
            'body' => "Hasil seleksi telah dipublikasikan. Kuota lulus: {$approved} / {$quota}. Cadangan: {$reserveCount} / {$reserve}. Peserta yang dinyatakan lulus akan mendapatkan status APPROVED di portal.",
            'status' => 'published',
            'published_at' => now(),
            'created_by' => $createdBy,
        ]);
    }
}
