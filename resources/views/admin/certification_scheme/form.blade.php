@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>{{ $item->exists ? 'Ubah' : 'Tambah' }} Skema Sertifikasi</h3>
    <a href="{{ route('admin.certification-scheme.index') }}" class="btn btn-secondary">Kembali</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Validasi gagal.</strong> Periksa kembali data yang diisi.
            </div>
        @endif
        <form action="{{ $action }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                        <option value="">Pilih kategori</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ old('category', $item->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Urutan</label>
                    <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror" value="{{ old('urutan', $item->urutan ?? 0) }}" min="0">
                    @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $item->title) }}" maxlength="150" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subjudul</label>
                    <input type="text" name="subtitle" class="form-control @error('subtitle') is-invalid @enderror" value="{{ old('subtitle', $item->subtitle) }}" maxlength="200">
                    @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="2000">{{ old('description', $item->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Teks Tombol</label>
                    <input type="text" name="cta_text" class="form-control @error('cta_text') is-invalid @enderror" value="{{ old('cta_text', $item->cta_text) }}" maxlength="60">
                    @error('cta_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tautan Tombol</label>
                    <input type="url" name="cta_url" class="form-control @error('cta_url') is-invalid @enderror" value="{{ old('cta_url', $item->cta_url) }}" placeholder="https://...">
                    @error('cta_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gambar</label>
                    <input type="file" name="gambar" class="form-control @error('gambar') is-invalid @enderror" accept=".jpg,.jpeg,.png">
                    <small class="text-muted">Format JPG/PNG, maks 2 MB.</small>
                    @error('gambar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if($item->image_path)
                        <small class="text-muted d-block mt-1">Gambar saat ini:</small>
                        <img src="{{ asset($item->image_path) }}" class="img-fluid rounded" style="max-height:120px;">
                    @endif
                </div>
                <div class="col-md-6 d-flex align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="aktif" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="aktif">Aktifkan skema</label>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(\App\Models\CertificationScheme::statuses() as $key => $label)
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
