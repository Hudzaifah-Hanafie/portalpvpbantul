@extends('layouts.admin')

@php
    $statusOptions = \App\Models\CourseAnnouncement::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $announcement->exists ? 'Ubah' : 'Tambah' }} Pengumuman</h4>
        <small class="text-muted">Pengumuman ditampilkan kepada peserta kelas.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-announcement.index' : 'admin.course-announcement.index')) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<form action="{{ $action }}" method="POST" class="bg-white rounded shadow-sm p-4" novalidate>
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div class="mb-3">
        <label class="form-label">Kelas</label>
        <select name="course_class_id" class="form-select @error('course_class_id') is-invalid @enderror" required>
            <option value="">Pilih Kelas</option>
            @foreach($classes as $id => $title)
                <option value="{{ $id }}" @selected(old('course_class_id', $announcement->course_class_id) == $id)>{{ $title }}</option>
            @endforeach
        </select>
        @error('course_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Judul</label>
        <input type="text" name="title" value="{{ old('title', $announcement->title) }}" class="form-control @error('title') is-invalid @enderror" required>
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Isi</label>
        <textarea name="body" rows="6" class="form-control @error('body') is-invalid @enderror" placeholder="Isi pengumuman...">{{ old('body', $announcement->body) }}</textarea>
        @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror">
                @foreach($statusOptions as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $announcement->status ?? 'draft') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal Publikasi</label>
            <input type="datetime-local" name="published_at" value="{{ old('published_at', $announcement->published_at ? $announcement->published_at->format('Y-m-d\\TH:i') : '') }}" class="form-control @error('published_at') is-invalid @enderror">
            <div class="form-text">Opsional. Jika kosong & status published, akan otomatis diisi waktu saat ini.</div>
            @error('published_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="text-end mt-4">
        <button class="btn btn-primary px-4">{{ $announcement->exists ? 'Perbarui' : 'Simpan' }}</button>
    </div>
</form>
@endsection
