@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>{{ $category->exists ? 'Ubah' : 'Tambah' }} Kategori Publikasi</h3>
    <a href="{{ route('admin.publication-category.index') }}" class="btn btn-secondary">Kembali</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Form belum lengkap.</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ $action }}" method="POST" novalidate>
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required maxlength="255">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Slug (opsional)</label>
                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $category->slug) }}" maxlength="255">
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Layout</label>
                    <select name="layout" class="form-select @error('layout') is-invalid @enderror" required>
                        @php $layouts = ['cards' => 'Grid Kartu', 'infographic' => 'Infografis', 'list' => 'Daftar', 'downloads' => 'Unduh List', 'alumni' => 'Alumni']; @endphp
                        @foreach($layouts as $key => $label)
                            <option value="{{ $key }}" {{ old('layout', $category->layout) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('layout') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jumlah Kolom</label>
                    <input type="number" name="columns" class="form-control @error('columns') is-invalid @enderror" value="{{ old('columns', $category->columns ?? 4) }}" min="1" max="6">
                    @error('columns') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Urutan</label>
                    <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror" value="{{ old('urutan', $category->urutan ?? 0) }}" min="0">
                    @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Subjudul</label>
                    <input type="text" name="subtitle" class="form-control @error('subtitle') is-invalid @enderror" value="{{ old('subtitle', $category->subtitle) }}" maxlength="255">
                    @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror" maxlength="1000">{{ old('description', $category->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="aktif" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="aktif">Tampilkan kategori ini</label>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
