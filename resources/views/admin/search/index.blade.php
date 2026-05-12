@extends('layouts.admin')

@php
    $lmsPrefix = $isInstructor ? 'instructor.lms.' : 'admin.';
    $canEnrollments = auth()->user()?->hasPermission('manage-enrollment');
@endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-0">Pencarian LMS</h4>
        <small class="text-muted">Cari kelas, tugas, sesi, pengumuman, dan peserta (jika berizin).</small>
    </div>
    <form method="GET" action="{{ route('lms.search') }}" class="d-flex gap-2">
        <input type="text" name="q" value="{{ $query }}" class="form-control form-control-sm" placeholder="Cari kelas, peserta, materi...">
        <button class="btn btn-sm btn-primary">Cari</button>
    </form>
</div>

@if($query === '')
    <div class="alert alert-info alert-modern">Masukkan kata kunci untuk memulai pencarian di LMS.</div>
@endif

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Kelas</h6>
                @forelse($classes as $class)
                    <div class="border rounded p-2 mb-2">
                        <div class="fw-semibold">{{ $class->title }}</div>
                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit(strip_tags($class->description), 90) }}</div>
                        <div class="small text-muted">Format: {{ $class->format }}</div>
                        <a href="{{ route($lmsPrefix . 'course-class.edit', $class->id) }}" class="small">Kelola</a>
                    </div>
                @empty
                    <div class="text-muted small">Tidak ada kelas yang cocok.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Tugas / Quiz</h6>
                @forelse($assignments as $assignment)
                    <div class="border rounded p-2 mb-2">
                        <div class="fw-semibold">{{ $assignment->title }}</div>
                        <div class="text-muted small">{{ $assignment->course?->title ?? '-' }}</div>
                        <div class="small text-muted">Tipe: {{ $assignment->type }}</div>
                        <a href="{{ route($lmsPrefix . 'course-assignment.edit', $assignment->id) }}" class="small">Kelola</a>
                    </div>
                @empty
                    <div class="text-muted small">Tidak ada tugas yang cocok.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Sesi Kelas</h6>
                @forelse($sessions as $session)
                    <div class="border rounded p-2 mb-2">
                        <div class="fw-semibold">{{ $session->title ?? 'Sesi' }}</div>
                        <div class="text-muted small">{{ $session->course?->title ?? '-' }}</div>
                        <div class="small text-muted">{{ $session->start_at?->format('d M Y H:i') ?? '-' }}</div>
                        <a href="{{ route($lmsPrefix . 'course-session.edit', $session->id) }}" class="small">Kelola</a>
                    </div>
                @empty
                    <div class="text-muted small">Tidak ada sesi yang cocok.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Pengumuman</h6>
                @forelse($announcements as $announcement)
                    <div class="border rounded p-2 mb-2">
                        <div class="fw-semibold">{{ $announcement->title }}</div>
                        <div class="text-muted small">{{ $announcement->course?->title ?? '-' }}</div>
                        <div class="small text-muted">{{ $announcement->published_at?->format('d M Y H:i') ?? '-' }}</div>
                        <a href="{{ route($lmsPrefix . 'course-announcement.edit', $announcement->id) }}" class="small">Kelola</a>
                    </div>
                @empty
                    <div class="text-muted small">Tidak ada pengumuman yang cocok.</div>
                @endforelse
            </div>
        </div>
    </div>
    @if($canEnrollments)
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Pendaftaran Peserta</h6>
                    @forelse($enrollments as $enrollment)
                        <div class="border rounded p-2 mb-2">
                            <div class="fw-semibold">{{ $enrollment->user?->name ?? '-' }}</div>
                            <div class="text-muted small">{{ $enrollment->user?->email ?? '-' }}</div>
                            <div class="small text-muted">{{ $enrollment->course?->title ?? '-' }}</div>
                            <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-enrollment.edit' : 'admin.course-enrollment.edit'), $enrollment->id) }}" class="small">Kelola</a>
                        </div>
                    @empty
                        <div class="text-muted small">Tidak ada peserta yang cocok.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
