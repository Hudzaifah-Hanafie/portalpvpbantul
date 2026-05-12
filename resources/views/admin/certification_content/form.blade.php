@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>{{ $item->exists ? 'Ubah' : 'Tambah' }} Konten Sertifikasi</h3>
    <a href="{{ route('admin.certification-content.index') }}" class="btn btn-secondary">Kembali</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Form belum valid.</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ $action }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Section</label>
                    <select name="section" class="form-select @error('section') is-invalid @enderror" required>
                        <option value="">Pilih Section</option>
                        @foreach($sections as $key => $label)
                            <option value="{{ $key }}" {{ old('section', $item->section ?? array_key_first($sections)) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('section') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Urutan</label>
                    <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror" value="{{ old('urutan', $item->urutan ?? 0) }}" min="0">
                    @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $item->title) }}" maxlength="255">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subjudul</label>
                    <input type="text" name="subtitle" class="form-control @error('subtitle') is-invalid @enderror" value="{{ old('subtitle', $item->subtitle) }}" maxlength="255">
                    @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Badge / Label</label>
                    <input type="text" name="badge" class="form-control @error('badge') is-invalid @enderror" value="{{ old('badge', $item->badge) }}" maxlength="100">
                    @error('badge') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Teks Tombol</label>
                    <input type="text" name="button_text" class="form-control @error('button_text') is-invalid @enderror" value="{{ old('button_text', $item->button_text) }}" maxlength="150">
                    @error('button_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tautan Tombol</label>
                    <input type="url" name="button_url" class="form-control @error('button_url') is-invalid @enderror" value="{{ old('button_url', $item->button_url) }}" maxlength="255">
                    @error('button_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Warna / Background (opsional)</label>
                    <input type="text" name="background" class="form-control @error('background') is-invalid @enderror" value="{{ old('background', $item->background) }}" placeholder="#d9f3f3">
                    @error('background') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $item->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Daftar Poin (pisahkan per baris)</label>
                    <textarea name="list_items" class="form-control @error('list_items') is-invalid @enderror" rows="4">{{ old('list_items', $item->list_items_text ?? '') }}</textarea>
                    @error('list_items') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gambar</label>
                    <input type="file" name="gambar" class="form-control @error('gambar') is-invalid @enderror" accept="image/jpeg,image/png">
                    <small class="text-muted">Format JPG/PNG maksimal 2MB.</small>
                    @error('gambar') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    @if($item->image_path)
                        <small class="text-muted">Gambar saat ini:</small>
                        <img src="{{ asset($item->image_path) }}" class="img-fluid rounded mt-2" style="max-height:120px;">
                    @endif
                </div>
                <div class="col-md-6 d-flex align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="aktif" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="aktif">Tandai sebagai aktif</label>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(\App\Models\CertificationContent::statuses() as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $item->status ?? null) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
