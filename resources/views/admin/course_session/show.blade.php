@extends('layouts.admin')

@php
    $statusOptions = \App\Models\CourseSession::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Detail Sesi</h4>
        <small class="text-muted">{{ $session->course->title ?? '-' }}</small>
    </div>
    <div class="d-flex gap-2">
        @if($session->attendance_code)
            <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-session.qr' : 'admin.course-session.qr'), $session->id) }}" class="btn btn-outline-primary btn-sm">QR Presensi</a>
            <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-session.cards' : 'admin.course-session.cards'), $session->id) }}" class="btn btn-outline-secondary btn-sm">Cetak Kartu Peserta</a>
        @endif
        <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-session.index' : 'admin.course-session.index')) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="fw-bold mb-1">{{ $session->title }}</h5>
                <div class="text-muted small">{{ $session->start_at?->format('d M Y H:i') }} s/d {{ $session->end_at?->format('d M Y H:i') }}</div>
                <div class="mt-2">
                    <span class="badge bg-secondary">{{ $statusOptions[$session->status] ?? $session->status }}</span>
                    <span class="badge {{ $session->is_active ? 'bg-success' : 'bg-dark' }}">{{ $session->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                @if($session->attendance_code)
                    <div class="mt-3">
                        <div class="small text-muted">Kode Presensi</div>
                        <div class="fw-bold">{{ $session->attendance_code }}</div>
                        @if($session->attendance_code_expires_at)
                            <div class="text-muted small">Berlaku hingga {{ $session->attendance_code_expires_at->format('d M Y H:i') }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-semibold mb-2">Catat Hadir (Operator)</h6>
                <form action="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-attendance.store' : 'admin.course-attendance.store')) }}" method="POST" class="row g-2">
                    @csrf
                    <input type="hidden" name="course_session_id" value="{{ $session->id }}">
                    <div class="col-md-6">
                        <input type="text" name="user_id" class="form-control form-control-sm @error('user_id') is-invalid @enderror" placeholder="ID peserta" required>
                        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select form-select-sm @error('status') is-invalid @enderror">
                            @foreach(\App\Models\CourseAttendance::statuses() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary btn-sm w-100">Catat</button>
                    </div>
                    <div class="col-12">
                        <input type="text" name="reason" class="form-control form-control-sm" placeholder="Catatan (opsional)">
                    </div>
                </form>
                <div class="text-muted small mt-2">Gunakan untuk peserta yang tidak bisa scan QR / tidak bawa gawai.</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">Presensi Peserta</h6>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Peserta</th>
                        <th>Status</th>
                        <th>Dicatat</th>
                        <th>Waktu</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $att)
                        <tr>
                            <td>{{ $att->user->name ?? $att->user_id }}</td>
                            <td><span class="badge bg-secondary">{{ \App\Models\CourseAttendance::statuses()[$att->status] ?? $att->status }}</span></td>
                            <td class="small text-muted">{{ $att->recorded_source === 'operator' ? 'Operator' : 'Peserta' }}</td>
                            <td class="small">{{ $att->checked_at?->format('d M Y H:i') }}</td>
                            <td class="small">{{ $att->reason }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Belum ada presensi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
