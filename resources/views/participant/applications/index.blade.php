@extends('layouts.participant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Pendaftaran & Seleksi</h4>
        <small class="text-muted">Pantau status pendaftaran, jadwal CBT, dan wawancara Anda.</small>
    </div>
    <a href="{{ route('program') }}" class="btn btn-outline-secondary btn-sm">Lihat Katalog</a>
</div>

@forelse($enrollments as $enrollment)
    @php
        $statusLabel = $statusOptions[$enrollment->status] ?? $enrollment->status;
        $adminLabel = $adminStatuses[$enrollment->admin_status] ?? ucfirst($enrollment->admin_status ?? 'pending');
        $cbtInfo = $cbtInfoByClass[$enrollment->course_class_id] ?? ['state' => 'none', 'label' => 'Belum dijadwalkan'];
        $allocation = $enrollment->interviewAllocations->first();
        $session = $allocation?->session;
        $registrationUrl = $enrollment->trainingSchedule?->pendaftaran_link
            ?? ($enrollment->trainingSchedule?->external_id ? "https://skillhub.kemnaker.go.id/pelatihan/{$enrollment->trainingSchedule->external_id}/daftar" : null)
            ?? 'https://skillhub.kemnaker.go.id/app/pelatihan';
        $statusBadge = match($enrollment->status) {
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'pending' => 'bg-warning text-dark',
            'completed' => 'bg-info',
            default => 'bg-secondary'
        };
        $verifyStep = match($enrollment->admin_status) {
            'verified' => 'bg-success',
            'rejected' => 'bg-danger',
            default => 'bg-warning text-dark'
        };
        $cbtStep = $enrollment->written_score !== null ? 'bg-success' : ($enrollment->admin_status === 'rejected' ? 'bg-secondary' : 'bg-warning text-dark');
        $interviewStep = $enrollment->interview_score !== null ? 'bg-success' : ($session ? 'bg-info text-dark' : 'bg-secondary');
        $hasCoupon = !empty($enrollment->coupon_code);
        $couponStep = $hasCoupon ? 'bg-success' : ($enrollment->status === 'approved' ? 'bg-warning text-dark' : 'bg-secondary');
        $couponLabel = $hasCoupon
            ? 'Kupon tersedia'
            : ($enrollment->status === 'approved' ? 'Menunggu kupon' : 'Belum tersedia');
    @endphp
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">{{ $enrollment->course->title ?? $enrollment->trainingSchedule?->judul ?? 'Pelatihan' }}</h5>
                    <div class="small text-muted">Batch ID: {{ $enrollment->trainingSchedule?->batch_id ?? '-' }} • Kuota: {{ $enrollment->trainingSchedule?->kuota ?? '-' }}</div>
                </div>
                <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                <span class="badge bg-success">1. Daftar Portal Satpel</span>
                <span class="badge {{ $verifyStep }}">2. Verifikasi Administrasi: {{ $adminLabel }}</span>
                <span class="badge {{ $cbtStep }}">3. CBT: {{ $enrollment->written_score !== null ? 'Selesai' : $cbtInfo['label'] }}</span>
                <span class="badge {{ $interviewStep }}">4. Wawancara: {{ $enrollment->interview_score !== null ? 'Selesai' : ($session ? 'Terjadwal' : 'Menunggu') }}</span>
                <span class="badge {{ $couponStep }}">5. Kupon SIAP Kerja: {{ $couponLabel }}</span>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-md-4">
                    <div class="small text-muted">Skor CBT</div>
                    <div class="fw-semibold">{{ $enrollment->written_score !== null ? number_format($enrollment->written_score, 2) : '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Skor Wawancara</div>
                    <div class="fw-semibold">{{ $enrollment->interview_score !== null ? number_format($enrollment->interview_score, 2) : '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Skor Akhir</div>
                    <div class="fw-semibold">{{ $enrollment->final_score !== null ? number_format($enrollment->final_score, 2) : '-' }}</div>
                </div>
            </div>

            @if($session)
                <div class="mt-3 small text-muted">
                    Jadwal wawancara: {{ $session->date?->format('d M Y') }} • {{ $session->start_time }}-{{ $session->end_time }} • {{ $session->location }}
                </div>
            @endif

            <div class="d-flex flex-wrap gap-2 mt-3">
                @if($enrollment->written_score === null && $enrollment->admin_status !== 'rejected')
                    <a href="{{ route('participant.assignments', ['class_id' => $enrollment->course_class_id]) }}" class="btn btn-sm btn-outline-primary">Mulai CBT</a>
                @endif
                @if($session)
                    <a href="{{ route('participant.interviews') }}" class="btn btn-sm btn-outline-secondary">Lihat Jadwal Wawancara</a>
                @endif
                @if($enrollment->status === 'approved')
                    @if($hasCoupon)
                        <span class="badge bg-light text-dark border">Kupon: <span class="fw-semibold">{{ $enrollment->coupon_code }}</span></span>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="navigator.clipboard.writeText('{{ $enrollment->coupon_code }}')">Salin Kupon</button>
                        <a href="{{ $registrationUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-success">Buka SIAP Kerja</a>
                    @else
                        <span class="text-muted small">Kupon akan muncul setelah kelulusan diproses.</span>
                    @endif
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="alert alert-info">
        Belum ada data pendaftaran. Silakan daftar pelatihan melalui portal Satpel.
    </div>
@endforelse
@endsection
