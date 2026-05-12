<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RestrictsToInstructorClasses;
use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\CourseCurriculum;
use App\Models\CourseCurriculumUnit;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class CourseCurriculumUnitController extends Controller
{
    use RestrictsToInstructorClasses;

    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $curriculumFilter = $request->input('curriculum_id');
        $classFilter = $request->input('class_id');

        $query = CourseCurriculumUnit::with('curriculum.course')->orderBy('sort_order');
        if ($this->isInstructorUser($request->user())) {
            $classIds = CourseClass::where('instructor_id', $request->user()->id)->pluck('id');
            $query->whereHas('curriculum', fn ($q) => $q->whereIn('course_class_id', $classIds));
        }
        if ($curriculumFilter) {
            $query->where('course_curriculum_id', $curriculumFilter);
        } elseif ($classFilter) {
            $query->whereHas('curriculum', fn ($q) => $q->where('course_class_id', $classFilter));
        }

        $units = $query->paginate(25)->withQueryString();
        $classes = $this->scopedClassOptions($request->user());
        $curricula = CourseCurriculum::orderBy('title')
            ->when($this->isInstructorUser($request->user()), function ($q) {
                $classIds = CourseClass::where('instructor_id', request()->user()->id)->pluck('id');
                $q->whereIn('course_class_id', $classIds);
            })
            ->pluck('title', 'id');

        return view('admin.course_curriculum_unit.index', compact('units', 'classes', 'classFilter', 'curricula', 'curriculumFilter'));
    }

    public function create()
    {
        $classes = $this->scopedClassOptions(request()->user());
        $curricula = CourseCurriculum::orderBy('title')
            ->when($this->isInstructorUser(request()->user()), function ($q) {
                $classIds = CourseClass::where('instructor_id', request()->user()->id)->pluck('id');
                $q->whereIn('course_class_id', $classIds);
            })
            ->pluck('title', 'id');

        return view('admin.course_curriculum_unit.form', [
            'unit' => new CourseCurriculumUnit(['is_active' => true, 'method' => 'luring']),
            'classes' => $classes,
            'curricula' => $curricula,
            'action' => route($this->routePrefix() . 'course-curriculum-unit.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $curriculum = CourseCurriculum::find($data['course_curriculum_id']);
        if ($curriculum) {
            $this->ensureInstructorOwnsClassId($request->user(), $curriculum->course_class_id);
        }
        $data['created_by'] = $request->user()->id;
        $unit = CourseCurriculumUnit::create($data);

        $this->logger->log(
            $request->user(),
            'course.curriculum.unit.created',
            "Unit kompetensi '{$unit->unit_title}' ditambahkan",
            $unit
        );

        return redirect()->route($this->routePrefix() . 'course-curriculum-unit.index')->with('success', 'Unit kompetensi berhasil ditambahkan.');
    }

    public function edit(CourseCurriculumUnit $course_curriculum_unit)
    {
        $curriculum = $course_curriculum_unit->curriculum;
        if ($curriculum) {
            $this->ensureInstructorOwnsClassId(request()->user(), $curriculum->course_class_id);
        }
        $classes = $this->scopedClassOptions(request()->user());
        $curricula = CourseCurriculum::orderBy('title')
            ->when($this->isInstructorUser(request()->user()), function ($q) {
                $classIds = CourseClass::where('instructor_id', request()->user()->id)->pluck('id');
                $q->whereIn('course_class_id', $classIds);
            })
            ->pluck('title', 'id');

        return view('admin.course_curriculum_unit.form', [
            'unit' => $course_curriculum_unit,
            'classes' => $classes,
            'curricula' => $curricula,
            'action' => route($this->routePrefix() . 'course-curriculum-unit.update', $course_curriculum_unit->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, CourseCurriculumUnit $course_curriculum_unit)
    {
        $data = $this->validateData($request);
        $curriculum = CourseCurriculum::find($data['course_curriculum_id']);
        if ($curriculum) {
            $this->ensureInstructorOwnsClassId($request->user(), $curriculum->course_class_id);
        }
        $course_curriculum_unit->update($data);

        $this->logger->log(
            $request->user(),
            'course.curriculum.unit.updated',
            "Unit kompetensi '{$course_curriculum_unit->unit_title}' diperbarui",
            $course_curriculum_unit
        );

        return redirect()->route($this->routePrefix() . 'course-curriculum-unit.index')->with('success', 'Unit kompetensi diperbarui.');
    }

    public function destroy(CourseCurriculumUnit $course_curriculum_unit)
    {
        $curriculum = $course_curriculum_unit->curriculum;
        if ($curriculum) {
            $this->ensureInstructorOwnsClassId(request()->user(), $curriculum->course_class_id);
        }
        $this->logger->log(
            request()->user(),
            'course.curriculum.unit.deleted',
            "Unit kompetensi '{$course_curriculum_unit->unit_title}' dihapus",
            $course_curriculum_unit
        );
        $course_curriculum_unit->delete();

        return redirect()->route($this->routePrefix() . 'course-curriculum-unit.index')->with('success', 'Unit kompetensi dihapus.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'course_curriculum_id' => 'required|exists:course_curricula,id',
            'unit_code' => 'nullable|string|max:100',
            'unit_title' => 'required|string|max:255',
            'elements' => 'nullable|string',
            'kuk' => 'nullable|string',
            'materials' => 'nullable|string',
            'jp_theory' => 'nullable|integer|min:0|max:2000',
            'jp_practice' => 'nullable|integer|min:0|max:2000',
            'method' => 'required|in:luring,daring,blended',
            'sort_order' => 'nullable|integer|min:0|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['jp_theory'] = $data['jp_theory'] ?? 0;
        $data['jp_practice'] = $data['jp_practice'] ?? 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }

    private function routePrefix(): string
    {
        return request()->routeIs('instructor.*') ? 'instructor.lms.' : 'admin.';
    }
}
