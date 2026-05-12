@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $module->exists ? 'Ubah Bab' : 'Tambah Bab' }}</h4>
        <small class="text-muted">Gunakan bab untuk mengelompokkan materi dan kuis.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-module.index' : 'admin.course-module.index')) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ $action }}" method="POST" class="row g-3">
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif
            <div class="col-md-6">
                <label class="form-label">Kelas</label>
                <select name="course_class_id" class="form-select @error('course_class_id') is-invalid @enderror" required>
                    <option value="">Pilih kelas</option>
                    @foreach($classes as $id => $title)
                        <option value="{{ $id }}" @selected(old('course_class_id', $module->course_class_id) === $id)>{{ $title }}</option>
                    @endforeach
                </select>
                @error('course_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Urutan</label>
                <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $module->sort_order ?? 0) }}" min="0">
                @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label class="form-label">Judul Bab</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $module->title) }}" required>
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $module->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" @checked(old('is_active', $module->is_active ?? true))>
                    <label class="form-check-label" for="isActive">Aktif</label>
                </div>
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
