<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RestrictsToInstructorClasses;
use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\CourseCurriculum;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class CourseCurriculumController extends Controller
{
    use RestrictsToInstructorClasses;

    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $classFilter = $request->input('class_id');
        $query = CourseCurriculum::with(['course.modules.materials', 'course.sessions'])->orderByDesc('created_at');
        if ($this->isInstructorUser($request->user())) {
            $classIds = CourseClass::where('instructor_id', $request->user()->id)->pluck('id');
            $query->whereIn('course_class_id', $classIds);
        }
        if ($classFilter) {
            $query->where('course_class_id', $classFilter);
        }

        $curricula = $query->paginate(20)->withQueryString();
        $classes = $this->scopedClassOptions($request->user());

        return view('admin.course_curriculum.index', compact('curricula', 'classes', 'classFilter'));
    }

    public function create(Request $request)
    {
        $classes = $this->scopedClassOptions($request->user());
        $selectedClassId = $request->input('class_id');
        $selectedClass = null;
        if ($selectedClassId) {
            $selectedClass = CourseClass::with(['modules.materials', 'sessions'])
                ->whereKey($selectedClassId)
                ->first();
        }

        return view('admin.course_curriculum.form', [
            'curriculum' => new CourseCurriculum([
                'is_active' => true,
                'method' => $selectedClass?->format ?? 'luring',
                'course_class_id' => $selectedClass?->id,
            ]),
            'classes' => $classes,
            'selectedClass' => $selectedClass,
            'action' => route($this->routePrefix() . 'course-curriculum.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->ensureInstructorOwnsClassId($request->user(), $data['course_class_id']);
        $data['created_by'] = $request->user()->id;
        $curriculum = CourseCurriculum::create($data);

        $this->logger->log(
            $request->user(),
            'course.curriculum.created',
            "Kurikulum '{$curriculum->title}' ditambahkan",
            $curriculum
        );

        return redirect()->route($this->routePrefix() . 'course-curriculum.index')->with('success', 'Kurikulum berhasil ditambahkan.');
    }

    public function edit(CourseCurriculum $course_curriculum)
    {
        $this->ensureInstructorOwnsClassId(request()->user(), $course_curriculum->course_class_id);
        $classes = $this->scopedClassOptions(request()->user());

        return view('admin.course_curriculum.form', [
            'curriculum' => $course_curriculum,
            'classes' => $classes,
            'action' => route($this->routePrefix() . 'course-curriculum.update', $course_curriculum->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, CourseCurriculum $course_curriculum)
    {
        $data = $this->validateData($request);
        $this->ensureInstructorOwnsClassId($request->user(), $course_curriculum->course_class_id);
        $this->ensureInstructorOwnsClassId($request->user(), $data['course_class_id']);
        $course_curriculum->update($data);

        $this->logger->log(
            $request->user(),
            'course.curriculum.updated',
            "Kurikulum '{$course_curriculum->title}' diperbarui",
            $course_curriculum
        );

        return redirect()->route($this->routePrefix() . 'course-curriculum.index')->with('success', 'Kurikulum diperbarui.');
    }

    public function destroy(CourseCurriculum $course_curriculum)
    {
        $this->ensureInstructorOwnsClassId(request()->user(), $course_curriculum->course_class_id);
        $this->logger->log(
            request()->user(),
            'course.curriculum.deleted',
            "Kurikulum '{$course_curriculum->title}' dihapus",
            $course_curriculum
        );
        $course_curriculum->delete();

        return redirect()->route($this->routePrefix() . 'course-curriculum.index')->with('success', 'Kurikulum dihapus.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'course_class_id' => 'required|exists:course_classes,id',
            'title' => 'required|string|max:255',
            'skkni_reference' => 'nullable|string|max:255',
            'matrix_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'method' => 'required|in:luring,daring,blended',
            'total_jp_theory' => 'nullable|integer|min:0|max:2000',
            'total_jp_practice' => 'nullable|integer|min:0|max:2000',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['total_jp_theory'] = $data['total_jp_theory'] ?? 0;
        $data['total_jp_practice'] = $data['total_jp_practice'] ?? 0;

        return $data;
    }

    private function routePrefix(): string
    {
        return request()->routeIs('instructor.*') ? 'instructor.lms.' : 'admin.';
    }
}
