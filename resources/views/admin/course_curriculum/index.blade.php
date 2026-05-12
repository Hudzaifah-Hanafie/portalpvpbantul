@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Kurikulum</h4>
        <small class="text-muted">Kelola kurikulum kelas berdasarkan SKKNI & matriks pelatihan.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-curriculum.create' : 'admin.course-curriculum.create'), $classFilter ? ['class_id' => $classFilter] : []) }}" class="btn btn-primary btn-sm">Tambah Kurikulum</a>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Filter Kelas</label>
                <select name="class_id" class="form-select">
                    <option value="">Semua kelas</option>
                    @foreach($classes as $id => $label)
                        <option value="{{ $id }}" @selected($classFilter === $id)>{{ $label }}</option>
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
                    <th>Judul Kurikulum</th>
                    <th>Kelas</th>
                    <th>Metode</th>
                    <th>Total JP</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($curricula as $curriculum)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $curriculum->title }}</div>
                            <small class="text-muted">SKKNI: {{ $curriculum->skkni_reference ?? '-' }}</small>
                        </td>
                        @php
                            $course = $curriculum->course;
                            $moduleCount = $course?->modules?->count() ?? 0;
                            $materialCount = $course?->modules?->sum(fn ($module) => $module->materials->count()) ?? 0;
                            $sessionCount = $course?->sessions?->count() ?? 0;
                            $formatLabel = $course?->format ? ucfirst($course->format) : '-';
                        @endphp
                        <td>
                            <div class="fw-semibold">{{ $course?->title ?? '-' }}</div>
                            <small class="text-muted">Format: {{ $formatLabel }} • Bab: {{ $moduleCount }} • Materi: {{ $materialCount }} • Sesi: {{ $sessionCount }}</small>
                        </td>
                        <td class="text-capitalize">{{ $curriculum->method }}</td>
                        <td>{{ $curriculum->total_jp_theory + $curriculum->total_jp_practice }} JP</td>
                        <td>
                            <span class="badge {{ $curriculum->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $curriculum->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-curriculum-unit.index' : 'admin.course-curriculum-unit.index'), ['curriculum_id' => $curriculum->id]) }}" class="btn btn-sm btn-outline-secondary">Unit</a>
                            <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-curriculum.edit' : 'admin.course-curriculum.edit'), $curriculum->id) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                            <form action="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-curriculum.destroy' : 'admin.course-curriculum.destroy'), $curriculum->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kurikulum ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada kurikulum.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $curricula->links() }}
</div>
@endsection
