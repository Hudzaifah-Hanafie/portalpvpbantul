<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\KejuruanModule;
use App\Models\Program;
use App\Models\TrainingSchedule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KejuruanModuleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $programOptions = $this->programOptionsForInstructor($user->id);

        $query = KejuruanModule::with('program')
            ->where('owner_id', $user->id)
            ->orderByDesc('updated_at');

        $programFilter = $request->input('program_id');
        $search = trim((string) $request->input('q', ''));

        if ($programFilter) {
            $query->where('program_id', $programFilter);
        }
        if ($search !== '') {
            $query->where('title', 'ILIKE', "%{$search}%");
        }

        $modules = $query->get();
        $grouped = $modules->groupBy(fn ($module) => $module->program?->judul ?? 'LAINNYA');

        return view('kejuruan_modules.index', [
            'modulesByKejuruan' => $grouped,
            'programOptions' => $programOptions,
            'programFilter' => $programFilter,
            'ownerOptions' => collect(),
            'ownerFilter' => null,
            'search' => $search,
            'routePrefix' => 'instructor.lms.kejuruan-modules',
            'pageTitle' => 'Modul Kejuruan',
            'showOwner' => false,
        ]);
    }

    public function create(Request $request)
    {
        $programOptions = $this->programOptionsForInstructor($request->user()->id);
        if ($programOptions->isEmpty()) {
            return redirect()->route('instructor.lms.kejuruan-modules.index')
                ->with('error', 'Belum ada kejuruan yang ditugaskan untuk Anda.');
        }

        return view('kejuruan_modules.form', [
            'module' => new KejuruanModule(),
            'programOptions' => $programOptions,
            'ownerOptions' => collect(),
            'routePrefix' => 'instructor.lms.kejuruan-modules',
            'pageTitle' => 'Tambah Modul Kejuruan',
            'showOwnerField' => false,
            'action' => route('instructor.lms.kejuruan-modules.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $programOptions = $this->programOptionsForInstructor($request->user()->id);
        $allowedProgramIds = $programOptions->keys()->all();
        if (empty($allowedProgramIds)) {
            return redirect()->route('instructor.lms.kejuruan-modules.index')
                ->with('error', 'Belum ada kejuruan yang ditugaskan untuk Anda.');
        }

        $data = $request->validate([
            'program_id' => ['required', Rule::in($allowedProgramIds)],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link_url' => 'required|url|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        $data['created_by'] = $request->user()->id;
        $data['owner_id'] = $request->user()->id;
        $data['is_active'] = $request->boolean('is_active');

        KejuruanModule::create($data);

        return redirect()->route('instructor.lms.kejuruan-modules.index')
            ->with('success', 'Modul kejuruan berhasil ditambahkan.');
    }

    public function edit(Request $request, KejuruanModule $kejuruan_module)
    {
        $this->authorizeOwner($request, $kejuruan_module);

        return view('kejuruan_modules.form', [
            'module' => $kejuruan_module,
            'programOptions' => $this->programOptionsForInstructor($request->user()->id),
            'ownerOptions' => collect(),
            'routePrefix' => 'instructor.lms.kejuruan-modules',
            'pageTitle' => 'Edit Modul Kejuruan',
            'showOwnerField' => false,
            'action' => route('instructor.lms.kejuruan-modules.update', $kejuruan_module->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, KejuruanModule $kejuruan_module)
    {
        $this->authorizeOwner($request, $kejuruan_module);

        $programOptions = $this->programOptionsForInstructor($request->user()->id);
        $allowedProgramIds = $programOptions->keys()->all();

        $data = $request->validate([
            'program_id' => ['required', Rule::in($allowedProgramIds)],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link_url' => 'required|url|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $kejuruan_module->update($data);

        return redirect()->route('instructor.lms.kejuruan-modules.index')
            ->with('success', 'Modul kejuruan berhasil diperbarui.');
    }

    public function destroy(Request $request, KejuruanModule $kejuruan_module)
    {
        $this->authorizeOwner($request, $kejuruan_module);
        $kejuruan_module->delete();

        return redirect()->route('instructor.lms.kejuruan-modules.index')
            ->with('success', 'Modul kejuruan dihapus.');
    }

    private function programOptionsForInstructor(string $instructorId)
    {
        $classIds = CourseClass::where('instructor_id', $instructorId)->pluck('id');
        if ($classIds->isEmpty()) {
            return collect();
        }

        $programIds = TrainingSchedule::whereIn('id', $classIds)
            ->whereNotNull('program_id')
            ->pluck('program_id')
            ->unique()
            ->values();

        if ($programIds->isEmpty()) {
            return collect();
        }

        return Program::whereIn('id', $programIds)->orderBy('judul')->pluck('judul', 'id');
    }

    private function authorizeOwner(Request $request, KejuruanModule $module): void
    {
        if ($module->owner_id !== $request->user()->id) {
            abort(403);
        }
    }
}
