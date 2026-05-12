@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Unit Kompetensi</h4>
        <small class="text-muted">Kelola unit kompetensi, KUK, dan durasi JP.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-curriculum-unit.create' : 'admin.course-curriculum-unit.create')) }}" class="btn btn-primary btn-sm">Tambah Unit</a>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Filter Kelas</label>
                <select name="class_id" class="form-select">
                    <option value="">Semua kelas</option>
                    @foreach($classes as $id => $label)
                        <option value="{{ $id }}" @selected($classFilter === $id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Filter Kurikulum</label>
                <select name="curriculum_id" class="form-select">
                    <option value="">Semua kurikulum</option>
                    @foreach($curricula as $id => $label)
                        <option value="{{ $id }}" @selected($curriculumFilter === $id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100">Terapkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Unit</th>
                    <th>Kurikulum</th>
                    <th>Metode</th>
                    <th>JP (T/P)</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $unit)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $unit->unit_title }}</div>
                            <small class="text-muted">{{ $unit->unit_code ?? '-' }}</small>
                        </td>
                        <td>{{ $unit->curriculum?->title ?? '-' }}</td>
                        <td class="text-capitalize">{{ $unit->method }}</td>
                        <td>{{ $unit->jp_theory }} / {{ $unit->jp_practice }}</td>
                        <td>
                            <span class="badge {{ $unit->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-curriculum-unit.edit' : 'admin.course-curriculum-unit.edit'), $unit->id) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                            <form action="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-curriculum-unit.destroy' : 'admin.course-curriculum-unit.destroy'), $unit->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus unit kompetensi ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada unit kompetensi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $units->links() }}
</div>
@endsection
