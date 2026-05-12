@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\CourseAssignment::statuses();
    $assessmentOptions = $assessmentOptions ?? [
        'regular' => 'Reguler',
        'module_quiz' => 'Quiz Akhir Bab',
        'final_exam' => 'Ujian Final',
        'final_project' => 'Proyek Akhir',
    ];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Tugas & Quiz</h4>
        <small class="text-muted">Kelola tugas/quiz per kelas dengan workflow review/publish.</small>
    </div>
    <a href="{{ request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? route('instructor.lms.course-assignment.create') : route('admin.course-assignment.create') }}" class="btn btn-primary btn-sm">Tambah Tugas</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-sm-3">
                <label class="form-label mb-1">Filter Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('status', $statusFilter ?? null) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label mb-1">Filter Kelas</label>
                <select name="class_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($classes as $id => $title)
                        <option value="{{ $id }}" @selected(request('class_id', $classFilter ?? null) === $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">Terapkan</button>
            </div>
            @if(request('status') || request('class_id'))
                <div class="col-auto">
                    <a href="{{ request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? route('instructor.lms.course-assignment.index') : route('admin.course-assignment.index') }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Judul</th>
                        <th>Kelas</th>
                        <th>Bab</th>
                        <th>Kategori</th>
                        <th>Tipe</th>
                        <th>Due</th>
                        <th>Bobot</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr>
                            <td>{{ $assignments->firstItem() + $loop->index }}</td>
                            <td>{{ $assignment->title }}</td>
                            <td>{{ $assignment->course->title ?? '-' }}</td>
                            <td>{{ $assignment->module?->title ?? '-' }}</td>
                            <td>{{ $assessmentOptions[$assignment->assessment_type ?? 'regular'] ?? 'Reguler' }}</td>
                            <td>
                                @if($assignment->type === 'quiz')
                                    @php
                                        $scopeLabel = ($assignment->quiz_scope ?? 'class') === 'selection' ? 'CBT Seleksi' : 'CBT Kelas';
                                    @endphp
                                    <span class="badge bg-warning text-dark">{{ $scopeLabel }}</span>
                                @else
                                    <span class="badge bg-info text-dark text-uppercase">{{ $assignment->type }}</span>
                                @endif
                            </td>
                            <td>{{ $assignment->due_at ? $assignment->due_at->format('d M Y H:i') : '-' }}</td>
                            <td>{{ $assignment->weight }}%</td>
                            <td class="text-nowrap">
                                @php
                                    $status = $assignment->status ?? 'draft';
                                    $badgeClass = [
                                        'draft' => 'bg-secondary',
                                        'pending' => 'bg-warning text-dark',
                                        'published' => 'bg-success',
                                    ][$status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $statusOptions[$status] ?? ucfirst($status) }}</span>
                                <span class="badge {{ $assignment->is_active ? 'bg-success' : 'bg-dark' }}">{{ $assignment->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="text-end">
                                @php
                                    $editRoute = request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') 
                                        ? route('instructor.lms.course-assignment.edit', $assignment->id) 
                                        : route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-assignment.edit' : 'admin.course-assignment.edit'), $assignment->id);
                                    
                                    $exportRoute = request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') 
                                        ? route('instructor.lms.course-assignment.export', $assignment->id) 
                                        : route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-assignment.export' : 'admin.course-assignment.export'), $assignment->id);
                                        
                                    $destroyRoute = request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') 
                                        ? route('instructor.lms.course-assignment.destroy', $assignment->id) 
                                        : route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-assignment.destroy' : 'admin.course-assignment.destroy'), $assignment->id);
                                @endphp
                                <a href="{{ $editRoute }}" class="btn btn-sm btn-warning">Ubah</a>
                                <a href="{{ $exportRoute }}" class="btn btn-sm btn-outline-secondary">Ekspor Nilai</a>
                                <form action="{{ $destroyRoute }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus tugas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Belum ada tugas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $assignments->links() }}
        </div>
    </div>
</div>
@endsection
