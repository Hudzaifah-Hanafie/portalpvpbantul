@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $material->exists ? 'Ubah Materi' : 'Tambah Materi' }}</h4>
        <small class="text-muted">Materi berada di bawah bab tertentu.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-material.index' : 'admin.course-material.index')) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ $action }}" method="POST" class="row g-3">
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif
            <div class="col-md-6">
                <label class="form-label">Bab</label>
                <select name="course_module_id" class="form-select @error('course_module_id') is-invalid @enderror" required>
                    <option value="">Pilih bab</option>
                    @foreach($modules as $id => $title)
                        <option value="{{ $id }}" @selected(old('course_module_id', $material->course_module_id) === $id)>{{ $title }}</option>
                    @endforeach
                </select>
                @error('course_module_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Urutan</label>
                <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $material->sort_order ?? 0) }}" min="0">
                @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label class="form-label">Judul Materi</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $material->title) }}" required>
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label class="form-label">Isi Materi</label>
                <textarea name="content" rows="5" class="form-control @error('content') is-invalid @enderror">{{ old('content', $material->content) }}</textarea>
                @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label class="form-label">Tautan (opsional)</label>
                <input type="url" name="link_url" class="form-control @error('link_url') is-invalid @enderror" value="{{ old('link_url', $material->link_url) }}" placeholder="https://...">
                @error('link_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" @checked(old('is_active', $material->is_active ?? true))>
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
