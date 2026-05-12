@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $curriculum->exists ? 'Ubah' : 'Tambah' }} Kurikulum</h4>
        <small class="text-muted">Isi detail kurikulum, metode, dan total JP.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-curriculum.index' : 'admin.course-curriculum.index')) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

@php
    $courseSummary = $curriculum->course ?? ($selectedClass ?? null);
    $moduleCount = $courseSummary?->modules?->count() ?? 0;
    $materialCount = $courseSummary?->modules?->sum(fn ($module) => $module->materials->count()) ?? 0;
    $sessionCount = $courseSummary?->sessions?->count() ?? 0;
    $formatLabel = $courseSummary?->format ? ucfirst($courseSummary->format) : '-';
@endphp

@if($courseSummary)
    <div class="alert alert-info d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-semibold">Ringkasan Kelas</div>
            <small class="text-muted">
                Format: {{ $formatLabel }} • Bab: {{ $moduleCount }} • Materi: {{ $materialCount }} • Sesi: {{ $sessionCount }}
            </small>
        </div>
        <a href="{{ route(request()->routeIs('instructor.*') ? 'instructor.lms.course-module.index' : 'admin.course-module.index', ['class_id' => $courseSummary->id]) }}" class="btn btn-outline-primary btn-sm">
            Lihat Materi
        </a>
    </div>
@endif

<form action="{{ $action }}" method="POST" class="bg-white rounded shadow-sm p-4" novalidate>
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div class="mb-3">
        <label class="form-label">Kelas</label>
        <select name="course_class_id" class="form-select @error('course_class_id') is-invalid @enderror" required>
            <option value="">Pilih kelas</option>
            @foreach($classes as $id => $label)
                <option value="{{ $id }}" @selected(old('course_class_id', $curriculum->course_class_id) === $id)>{{ $label }}</option>
            @endforeach
        </select>
        @error('course_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Judul Kurikulum</label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $curriculum->title) }}" required>
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Referensi SKKNI</label>
            <input type="text" name="skkni_reference" class="form-control @error('skkni_reference') is-invalid @enderror" value="{{ old('skkni_reference', $curriculum->skkni_reference) }}" placeholder="Contoh: SKKNI TIK 2020">
            @error('skkni_reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Matriks Pelatihan</label>
            <input type="text" name="matrix_reference" class="form-control @error('matrix_reference') is-invalid @enderror" value="{{ old('matrix_reference', $curriculum->matrix_reference) }}" placeholder="Contoh: Matriks PVP 2024">
            @error('matrix_reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <label class="form-label">Metode</label>
            <select name="method" class="form-select @error('method') is-invalid @enderror">
                <option value="luring" @selected(old('method', $curriculum->method) === 'luring')>Luring</option>
                <option value="daring" @selected(old('method', $curriculum->method) === 'daring')>Daring</option>
                <option value="blended" @selected(old('method', $curriculum->method) === 'blended')>Blended</option>
            </select>
            @error('method') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Total JP Teori</label>
            <input type="number" min="0" name="total_jp_theory" class="form-control @error('total_jp_theory') is-invalid @enderror" value="{{ old('total_jp_theory', $curriculum->total_jp_theory) }}">
            @error('total_jp_theory') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Total JP Praktik</label>
            <input type="number" min="0" name="total_jp_practice" class="form-control @error('total_jp_practice') is-invalid @enderror" value="{{ old('total_jp_practice', $curriculum->total_jp_practice) }}">
            @error('total_jp_practice') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mt-3">
        <label class="form-label">Catatan Kurikulum</label>
        <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $curriculum->notes) }}</textarea>
        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="form-check mt-3">
        <input type="checkbox" class="form-check-input" name="is_active" value="1" {{ old('is_active', $curriculum->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label">Aktif</label>
    </div>

    <div class="text-end mt-4">
        <button class="btn btn-primary px-4">{{ $curriculum->exists ? 'Perbarui' : 'Simpan' }}</button>
    </div>
</form>
@endsection
