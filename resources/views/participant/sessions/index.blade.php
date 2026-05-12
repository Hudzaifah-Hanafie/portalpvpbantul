@extends('layouts.participant')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 fade-up">
    <div>
        <h4 class="mb-1 section-title">Presensi</h4>
        <div class="section-subtitle">Isi presensi untuk sesi pelatihan yang aktif.</div>
    </div>
</div>

<div class="card stat-card card-hover">
    <div class="card-body">
        <div class="list-group list-group-flush">
            @forelse($sessions as $session)
                @php
                    $attendance = $attendanceMap->get($session->id);
                    $expired = $session->attendance_code_expires_at && $session->attendance_code_expires_at->isPast();
                    $now = now();
                    $isOngoing = $session->start_at && $session->end_at
                        ? $session->start_at->lte($now) && $session->end_at->gte($now)
                        : ($session->start_at ? $session->start_at->lte($now) : false);
                    $isUpcoming = $session->start_at ? $session->start_at->gt($now) : false;
                    $sessionBadge = $isOngoing ? 'bg-success' : ($isUpcoming ? 'bg-info text-dark' : 'bg-secondary');
                    $sessionLabel = $isOngoing ? 'Sedang Berlangsung' : ($isUpcoming ? 'Akan Datang' : 'Selesai');
                @endphp
                <div class="list-group-item">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <div class="fw-bold">{{ $session->course->title ?? 'Pelatihan' }}</div>
                            <div class="text-muted small">{{ $session->title ?? 'Sesi' }}</div>
                            <div class="text-muted small">
                                {{ $session->start_at ? $session->start_at->format('d M Y H:i') : '-' }}
                                @if($session->end_at) - {{ $session->end_at->format('H:i') }} @endif
                            </div>
                            <span class="badge {{ $sessionBadge }} mt-1">{{ $sessionLabel }}</span>
                            @if($session->attendance_code_expires_at)
                                <div class="text-muted small">
                                    Presensi dibuka sampai {{ $session->attendance_code_expires_at->format('d M Y H:i') }}
                                    <span class="ms-1 badge bg-light text-dark border attendance-countdown" data-expire="{{ $session->attendance_code_expires_at->toIso8601String() }}">--:--</span>
                                </div>
                            @endif
                            @if($session->meeting_link)
                                <div class="small text-muted mt-1">
                                    <i class="fas fa-video me-1"></i> Link tatap muka tersedia
                                </div>
                            @endif
                        </div>
                        <div class="text-end">
                            @if($attendance)
                                <span class="badge bg-success">Sudah Presensi</span>
                                <div class="text-muted small">{{ $attendance->checked_at?->format('d M Y H:i') }}</div>
                            @elseif($expired)
                                <span class="badge bg-secondary">Kode Kadaluarsa</span>
                            @else
                                <a href="{{ route('participant.sessions.attendance.form', $session) }}" class="btn btn-primary lms-cta">Isi Presensi</a>
                            @endif
                            @if($session->meeting_link)
                                <div class="mt-2">
                                    <a href="{{ $session->meeting_link }}" class="btn btn-outline-primary lms-cta" target="_blank" rel="noopener">Buka Meet/Zoom</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-muted">Belum ada sesi yang aktif untuk presensi.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const timers = document.querySelectorAll('.attendance-countdown');
        if (!timers.length) return;

        function formatRemaining(ms) {
            if (ms <= 0) return 'Berakhir';
            const totalSeconds = Math.floor(ms / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            if (hours > 0) {
                return `${hours}j ${minutes}m`;
            }
            return `${minutes}m ${seconds}s`;
        }

        function tick() {
            timers.forEach((el) => {
                const expire = new Date(el.dataset.expire);
                const diff = expire - new Date();
                el.textContent = formatRemaining(diff);
            });
        }

        tick();
        setInterval(tick, 1000);
    })();
</script>
@endpush
