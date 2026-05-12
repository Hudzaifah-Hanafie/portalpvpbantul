<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RestrictsToInstructorClasses;
use App\Models\CourseClass;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class CourseClassController extends Controller
{
    use RestrictsToInstructorClasses;

    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index()
    {
        $statusOptions = CourseClass::statuses();
        $statusFilter = request('status');
        $tagFilter = request('tag');

        $query = $this->scopeClassesForUser(CourseClass::orderBy('created_at', 'desc'), request()->user());
        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }
        if ($tagFilter) {
            $query->whereJsonContains('tags', $tagFilter);
        }

        $classes = $query->paginate(15)->withQueryString();

        return view('admin.course_class.index', compact('classes', 'statusOptions', 'statusFilter', 'tagFilter'));
    }

    public function create()
    {
        return view('admin.course_class.form', [
            'course' => new CourseClass(['format' => 'sinkron', 'is_active' => true]),
            'action' => route($this->getRoutePrefix() . 'course-class.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;
        if ($this->isInstructorUser($request->user())) {
            $data['instructor_id'] = $request->user()->id;
        }
        $this->applyWorkflow($request, $data);
        $course = CourseClass::create($data);

        $this->logger->log(
            $request->user(),
            'course.created',
            "Kelas '{$course->title}' ditambahkan",
            $course
        );

        return redirect()->route($this->getRoutePrefix() . 'course-class.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(CourseClass $course_class)
    {
        $this->ensureInstructorOwnsClass(request()->user(), $course_class);

        return view('admin.course_class.form', [
            'course' => $course_class,
            'action' => route($this->getRoutePrefix() . 'course-class.update', $course_class->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, CourseClass $course_class)
    {
        $this->ensureInstructorOwnsClass($request->user(), $course_class);
        $data = $this->validateData($request);
        if ($this->isInstructorUser($request->user())) {
            $data['instructor_id'] = $request->user()->id;
        }
        $this->applyWorkflow($request, $data, $course_class);
        $course_class->update($data);

        $this->logger->log(
            $request->user(),
            'course.updated',
            "Kelas '{$course_class->title}' diperbarui",
            $course_class
        );

        return redirect()->route($this->getRoutePrefix() . 'course-class.index')->with('success', 'Kelas diperbarui.');
    }

    public function destroy(CourseClass $course_class)
    {
        $this->ensureInstructorOwnsClass(request()->user(), $course_class);
        $this->logger->log(
            request()->user(),
            'course.deleted',
            "Kelas '{$course_class->title}' dihapus",
            $course_class
        );
        $course_class->delete();
        return redirect()->route($this->getRoutePrefix() . 'course-class.index')->with('success', 'Kelas dihapus.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'format' => 'required|in:sinkron,asinkron,blended,luring',
            'prerequisites' => 'nullable|string',
            'competencies' => 'nullable|string',
            'badge' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'status' => 'nullable|in:' . implode(',', array_keys(CourseClass::statuses())),
            'instructor_id' => 'nullable|exists:users,id',
            'min_attendance' => 'nullable|integer|min:0|max:100',
            'min_score' => 'nullable|integer|min:0|max:100',
            'require_final_project' => 'nullable|boolean',
            'require_final_exam' => 'nullable|boolean',
            'weight_theory' => 'nullable|integer|min:0|max:100',
            'weight_practice' => 'nullable|integer|min:0|max:100',
            'weight_attitude' => 'nullable|integer|min:0|max:100',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['require_final_project'] = $request->boolean('require_final_project', true);
        $data['require_final_exam'] = $request->boolean('require_final_exam', false);
        $data['prerequisites'] = $data['prerequisites'] ? array_values(array_filter(array_map('trim', preg_split("/(\r?\n)+/", $data['prerequisites'])))) : null;
        $data['competencies'] = $data['competencies'] ? array_values(array_filter(array_map('trim', preg_split("/(\r?\n)+/", $data['competencies'])))) : null;
        $data['tags'] = $data['tags'] ? array_values(array_unique(array_filter(array_map('trim', preg_split('/[,\r\n]+/', $data['tags']))))) : null;

        $weightTheory = $data['weight_theory'] ?? 30;
        $weightPractice = $data['weight_practice'] ?? 60;
        $weightAttitude = $data['weight_attitude'] ?? 10;
        if (($weightTheory + $weightPractice + $weightAttitude) !== 100) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'weight_theory' => 'Total bobot penilaian harus 100%.',
            ]);
        }
        $data['weight_theory'] = $weightTheory;
        $data['weight_practice'] = $weightPractice;
        $data['weight_attitude'] = $weightAttitude;

        return $data;
    }

    private function applyWorkflow(Request $request, array &$data, ?CourseClass $course = null): void
    {
        $defaultStatus = $request->user()->hasPermission('approve-content') ? 'published' : 'draft';
        $currentStatus = $course ? $course->status : $defaultStatus;
        $requestedStatus = $data['status'] ?? $currentStatus;

        if (! $request->user()->hasPermission('approve-content') && $requestedStatus === 'published') {
            $requestedStatus = 'pending';
        }

        $data['status'] = $requestedStatus ?: $currentStatus;

        if ($data['status'] === 'published') {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
            $data['published_at'] = $course?->published_at ?? now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
    }
}
