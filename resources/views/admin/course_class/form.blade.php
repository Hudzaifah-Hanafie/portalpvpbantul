@extends('layouts.admin')

@php
    $statusOptions = \App\Models\CourseClass::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $course->exists ? 'Ubah' : 'Tambah' }} Kelas</h4>
        <small class="text-muted">Atur data kelas, prasyarat, kompetensi, dan status publikasi.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-class.index' : 'admin.course-class.index')) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<form action="{{ $action }}" method="POST" class="bg-white rounded shadow-sm p-4" novalidate>
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div class="mb-3">
        <label class="form-label">Judul</label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $course->title) }}" required maxlength="255">
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $course->description) }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Format</label>
            <select name="format" class="form-select @error('format') is-invalid @enderror">
                <option value="sinkron" @selected(old('format', $course->format) === 'sinkron')>Sinkron</option>
                <option value="asinkron" @selected(old('format', $course->format) === 'asinkron')>Asinkron</option>
                <option value="blended" @selected(old('format', $course->format) === 'blended')>Blended</option>
                <option value="luring" @selected(old('format', $course->format) === 'luring')>Luring</option>
            </select>
            @error('format') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Instruktur (opsional)</label>
            <input type="text" disabled class="form-control" value="{{ $course->instructor?->name ?? 'Setel di relasi user' }}">
            <small class="text-muted">Gunakan relasi user untuk menetapkan instruktur.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror">
                @foreach($statusOptions as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $course->status ?? 'draft') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <small class="text-muted">Draft/Pending untuk review; Reviewer/Admin dapat langsung publikasi.</small>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-6">
            <label class="form-label">Prasyarat (pisahkan baris)</label>
            <textarea name="prerequisites" rows="3" class="form-control @error('prerequisites') is-invalid @enderror" placeholder="Skill/alat yang wajib dikuasai">{{ old('prerequisites', $course->prerequisites ? implode(PHP_EOL, $course->prerequisites) : null) }}</textarea>
            @error('prerequisites') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Kompetensi/Unit (pisahkan baris)</label>
            <textarea name="competencies" rows="3" class="form-control @error('competencies') is-invalid @enderror" placeholder="Unit SKKNI/KKNI atau kompetensi target">{{ old('competencies', $course->competencies ? implode(PHP_EOL, $course->competencies) : null) }}</textarea>
            @error('competencies') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-4">
            <label class="form-label">Minimal Kehadiran (%)</label>
            <input type="number" min="0" max="100" name="min_attendance" class="form-control @error('min_attendance') is-invalid @enderror" value="{{ old('min_attendance', $course->min_attendance ?? 80) }}">
            @error('min_attendance') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Minimal Nilai (%)</label>
            <input type="number" min="0" max="100" name="min_score" class="form-control @error('min_score') is-invalid @enderror" value="{{ old('min_score', $course->min_score ?? 70) }}">
            @error('min_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4 d-flex flex-column gap-2">
            <div class="form-check mt-4">
                <input type="checkbox" class="form-check-input" name="require_final_project" value="1" {{ old('require_final_project', $course->require_final_project ?? true) ? 'checked' : '' }}>
                <label class="form-check-label">Wajib Proyek Akhir</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="require_final_exam" value="1" {{ old('require_final_exam', $course->require_final_exam ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">Wajib Ujian Final</label>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-12">
            <label class="form-label">Bobot Penilaian (Total 100%)</label>
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="number" min="0" max="100" name="weight_theory" class="form-control @error('weight_theory') is-invalid @enderror" value="{{ old('weight_theory', $course->weight_theory ?? 30) }}" placeholder="Teori (%)">
                    @error('weight_theory') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" max="100" name="weight_practice" class="form-control @error('weight_practice') is-invalid @enderror" value="{{ old('weight_practice', $course->weight_practice ?? 60) }}" placeholder="Praktik (%)">
                    @error('weight_practice') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <input type="number" min="0" max="100" name="weight_attitude" class="form-control @error('weight_attitude') is-invalid @enderror" value="{{ old('weight_attitude', $course->weight_attitude ?? 10) }}" placeholder="Sikap (%)">
                    @error('weight_attitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <small class="text-muted">Bobot mempengaruhi Nilai Akhir (NA) pada evaluasi pembelajaran.</small>
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-6">
            <label class="form-label">Badge (opsional)</label>
            <input type="text" name="badge" class="form-control @error('badge') is-invalid @enderror" value="{{ old('badge', $course->badge) }}" maxlength="255">
            @error('badge') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Tag Kelas (pisahkan dengan koma)</label>
            <input type="text" name="tags" class="form-control @error('tags') is-invalid @enderror" value="{{ old('tags', $course->tags ? implode(', ', $course->tags) : '') }}" placeholder="contoh: desain, otomotif, level dasar">
            <small class="text-muted">Tag membantu filter cepat di daftar kelas & pencarian global.</small>
            @error('tags') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-6 d-flex align-items-center">
            <div class="form-check mt-2">
                <input type="checkbox" class="form-check-input" name="is_active" value="1" {{ old('is_active', $course->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label">Aktif</label>
            </div>
        </div>
    </div>

    <div class="text-end mt-4">
        <button class="btn btn-primary px-4">{{ $course->exists ? 'Perbarui' : 'Simpan' }}</button>
    </div>
</form>
@endsection
