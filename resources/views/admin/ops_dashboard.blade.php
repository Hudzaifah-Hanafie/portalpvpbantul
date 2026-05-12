@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Dasbor Operasional</h4>
        <small class="text-muted">SLA grading & presensi hari ini.</small>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Submission menunggu penilaian</div>
                <div class="fw-bold">{{ $stats['pending_submissions'] }}</div>
                @if($stats['oldest_pending_hours'] !== null)
                    <div class="small text-muted">Tertua: {{ $stats['oldest_pending_hours'] }} jam</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Sesi hari ini</div>
                <div class="fw-bold">{{ $stats['today_sessions'] }}</div>
                <div class="small text-muted">{{ $stats['today_sessions_no_attendance'] }} tanpa presensi</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Scheduler</div>
                <div class="fw-bold">{{ $stats['scheduler_ok'] ? 'Aktif' : 'Periksa' }}</div>
                <div class="small text-muted">{{ $stats['last_heartbeat'] ? $stats['last_heartbeat']->diffForHumans() : 'N/A' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <h6 class="mb-3">Sesi tanpa presensi hari ini</h6>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kelas</th>
                        <th>Sesi</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($noAttendanceSessions as $sess)
                        <tr>
                            <td>{{ $sess->course->title ?? '-' }}</td>
                            <td>{{ $sess->title }}</td>
                            <td class="small">{{ $sess->start_at?->format('d M Y H:i') }} - {{ $sess->end_at?->format('H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Semua sesi sudah ada presensi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
