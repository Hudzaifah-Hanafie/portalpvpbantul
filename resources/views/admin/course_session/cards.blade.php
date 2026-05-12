@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Cetak Kartu Peserta</h4>
        <small class="text-muted">{{ $session->course->title ?? '-' }} • {{ $session->title }}</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-session.index' : 'admin.course-session.index')) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="row row-cols-2 row-cols-md-3 g-3">
    @foreach($enrollments as $enroll)
        <div class="col">
            <div class="card shadow-sm border-0 p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-bold">{{ $enroll->user->name ?? '-' }}</div>
                        <div class="small text-muted">{{ $session->course->title ?? '-' }}</div>
                    </div>
                    <div id="qr-{{ $enroll->id }}"></div>
                </div>
                <div class="small text-muted mt-2">Kode Presensi: {{ $session->attendance_code }}</div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
<script>
    @foreach($enrollments as $enroll)
        (function() {
            const el = document.getElementById('qr-{{ $enroll->id }}');
            const qr = new QRious({
                element: document.createElement('canvas'),
                size: 90,
                value: "{{ $session->attendance_code }}"
            });
            el.appendChild(qr.element);
        })();
    @endforeach
</script>
@endpush
