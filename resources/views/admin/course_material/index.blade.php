@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Materi Pembelajaran</h4>
        <small class="text-muted">Kelola materi yang berada di dalam bab.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-material.create' : 'admin.course-material.create')) }}" class="btn btn-primary">Tambah Materi</a>
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
            <div class="col-sm-4">
                <label class="form-label mb-1">Filter Bab</label>
                <select name="module_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($modules as $id => $title)
                        <option value="{{ $id }}" @selected($moduleFilter === $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">Terapkan</button>
            </div>
            @if($classFilter || $moduleFilter)
                <div class="col-auto">
                    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-material.index' : 'admin.course-material.index')) }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
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
                        <th>Materi</th>
                        <th>Bab</th>
                        <th>Kelas</th>
                        <th>Urutan</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $material)
                        <tr>
                            <td>{{ $materials->firstItem() + $loop->index }}</td>
                            <td>{{ $material->title }}</td>
                            <td>{{ $material->module?->title ?? '-' }}</td>
                            <td>{{ $material->module?->course?->title ?? '-' }}</td>
                            <td>{{ $material->sort_order }}</td>
                            <td>
                                <span class="badge {{ $material->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $material->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-material.edit' : 'admin.course-material.edit'), $material) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
                                <form action="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-material.destroy' : 'admin.course-material.destroy'), $material) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus materi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada materi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $materials->links() }}
        </div>
    </div>
</div>
@endsection
