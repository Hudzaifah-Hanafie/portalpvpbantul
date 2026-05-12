@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Bab Pembelajaran</h4>
        <small class="text-muted">Kelola bab untuk pengelompokan materi dan kuis.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-module.create' : 'admin.course-module.create')) }}" class="btn btn-primary">Tambah Bab</a>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <label class="form-label mb-1">Filter Kelas</label>
                <select name="class_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($classes as $id => $title)
                        <option value="{{ $id }}" @selected($classFilter === $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">Terapkan</button>
            </div>
            @if($classFilter)
                <div class="col-auto">
                    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-module.index' : 'admin.course-module.index')) }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Bab</th>
                        <th>Kelas</th>
                        <th>Urutan</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($modules as $module)
                        <tr>
                            <td>{{ $modules->firstItem() + $loop->index }}</td>
                            <td>{{ $module->title }}</td>
                            <td>{{ $module->course?->title ?? '-' }}</td>
                            <td>{{ $module->sort_order }}</td>
                            <td>
                                <span class="badge {{ $module->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $module->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-module.edit' : 'admin.course-module.edit'), $module) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
                                <form action="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-module.destroy' : 'admin.course-module.destroy'), $module) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus bab ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada bab pembelajaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $modules->links() }}
        </div>
    </div>
</div>
@endsection
