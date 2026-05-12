@extends('layouts.participant')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 fade-up">
    <div>
        <h4 class="mb-1 section-title">Dashboard Peserta</h4>
        <div class="section-subtitle">Ringkasan aktivitas kelas, presensi, dan tugas Anda.</div>
    </div>
    <a href="{{ route('program') }}" class="btn btn-sm btn-outline-light border-0 shadow-sm bg-white text-dark">Cari Pelatihan</a>
</div>

@if($classesCount === 0 && $pendingCount > 0)
    <div class="alert alert-info alert-modern">
        Pendaftaran Anda sedang diproses. Menu kelas, tugas, dan presensi akan muncul setelah Anda diterima.
    </div>
@endif

@php
    $nextSession = $upcomingSessions->first();
@endphp

<div class="card stat-card card-hover mb-4 fade-up">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
                <div class="fw-semibold">Langkah Berikutnya</div>
                <div class="small text-muted">Selesaikan tahapan agar progres belajar Anda optimal.</div>
            </div>
            <div class="text-muted small">
                Progress: {{ $onboardingProgress }} / {{ count($onboardingSteps) }}
            </div>
        </div>
        <div class="progress mb-3" style="height:8px;">
            <div class="progress-bar" style="width: {{ count($onboardingSteps) ? round(($onboardingProgress / count($onboardingSteps)) * 100) : 0 }}%"></div>
        </div>
        <div class="row g-2">
            @foreach($onboardingSteps as $step)
                <div class="col-md-3">
                    <div class="card-soft p-3 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="fw-semibold mb-1">
                                @if($step['done'])
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                @elseif(($step['status'] ?? null) === 'danger')
                                    <i class="fas fa-times-circle text-danger me-1"></i>
                                @else
                                    <i class="far fa-circle text-muted me-1"></i>
                                @endif
                                {{ $step['label'] }}
                            </div>
                            <div class="small {{ ($step['status'] ?? null) === 'danger' ? 'text-danger' : 'text-muted' }}">
                                {{ $step['note'] ?? ($step['done'] ? 'Selesai' : 'Belum selesai') }}
                            </div>
                        </div>
                        <div class="mt-2">
                            @php
                                $disabled = $step['done'] || (($step['status'] ?? null) === 'danger');
                            @endphp
                            <a href="{{ $step['url'] }}" class="btn btn-outline-primary lms-cta w-100 {{ $disabled ? 'disabled' : '' }}">
                                {{ $step['cta'] }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card card-hover h-100 fade-up delay-1">
            <div class="card-body">
                <div class="text-muted small">Kelas Aktif</div>
                <div class="fs-4 fw-bold">{{ $classesCount }}</div>
                <div class="small text-muted">kelas terdaftar</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card card-hover h-100 fade-up delay-2">
            <div class="card-body">
                <div class="text-muted small">Tugas Belum Selesai</div>
                <div class="fs-4 fw-bold">{{ $pendingAssignmentsCount }}</div>
                <div class="small text-muted">butuh perhatian</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card card-hover h-100 fade-up delay-3">
            <div class="card-body">
                <div class="text-muted small">Kehadiran</div>
                <div class="fs-4 fw-bold">{{ $attendanceRate !== null ? $attendanceRate . '%' : '-' }}</div>
                <div class="small text-muted">rata-rata hadir</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card card-hover h-100 fade-up delay-4">
            <div class="card-body">
                <div class="text-muted small">Sesi Terdekat</div>
                @if($nextSession)
                    <div class="fw-semibold">{{ $nextSession->course->title ?? 'Pelatihan' }}</div>
                    <div class="small text-muted">{{ $nextSession->start_at?->format('d M Y H:i') ?? '-' }}</div>
                @else
                    <div class="fs-6 fw-semibold">Belum ada</div>
                    <div class="small text-muted">menunggu jadwal</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0 section-title">Kelas Aktif</h5>
            <a href="{{ route('participant.classes') }}" class="small text-decoration-none">Lihat semua</a>
        </div>
        <div class="row g-3">
            @forelse($classes as $class)
                @php
                    $totalSessions = $totalSessionsByClass[$class->id] ?? 0;
                    $attended = $attendanceByClass[$class->id] ?? 0;
                    $rate = $totalSessions > 0 ? round(($attended / $totalSessions) * 100, 1) : null;
                    $nextSession = $nextSessionByClass[$class->id] ?? null;
                @endphp
                @php
                    $formatLabel = [
                        'sinkron' => 'Daring Sinkron',
                        'asinkron' => 'Daring Asinkron',
                        'blended' => 'Blended',
                        'luring' => 'Luring',
                    ][$class->format] ?? strtoupper($class->format ?? '-');
                @endphp
                <div class="col-md-6">
                    <div class="card stat-card card-hover h-100">
                        <div class="card-body">
                            <div class="fw-semibold">{{ $class->title }}</div>
                            <div class="small text-muted mb-2">{{ $class->instructor?->name ?? '-' }}</div>
                            <span class="badge badge-outline mb-2">{{ $formatLabel }}</span>
                            <div class="small text-muted">Tugas aktif: {{ $class->assignments_count ?? 0 }}</div>
                            <div class="small text-muted">Sesi: {{ $class->sessions_count ?? 0 }}</div>
                            <div class="mt-2">
                                <div class="small text-muted">Kehadiran</div>
                                <div class="progress" style="height:6px;">
                                    <div class="progress-bar" style="width: {{ $rate ?? 0 }}%"></div>
                                </div>
                                <div class="small text-muted mt-1">{{ $rate !== null ? $rate . '%' : 'Belum ada presensi' }}</div>
                            </div>
                            <div class="small text-muted mt-2">
                                Sesi berikutnya:
                                <span class="fw-semibold">{{ $nextSession?->start_at?->format('d M Y H:i') ?? 'Belum dijadwalkan' }}</span>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0">
                            <a href="{{ route('participant.class.show', $class) }}" class="btn btn-primary lms-cta">Masuk Kelas</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info alert-modern mb-0">Belum ada kelas aktif.</div>
                </div>
            @endforelse
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card stat-card card-hover mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 section-title">Sesi Terdekat</h6>
                    <a href="{{ route('participant.sessions.index') }}" class="small text-decoration-none">Presensi</a>
                </div>
                @forelse($upcomingSessions as $session)
                    <div class="card-soft p-2 mb-2">
                        <div class="fw-semibold">{{ $session->course->title ?? 'Pelatihan' }}</div>
                        <div class="small text-muted">{{ $session->title ?? 'Sesi' }}</div>
                        <div class="small text-muted">{{ $session->start_at?->format('d M Y H:i') ?? '-' }}</div>
                    </div>
                @empty
                    <div class="text-muted small">Belum ada sesi terjadwal.</div>
                @endforelse
            </div>
        </div>
        <div class="card stat-card card-hover">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 section-title">Tugas Jatuh Tempo</h6>
                    <a href="{{ route('participant.assignments') }}" class="small text-decoration-none">Tugas</a>
                </div>
                @forelse($upcomingAssignments as $assignment)
                    @php($submission = $upcomingSubmissionMap->get($assignment->id))
                    <div class="card-soft p-2 mb-2">
                        <div class="fw-semibold">{{ $assignment->title }}</div>
                        <div class="small text-muted">{{ $assignment->course->title ?? '-' }}</div>
                        <div class="small text-muted">Due: {{ $assignment->due_at?->format('d M Y H:i') ?? '-' }}</div>
                        <div class="small">
                            Status:
                            <span class="badge bg-{{ $submission?->status === 'graded' ? 'success' : ($submission ? 'secondary' : 'warning text-dark') }}">
                                {{ $submission?->status === 'graded' ? 'Dinilai' : ($submission ? 'Terkirim' : 'Belum') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">Tidak ada tugas terdekat.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
