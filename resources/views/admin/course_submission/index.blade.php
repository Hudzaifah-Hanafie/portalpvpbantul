@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\CourseSubmission::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Submission Tugas</h4>
        <small class="text-muted">Kelola submission peserta dan penilaian.</small>
    </div>
    <span class="text-muted small">Gunakan filter untuk mempercepat pencarian.</span>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-sm-3">
                <label class="form-label mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('status', $statusFilter ?? null) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label mb-1">Kelas</label>
                <select name="class_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($classes as $id => $title)
                        <option value="{{ $id }}" @selected(request('class_id', $classFilter ?? null) === $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label mb-1">Tugas</label>
                <select name="assignment_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($assignments as $id => $title)
                        <option value="{{ $id }}" @selected(request('assignment_id', $assignmentFilter ?? null) === $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">Terapkan</button>
            </div>
            @if(request('status') || request('class_id') || request('assignment_id'))
                <div class="col-auto">
                    <a href="{{ request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? route('instructor.lms.course-submission.index') : route('admin.course-submission.index') }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
            <div class="col-auto ms-auto">
                <a href="{{ request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? route('instructor.lms.course-submission.export.csv', request()->all()) : route('admin.course-submission.export.csv', request()->all()) }}" class="btn btn-sm btn-outline-primary">Ekspor CSV</a>
            </div>
        </form>

        @if($submissionGroups->isEmpty())
            <div class="text-center text-muted py-4">Belum ada submission.</div>
        @else
            <div class="accordion" id="submissionKejuruanAccordion">
                @foreach($submissionGroups as $kejuruan => $classGroups)
                    @php
                        $kejuruanId = 'kejuruan-submission-' . $loop->index;
                        $kejuruanCount = $classGroups->reduce(fn ($carry, $items) => $carry + $items->count(), 0);
                    @endphp
                    <div class="accordion-item mb-2">
                        <h2 class="accordion-header" id="heading-{{ $kejuruanId }}">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $kejuruanId }}" aria-expanded="true" aria-controls="collapse-{{ $kejuruanId }}">
                                {{ strtoupper($kejuruan) }}
                                <span class="badge bg-light text-muted ms-2">{{ $kejuruanCount }}</span>
                            </button>
                        </h2>
                        <div id="collapse-{{ $kejuruanId }}" class="accordion-collapse collapse show" aria-labelledby="heading-{{ $kejuruanId }}" data-bs-parent="#submissionKejuruanAccordion">
                            <div class="accordion-body p-0">
                                <div class="accordion" id="classAccordion-{{ $kejuruanId }}">
                                    @foreach($classGroups as $classTitle => $items)
                                        @php
                                            $classId = $kejuruanId . '-class-' . $loop->index;
                                        @endphp
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading-{{ $classId }}">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $classId }}" aria-expanded="false" aria-controls="collapse-{{ $classId }}">
                                                    {{ $classTitle }}
                                                    <span class="badge bg-light text-muted ms-2">{{ $items->count() }}</span>
                                                </button>
                                            </h2>
                                            <div id="collapse-{{ $classId }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $classId }}" data-bs-parent="#classAccordion-{{ $kejuruanId }}">
                                                <div class="accordion-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Peserta</th>
                                                                    <th>Tugas</th>
                                                                    <th>Dikirim</th>
                                                                    <th>Nilai</th>
                                                                    <th>Status</th>
                                                                    <th class="text-end">Aksi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($items as $sub)
                                                                    <tr>
                                                                        <td>{{ $loop->iteration }}</td>
                                                                        <td>{{ $sub->user?->name ?? $sub->user_id }}</td>
                                                                        <td>
                                                                            <div class="small fw-bold">{{ $sub->assignment?->title ?? '-' }}</div>
                                                                            <div class="small text-muted">{{ $sub->assignment?->course?->title ?? '-' }}</div>
                                                                        </td>
                                                                        <td class="small">{{ $sub->submitted_at ? $sub->submitted_at->format('d M Y H:i') : '-' }}</td>
                                                                        <td class="fw-bold">{{ $sub->total_score ?? '-' }}</td>
                                                                        <td class="text-nowrap">
                                                                            @php
                                                                                $badgeClass = [
                                                                                    'submitted' => 'bg-warning text-dark',
                                                                                    'graded' => 'bg-success',
                                                                                    'reopened' => 'bg-secondary',
                                                                                ][$sub->status] ?? 'bg-secondary';
                                                                            @endphp
                                                                            <span class="badge {{ $badgeClass }}">{{ $statusOptions[$sub->status] ?? $sub->status }}</span>
                                                                        </td>
                                                                        <td class="text-end">
                                                                            @php
                                                                                $editRoute = request()->routeIs('instructor.*') || request()->routeIs('*.lms.*')
                                                                                    ? route('instructor.lms.course-submission.edit', $sub->id)
                                                                                    : route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-submission.edit' : 'admin.course-submission.edit'), $sub->id);
                                                                                $deleteRoute = request()->routeIs('instructor.*') || request()->routeIs('*.lms.*')
                                                                                    ? route('instructor.lms.course-submission.destroy', $sub->id)
                                                                                    : route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-submission.destroy' : 'admin.course-submission.destroy'), $sub->id);
                                                                            @endphp
                                                                            <a href="{{ $editRoute }}" class="btn btn-sm btn-warning">Nilai/Ubah</a>
                                                                            <form action="{{ $deleteRoute }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus submission ini?')">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button class="btn btn-sm btn-danger">Hapus</button>
                                                                            </form>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
