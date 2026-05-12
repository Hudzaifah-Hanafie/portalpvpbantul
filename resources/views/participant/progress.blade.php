@extends('layouts.participant')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 fade-up">
    <div>
        <h4 class="mb-1 section-title">Progres Saya</h4>
        <div class="section-subtitle">Ringkasan kehadiran, submission, dan nilai di kelas aktif.</div>
    </div>
</div>

@forelse($classes as $row)
    <div class="card stat-card card-hover mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="mb-0">{{ $row['class']->title }}</h5>
                    <small class="text-muted">{{ $row['class']->instructor?->name ?? '-' }}</small>
                </div>
                <div class="text-end">
                    @if($row['attendance_rate'] !== null)
                        <div class="fw-semibold">{{ $row['attendance_rate'] }}% hadir</div>
                        <div class="small text-muted">{{ $row['attended'] }}/{{ $row['total_sessions'] }} sesi</div>
                    @else
                        <div class="small text-muted">Belum ada presensi</div>
                    @endif
                </div>
            </div>
            <div class="mb-3">
                <div class="progress" style="height:6px;">
                    <div class="progress-bar" style="width: {{ $row['attendance_rate'] ?? 0 }}%"></div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-3">
                <div>
                    <div class="small text-muted">Submission</div>
                    <div class="fw-semibold">{{ $row['submitted'] }} terkumpul • {{ $row['graded'] }} dinilai</div>
                </div>
                <div>
                    <div class="small text-muted">Nilai Rata-rata</div>
                    <div class="fw-semibold">{{ $row['avg_score'] ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="text-muted">Belum ada kelas aktif.</div>
@endforelse
@endsection
