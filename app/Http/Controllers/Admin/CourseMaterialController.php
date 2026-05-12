<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RestrictsToInstructorClasses;
use App\Models\CourseClass;
use App\Models\CourseMaterial;
use App\Models\CourseModule;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseMaterialController extends Controller
{
    use RestrictsToInstructorClasses;

    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request): View
    {
        $classFilter = $request->input('class_id');
        $moduleFilter = $request->input('module_id');

        $query = CourseMaterial::with(['module.course'])->orderBy('sort_order');
        if ($this->isInstructorUser($request->user())) {
            $query->whereHas('module.course', function ($builder) use ($request) {
                $builder->where('instructor_id', $request->user()->id);
            });
        }
        if ($moduleFilter) {
            $query->where('course_module_id', $moduleFilter);
        } elseif ($classFilter) {
            $query->whereHas('module', fn ($q) => $q->where('course_class_id', $classFilter));
        }

        $materials = $query->paginate(20)->withQueryString();
        $classes = $this->scopedClassOptions($request->user());
        $modules = $this->scopedModuleOptions($request->user());

        return view('admin.course_material.index', compact('materials', 'classes', 'modules', 'classFilter', 'moduleFilter'));
    }

    public function create(): View
    {
        $classes = $this->scopedClassOptions(request()->user());
        $modules = $this->scopedModuleOptions(request()->user());

        return view('admin.course_material.form', [
            'material' => new CourseMaterial(['is_active' => true]),
            'classes' => $classes,
            'modules' => $modules,
            'action' => route($this->getRoutePrefix() . 'course-material.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $module = CourseModule::find($data['course_module_id']);
        if ($module) {
            $this->ensureInstructorOwnsClassId($request->user(), $module->course_class_id);
        }
        $data['created_by'] = $request->user()->id;
        $material = CourseMaterial::create($data);

        $this->logger->log(
            $request->user(),
            'course.material.created',
            "Materi '{$material->title}' ditambahkan",
            $material
        );

        return redirect()->route($this->getRoutePrefix() . 'course-material.index')->with('success', 'Materi pembelajaran berhasil ditambahkan.');
    }

    public function edit(CourseMaterial $course_material): View
    {
        $moduleClassId = $course_material->module?->course_class_id;
        if ($moduleClassId) {
            $this->ensureInstructorOwnsClassId(request()->user(), $moduleClassId);
        }
        $classes = $this->scopedClassOptions(request()->user());
        $modules = $this->scopedModuleOptions(request()->user());

        return view('admin.course_material.form', [
            'material' => $course_material,
            'classes' => $classes,
            'modules' => $modules,
            'action' => route($this->getRoutePrefix() . 'course-material.update', $course_material->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, CourseMaterial $course_material): RedirectResponse
    {
        $data = $this->validateData($request);
        $module = CourseModule::find($data['course_module_id']);
        if ($module) {
            $this->ensureInstructorOwnsClassId($request->user(), $module->course_class_id);
        }
        $course_material->update($data);

        $this->logger->log(
            $request->user(),
            'course.material.updated',
            "Materi '{$course_material->title}' diperbarui",
            $course_material
        );

        return redirect()->route($this->getRoutePrefix() . 'course-material.index')->with('success', 'Materi pembelajaran diperbarui.');
    }

    public function destroy(CourseMaterial $course_material): RedirectResponse
    {
        $moduleClassId = $course_material->module?->course_class_id;
        if ($moduleClassId) {
            $this->ensureInstructorOwnsClassId(request()->user(), $moduleClassId);
        }
        $this->logger->log(
            $request->user(),
            'course.material.deleted',
            "Materi '{$course_material->title}' dihapus",
            $course_material
        );
        $course_material->delete();

        return redirect()->route($this->getRoutePrefix() . 'course-material.index')->with('success', 'Materi pembelajaran dihapus.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'course_module_id' => 'required|exists:course_modules,id',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'link_url' => 'nullable|url|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
