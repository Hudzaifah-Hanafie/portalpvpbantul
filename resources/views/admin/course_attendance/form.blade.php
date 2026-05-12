@extends('layouts.admin')

@php
    $statusOptions = \App\Models\CourseAttendance::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $attendance->exists ? 'Ubah' : 'Tambah' }} Presensi Peserta</h4>
        <small class="text-muted">Catat status kehadiran peserta per sesi.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-attendance.index' : 'admin.course-attendance.index')) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<form action="{{ $action }}" method="POST" class="bg-white rounded shadow-sm p-4" novalidate>
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Sesi</label>
            <select name="course_session_id" class="form-select @error('course_session_id') is-invalid @enderror" required>
                <option value="">Pilih Sesi</option>
                @foreach($sessions as $id => $title)
                    <option value="{{ $id }}" @selected(old('course_session_id', $attendance->course_session_id) === $id)>{{ $title }}</option>
                @endforeach
            </select>
            @error('course_session_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">ID Peserta</label>
            <input type="text" name="user_id" class="form-control @error('user_id') is-invalid @enderror" value="{{ old('user_id', $attendance->user_id) }}" required>
            <small class="text-muted">Masukkan UUID peserta.</small>
            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                @foreach($statusOptions as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $attendance->status ?? 'hadir') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Waktu Catat</label>
            <input type="datetime-local" name="checked_at" class="form-control @error('checked_at') is-invalid @enderror" value="{{ old('checked_at', optional($attendance->checked_at)->format('Y-m-d\TH:i')) }}">
            @error('checked_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Bukti (URL)</label>
            <input type="text" name="proof_url" class="form-control @error('proof_url') is-invalid @enderror" value="{{ old('proof_url', $attendance->proof_url) }}" maxlength="255">
            @error('proof_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mt-3">
        <label class="form-label">Alasan (opsional)</label>
        <textarea name="reason" rows="3" class="form-control @error('reason') is-invalid @enderror">{{ old('reason', $attendance->reason) }}</textarea>
        @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="text-end mt-4">
        <button class="btn btn-primary px-4">{{ $attendance->exists ? 'Perbarui' : 'Simpan' }}</button>
    </div>
</form>
@endsection
