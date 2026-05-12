<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KejuruanModule;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;

class KejuruanModuleController extends Controller
{
    public function index(Request $request)
    {
        $programOptions = Program::orderBy('judul')->pluck('judul', 'id');
        $ownerOptions = $this->instructorOptions();

        $query = KejuruanModule::with(['program', 'owner'])->orderByDesc('updated_at');
        $programFilter = $request->input('program_id');
        $ownerFilter = $request->input('owner_id');
        $search = trim((string) $request->input('q', ''));

        if ($programFilter) {
            $query->where('program_id', $programFilter);
        }
        if ($ownerFilter) {
            $query->where('owner_id', $ownerFilter);
        }
        if ($search !== '') {
            $query->where('title', 'ILIKE', "%{$search}%");
        }

        $modules = $query->get();
        $grouped = $modules->groupBy(fn ($module) => $module->program?->judul ?? 'LAINNYA');

        return view('kejuruan_modules.index', [
            'modulesByKejuruan' => $grouped,
            'programOptions' => $programOptions,
            'ownerOptions' => $ownerOptions,
            'programFilter' => $programFilter,
            'ownerFilter' => $ownerFilter,
            'search' => $search,
            'routePrefix' => 'admin.kejuruan-modules',
            'pageTitle' => 'Modul Kejuruan',
            'showOwner' => true,
        ]);
    }

    public function create()
    {
        return view('kejuruan_modules.form', [
            'module' => new KejuruanModule(),
            'programOptions' => Program::orderBy('judul')->pluck('judul', 'id'),
            'ownerOptions' => $this->instructorOptions(),
            'routePrefix' => 'admin.kejuruan-modules',
            'pageTitle' => 'Tambah Modul Kejuruan',
            'showOwnerField' => true,
            'action' => route('admin.kejuruan-modules.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;
        $data['is_active'] = $request->boolean('is_active');

        KejuruanModule::create($data);

        return redirect()->route('admin.kejuruan-modules.index')
            ->with('success', 'Modul kejuruan berhasil ditambahkan.');
    }

    public function edit(KejuruanModule $kejuruan_module)
    {
        return view('kejuruan_modules.form', [
            'module' => $kejuruan_module,
            'programOptions' => Program::orderBy('judul')->pluck('judul', 'id'),
            'ownerOptions' => $this->instructorOptions(),
            'routePrefix' => 'admin.kejuruan-modules',
            'pageTitle' => 'Edit Modul Kejuruan',
            'showOwnerField' => true,
            'action' => route('admin.kejuruan-modules.update', $kejuruan_module->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, KejuruanModule $kejuruan_module)
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active');

        $kejuruan_module->update($data);

        return redirect()->route('admin.kejuruan-modules.index')
            ->with('success', 'Modul kejuruan berhasil diperbarui.');
    }

    public function destroy(KejuruanModule $kejuruan_module)
    {
        $kejuruan_module->delete();

        return redirect()->route('admin.kejuruan-modules.index')
            ->with('success', 'Modul kejuruan dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'program_id' => 'required|exists:programs,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link_url' => 'required|url|max:2048',
            'owner_id' => 'nullable|exists:users,id',
            'is_active' => 'nullable|boolean',
        ]);
    }

    private function instructorOptions()
    {
        return User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['instructor', 'instruktur']);
        })->orderBy('name')->pluck('name', 'id');
    }
}
