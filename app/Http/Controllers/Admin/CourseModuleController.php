<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RestrictsToInstructorClasses;
use App\Models\CourseClass;
use App\Models\CourseModule;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseModuleController extends Controller
{
    use RestrictsToInstructorClasses;

    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request): View
    {
        $classFilter = $request->input('class_id');
        $query = $this->scopeModulesForUser(CourseModule::with('course')->orderBy('sort_order'), $request->user());
        if ($classFilter) {
            $query->where('course_class_id', $classFilter);
        }
        $modules = $query->paginate(20)->withQueryString();
        $classes = $this->scopedClassOptions($request->user());

        return view('admin.course_module.index', compact('modules', 'classes', 'classFilter'));
    }

    public function create(): View
    {
        $classes = $this->scopedClassOptions(request()->user());

        return view('admin.course_module.form', [
            'module' => new CourseModule(['is_active' => true]),
            'classes' => $classes,
            'action' => route($this->getRoutePrefix() . 'course-module.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $this->ensureInstructorOwnsClassId($request->user(), $data['course_class_id']);
        $data['created_by'] = $request->user()->id;
        $module = CourseModule::create($data);

        $this->logger->log(
            $request->user(),
            'course.module.created',
            "Bab '{$module->title}' ditambahkan",
            $module
        );

        return redirect()->route($this->getRoutePrefix() . 'course-module.index')->with('success', 'Bab pembelajaran berhasil ditambahkan.');
    }

    public function edit(CourseModule $course_module): View
    {
        $this->ensureInstructorOwnsClassId(request()->user(), $course_module->course_class_id);
        $classes = $this->scopedClassOptions(request()->user());

        return view('admin.course_module.form', [
            'module' => $course_module,
            'classes' => $classes,
            'action' => route($this->getRoutePrefix() . 'course-module.update', $course_module->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, CourseModule $course_module): RedirectResponse
    {
        $data = $this->validateData($request);
        $this->ensureInstructorOwnsClassId($request->user(), $course_module->course_class_id);
        $this->ensureInstructorOwnsClassId($request->user(), $data['course_class_id']);
        $course_module->update($data);

        $this->logger->log(
            $request->user(),
            'course.module.updated',
            "Bab '{$course_module->title}' diperbarui",
            $course_module
        );

        return redirect()->route($this->getRoutePrefix() . 'course-module.index')->with('success', 'Bab pembelajaran diperbarui.');
    }

    public function destroy(CourseModule $course_module): RedirectResponse
    {
        $this->ensureInstructorOwnsClassId(request()->user(), $course_module->course_class_id);
        $this->logger->log(
            $request->user(),
            'course.module.deleted',
            "Bab '{$course_module->title}' dihapus",
            $course_module
        );
        $course_module->delete();

        return redirect()->route($this->getRoutePrefix() . 'course-module.index')->with('success', 'Bab pembelajaran dihapus.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'course_class_id' => 'required|exists:course_classes,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
