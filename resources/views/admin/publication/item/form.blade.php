@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>{{ $item->exists ? 'Ubah' : 'Tambah' }} Item Publikasi</h3>
    <a href="{{ route('admin.publication-item.index') }}" class="btn btn-secondary">Kembali</a>
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
                <div class="col-md-4">
                    <label class="form-label">Kategori</label>
                    <select name="publication_category_id" class="form-select @error('publication_category_id') is-invalid @enderror" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $id => $label)
                            <option value="{{ $id }}" {{ old('publication_category_id', $item->publication_category_id) == $id ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('publication_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $item->title) }}" required maxlength="255">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Subjudul</label>
                    <input type="text" name="subtitle" class="form-control @error('subtitle') is-invalid @enderror" value="{{ old('subtitle', $item->subtitle) }}" maxlength="255">
                    @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror" maxlength="2000">{{ old('description', $item->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Badge</label>
                    <input type="text" name="badge" class="form-control @error('badge') is-invalid @enderror" value="{{ old('badge', $item->badge) }}" maxlength="50">
                    @error('badge') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Teks Tombol</label>
                    <input type="text" name="button_text" class="form-control @error('button_text') is-invalid @enderror" value="{{ old('button_text', $item->button_text) }}" maxlength="100">
                    @error('button_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tautan Tombol</label>
                    <input type="url" name="button_link" class="form-control @error('button_link') is-invalid @enderror" value="{{ old('button_link', $item->button_link) }}" maxlength="255">
                    @error('button_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Urutan</label>
                    <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror" value="{{ old('urutan', $item->urutan ?? 0) }}" min="0">
                    @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gambar</label>
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png">
                    <small class="text-muted">Format JPG/PNG, maks 2MB.</small>
                    @error('image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    @if($item->image)
                        <img src="{{ asset($item->image) }}" class="img-fluid rounded mt-2" style="max-height:150px;">
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="form-label">Data Tambahan (pisahkan per baris)</label>
                    @php
                        $extraValue = old('extra', isset($item->extra) ? implode("
", (array) $item->extra) : '');
                    @endphp
                    <textarea name="extra" rows="4" class="form-control @error('extra') is-invalid @enderror">{{ $extraValue }}</textarea>
                    @error('extra') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="aktif" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="aktif">Tampilkan item</label>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(\App\Models\PublicationItem::statuses() as $key => $label)
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
