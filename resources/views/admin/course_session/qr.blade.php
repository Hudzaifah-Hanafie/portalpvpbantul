@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">QR Presensi</h4>
        <small class="text-muted">{{ $session->course->title ?? '-' }} • {{ $session->title }}</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-session.index' : 'admin.course-session.index')) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body text-center">
        <div id="qr-container" class="mb-3"></div>
        <h5 class="fw-bold">{{ $session->attendance_code }}</h5>
        <div class="text-muted small mb-3">Scan QR ini untuk isi presensi. Cetak dan tempel di kelas untuk peserta yang tidak familiar gawai.</div>
        <button class="btn btn-primary btn-sm" onclick="window.print()">Cetak</button>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
<script>
    const qr = new QRious({
        element: document.createElement('canvas'),
        size: 220,
        value: "{{ $session->attendance_code }}"
    });
    document.getElementById('qr-container').appendChild(qr.element);
</script>
@endpush
