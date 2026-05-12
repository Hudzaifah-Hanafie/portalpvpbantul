@extends('layouts.admin')

@php
    $statusOptions = \App\Models\CourseSession::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $session->exists ? 'Ubah' : 'Tambah' }} Sesi</h4>
        <small class="text-muted">Atur jadwal, link live, dan rekaman; gunakan status untuk kontrol publikasi.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-session.index' : 'admin.course-session.index')) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<form action="{{ $action }}" method="POST" class="bg-white rounded shadow-sm p-4" novalidate>
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Kelas</label>
            <select name="course_class_id" class="form-select @error('course_class_id') is-invalid @enderror" required>
                <option value="">Pilih Kelas</option>
                @foreach($classes as $id => $title)
                    <option value="{{ $id }}" @selected(old('course_class_id', $session->course_class_id) === $id)>{{ $title }}</option>
                @endforeach
            </select>
            @error('course_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Judul</label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $session->title) }}" required maxlength="255">
            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mb-3 mt-3">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $session->description) }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Mulai</label>
            <input type="datetime-local" name="start_at" class="form-control @error('start_at') is-invalid @enderror" value="{{ old('start_at', optional($session->start_at)->format('Y-m-d\TH:i')) }}">
            @error('start_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Selesai</label>
            <input type="datetime-local" name="end_at" class="form-control @error('end_at') is-invalid @enderror" value="{{ old('end_at', optional($session->end_at)->format('Y-m-d\TH:i')) }}">
            @error('end_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror">
                @foreach($statusOptions as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $session->status ?? 'draft') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <small class="text-muted">Draft/Pending untuk menunggu review; Reviewer/Admin dapat langsung publikasi.</small>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-6">
            <label class="form-label">Tautan Live</label>
            <input type="text" name="meeting_link" class="form-control @error('meeting_link') is-invalid @enderror" value="{{ old('meeting_link', $session->meeting_link) }}" maxlength="255">
            @error('meeting_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-4">
            <label class="form-label">Kode Presensi (QR)</label>
            <input type="text" name="attendance_code" class="form-control @error('attendance_code') is-invalid @enderror" value="{{ old('attendance_code', $session->attendance_code) }}" maxlength="20" placeholder="Otomatis jika kosong">
            <div class="form-text">Scan QR peserta harus memuat kode ini.</div>
            @error('attendance_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Masa Berlaku Kode</label>
            <input type="datetime-local" name="attendance_code_expires_at" class="form-control @error('attendance_code_expires_at') is-invalid @enderror" value="{{ old('attendance_code_expires_at', optional($session->attendance_code_expires_at)->format('Y-m-d\\TH:i')) }}">
            <div class="form-text">Opsional; kosongkan untuk kode tetap.</div>
            @error('attendance_code_expires_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-3 d-flex align-items-center">
            <div class="form-check mt-4">
                <input type="checkbox" class="form-check-input" name="is_active" value="1" {{ old('is_active', $session->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label">Aktif</label>
            </div>
        </div>
    </div>

    <div class="text-end mt-4">
        <button class="btn btn-primary px-4">{{ $session->exists ? 'Perbarui' : 'Simpan' }}</button>
    </div>
</form>
@endsection
