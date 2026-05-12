@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\CourseClass::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Kelas</h4>
        <small class="text-muted">Manajemen kelas sinkron, asinkron, atau blended dengan workflow review/publish.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-class.create' : 'admin.course-class.create')) }}" class="btn btn-primary btn-sm">Tambah Kelas</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-sm-4 col-md-3">
                <label class="form-label mb-1">Filter Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('status', $statusFilter ?? null) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4 col-md-3">
                <label class="form-label mb-1">Tag</label>
                <input type="text" name="tag" class="form-control form-control-sm" value="{{ request('tag', $tagFilter ?? '') }}" placeholder="contoh: desain">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">Terapkan</button>
            </div>
            @if(request('status') || request('tag'))
                <div class="col-auto">
                    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-class.index' : 'admin.course-class.index')) }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Judul</th>
                        <th>Format</th>
                        <th>Instruktur</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $class)
                        <tr>
                            <td>{{ $classes->firstItem() + $loop->index }}</td>
                            <td>
                                <strong>{{ $class->title }}</strong>
                                <div class="text-muted small">{{ \Illuminate\Support\Str::limit(strip_tags($class->description), 80) }}</div>
                                @if(!empty($class->tags))
                                    <div class="mt-1 d-flex flex-wrap gap-1">
                                        @foreach($class->tags as $tag)
                                            <span class="badge bg-light text-dark border">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            @php
                                $formatLabel = [
                                    'sinkron' => 'Daring Sinkron',
                                    'asinkron' => 'Daring Asinkron',
                                    'blended' => 'Blended',
                                    'luring' => 'Luring',
                                ][$class->format] ?? strtoupper($class->format ?? '-');
                            @endphp
                            <td><span class="badge bg-info text-dark">{{ $formatLabel }}</span></td>
                            <td>{{ $class->instructor?->name ?? '-' }}</td>
                            <td class="text-nowrap">
                                @php
                                    $status = $class->status ?? 'draft';
                                    $badgeClass = [
                                        'draft' => 'bg-secondary',
                                        'pending' => 'bg-warning text-dark',
                                        'published' => 'bg-success',
                                    ][$status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $statusOptions[$status] ?? ucfirst($status) }}</span>
                                <span class="badge {{ $class->is_active ? 'bg-success' : 'bg-dark' }}">{{ $class->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-class.edit' : 'admin.course-class.edit'), $class->id) }}" class="btn btn-sm btn-warning">Ubah</a>
                                @if(auth()->user()?->hasPermission('manage-enrollment'))
                                    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-enrollment.index' : 'admin.course-enrollment.index'), ['class_id' => $class->id]) }}" class="btn btn-sm btn-outline-primary">Peserta</a>
                                @endif
                                <form action="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-class.destroy' : 'admin.course-class.destroy'), $class->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kelas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada kelas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $classes->links() }}
        </div>
    </div>
</div>
@endsection
