@extends('layouts.participant')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 fade-up">
    <div>
        <h4 class="mb-1 section-title">Kelas Saya</h4>
        <div class="section-subtitle">Masuk ke kelas untuk melihat materi, tugas, dan presensi.</div>
    </div>
    <a href="{{ route('participant.dashboard') }}" class="btn btn-sm btn-outline-light border-0 shadow-sm bg-white text-dark">Kembali ke Dashboard</a>
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
        <div class="col-lg-6">
            <div class="card stat-card card-hover h-100 fade-up">
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
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('participant.class.show', $class) }}" class="btn btn-primary lms-cta">Masuk Kelas</a>
                        <a href="{{ route('participant.assignments', ['class_id' => $class->id]) }}" class="btn btn-outline-secondary lms-cta">Tugas</a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info alert-modern mb-0">Anda belum terdaftar pada kelas mana pun.</div>
        </div>
    @endforelse
</div>
@endsection
