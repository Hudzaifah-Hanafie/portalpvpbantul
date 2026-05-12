@extends('layouts.admin')

@php
    $statusOptions = \App\Models\CourseEnrollment::statuses();
    $adminStatuses = $adminStatuses ?? \App\Models\CourseEnrollment::adminStatuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $enrollment->exists ? 'Ubah' : 'Tambah' }} Pendaftaran</h4>
        <small class="text-muted">Daftarkan peserta ke kelas yang dipilih.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-enrollment.index' : 'admin.course-enrollment.index')) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<form action="{{ $action }}" method="POST" class="bg-white rounded shadow-sm p-4" novalidate>
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div class="mb-3">
        <label class="form-label">Kelas</label>
        <select name="course_class_id" class="form-select @error('course_class_id') is-invalid @enderror" required>
            <option value="">Pilih Kelas</option>
            @foreach($classes as $id => $title)
                <option value="{{ $id }}" @selected(old('course_class_id', $enrollment->course_class_id) == $id)>{{ $title }}</option>
            @endforeach
        </select>
        @error('course_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Peserta</label>
        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
            <option value="">Pilih Peserta</option>
            @foreach($users as $id => $name)
                <option value="{{ $id }}" @selected(old('user_id', $enrollment->user_id) == $id)>{{ $name }}</option>
            @endforeach
        </select>
        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            @foreach($statusOptions as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $enrollment->status ?? 'active') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Verifikasi Administrasi</label>
        <div class="row g-2">
            <div class="col-md-4">
                <select name="admin_status" class="form-select @error('admin_status') is-invalid @enderror">
                    @foreach($adminStatuses as $key => $label)
                        <option value="{{ $key }}" @selected(old('admin_status', $enrollment->admin_status ?? 'pending') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="form-text">Pilih VERIFIED untuk peserta yang lulus cek dokumen.</div>
                @error('admin_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-8">
                <textarea name="admin_note" rows="2" class="form-control @error('admin_note') is-invalid @enderror" placeholder="Catatan verifikasi (opsional)">{{ old('admin_note', $enrollment->admin_note) }}</textarea>
                <div class="form-text">Catatan akan tampil di dashboard admin sebagai alasan jika ditolak.</div>
                @error('admin_note') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Pembatasan Forum (hingga)</label>
        <input type="datetime-local" name="muted_until" value="{{ old('muted_until', $enrollment->muted_until ? $enrollment->muted_until->format('Y-m-d\\TH:i') : '') }}" class="form-control @error('muted_until') is-invalid @enderror">
        <div class="form-text">Opsional. Kosongkan jika tidak dibatasi. Gunakan untuk mute sementara peserta yang melanggar etika forum.</div>
        @error('muted_until') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="border-top pt-3 mt-4">
        <h6 class="mb-2">Evaluasi Pembelajaran</h6>
        <div class="small text-muted mb-3">Nilai dihitung otomatis dari tugas, quiz, presensi, dan rubrik. Input manual disembunyikan.</div>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Nilai Pre-Test</label>
                <input type="text" class="form-control" value="{{ $enrollment->pre_test_score !== null ? number_format($enrollment->pre_test_score, 2) : '-' }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nilai Post-Test</label>
                <input type="text" class="form-control" value="{{ $enrollment->post_test_score !== null ? number_format($enrollment->post_test_score, 2) : '-' }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nilai Praktik/Unjuk Kerja</label>
                <input type="text" class="form-control" value="{{ $enrollment->practice_score !== null ? number_format($enrollment->practice_score, 2) : '-' }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nilai Sikap/Etika</label>
                <input type="text" class="form-control" value="{{ $enrollment->attitude_score !== null ? number_format($enrollment->attitude_score, 2) : '-' }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Kehadiran Tercatat</label>
                <input type="text" class="form-control" value="{{ $enrollment->attendance_rate !== null ? $enrollment->attendance_rate.'%' : '-' }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nilai Akhir (NA)</label>
                <input type="text" class="form-control" value="{{ $enrollment->final_grade !== null ? number_format($enrollment->final_grade, 2) : '-' }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Predikat</label>
                <input type="text" class="form-control" value="{{ $enrollment->competency_status ? strtoupper($enrollment->competency_status) : '-' }}" disabled>
            </div>
        </div>
    </div>

    <div class="text-end mt-3">
        <button class="btn btn-primary px-4">{{ $enrollment->exists ? 'Perbarui' : 'Simpan' }}</button>
    </div>
</form>
@endsection
