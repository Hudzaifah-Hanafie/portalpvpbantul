@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $unit->exists ? 'Ubah' : 'Tambah' }} Unit Kompetensi</h4>
        <small class="text-muted">Lengkapi kode unit, elemen kompetensi, KUK, materi, dan JP.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-curriculum-unit.index' : 'admin.course-curriculum-unit.index')) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<form action="{{ $action }}" method="POST" class="bg-white rounded shadow-sm p-4" novalidate>
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div class="mb-3">
        <label class="form-label">Kurikulum</label>
        <select name="course_curriculum_id" class="form-select @error('course_curriculum_id') is-invalid @enderror" required>
            <option value="">Pilih kurikulum</option>
            @foreach($curricula as $id => $label)
                <option value="{{ $id }}" @selected(old('course_curriculum_id', $unit->course_curriculum_id) === $id)>{{ $label }}</option>
            @endforeach
        </select>
        @error('course_curriculum_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Kode Unit</label>
            <input type="text" name="unit_code" class="form-control @error('unit_code') is-invalid @enderror" value="{{ old('unit_code', $unit->unit_code) }}" placeholder="Contoh: TIK.JK01.001.01">
            @error('unit_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-8">
            <label class="form-label">Judul Unit</label>
            <input type="text" name="unit_title" class="form-control @error('unit_title') is-invalid @enderror" value="{{ old('unit_title', $unit->unit_title) }}" required>
            @error('unit_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mt-3">
        <label class="form-label">Elemen Kompetensi</label>
        <textarea name="elements" rows="3" class="form-control @error('elements') is-invalid @enderror">{{ old('elements', $unit->elements) }}</textarea>
        @error('elements') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mt-3">
        <label class="form-label">Kriteria Unjuk Kerja (KUK)</label>
        <textarea name="kuk" rows="3" class="form-control @error('kuk') is-invalid @enderror">{{ old('kuk', $unit->kuk) }}</textarea>
        @error('kuk') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mt-3">
        <label class="form-label">Materi Pelatihan</label>
        <textarea name="materials" rows="3" class="form-control @error('materials') is-invalid @enderror">{{ old('materials', $unit->materials) }}</textarea>
        @error('materials') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <label class="form-label">Metode</label>
            <select name="method" class="form-select @error('method') is-invalid @enderror">
                <option value="luring" @selected(old('method', $unit->method) === 'luring')>Luring</option>
                <option value="daring" @selected(old('method', $unit->method) === 'daring')>Daring</option>
                <option value="blended" @selected(old('method', $unit->method) === 'blended')>Blended</option>
            </select>
            @error('method') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">JP Teori</label>
            <input type="number" min="0" name="jp_theory" class="form-control @error('jp_theory') is-invalid @enderror" value="{{ old('jp_theory', $unit->jp_theory) }}">
            @error('jp_theory') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">JP Praktik</label>
            <input type="number" min="0" name="jp_practice" class="form-control @error('jp_practice') is-invalid @enderror" value="{{ old('jp_practice', $unit->jp_practice) }}">
            @error('jp_practice') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <label class="form-label">Urutan</label>
            <input type="number" min="0" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $unit->sort_order) }}">
            @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-8 d-flex align-items-center">
            <div class="form-check mt-3">
                <input type="checkbox" class="form-check-input" name="is_active" value="1" {{ old('is_active', $unit->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label">Aktif</label>
            </div>
        </div>
    </div>

    <div class="text-end mt-4">
        <button class="btn btn-primary px-4">{{ $unit->exists ? 'Perbarui' : 'Simpan' }}</button>
    </div>
</form>
@endsection
