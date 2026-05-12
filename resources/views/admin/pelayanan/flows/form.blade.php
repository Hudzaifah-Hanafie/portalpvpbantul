@extends('layouts.admin')

@php
    $isEdit = $flow->exists;
    $statusOptions = \App\Models\PublicServiceFlow::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $isEdit ? 'Ubah Alur Pelayanan' : 'Tambah Alur Pelayanan' }}</h4>
        <small class="text-muted">Isi detail langkah, gambar, dan urutan untuk menyesuaikan dengan UI.</small>
    </div>
    <a href="{{ route('admin.public-service-flow.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Validasi gagal.</strong> Periksa kembali kolom yang bertanda merah atau file yang tidak sesuai ketentuan.
    </div>
@endif

<form action="{{ $isEdit ? route('admin.public-service-flow.update', $flow) : route('admin.public-service-flow.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded shadow-sm p-4" novalidate>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row g-4">
        <div class="col-md-4">
            <label class="form-label">Kategori</label>
            <input type="text" name="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category', $flow->category ?? 'pelayanan') }}" placeholder="mis. pelayanan, pengaduan">
            <small class="text-muted">Kosongkan untuk otomatis menggunakan kategori <em>pelayanan</em>.</small>
            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Urutan</label>
            <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror" value="{{ old('urutan', $flow->urutan ?? 0) }}" min="0">
            <small class="text-muted">Digunakan untuk menentukan urutan tampilan.</small>
            @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="is_active" value="1" id="status" {{ old('is_active', $flow->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="status">Aktif</label>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <label class="form-label">Judul</label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $flow->title) }}" required maxlength="255">
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mt-3">
        <label class="form-label">Subjudul / Penjelasan singkat</label>
        <textarea name="subtitle" rows="3" class="form-control @error('subtitle') is-invalid @enderror">{{ old('subtitle', $flow->subtitle) }}</textarea>
        @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mt-3">
        <label class="form-label">Langkah (pisahkan per baris)</label>
        <textarea name="steps" rows="7" class="form-control @error('steps') is-invalid @enderror" placeholder="1. Isi baris pertama&#10;2. Baris kedua">{{ old('steps', implode(PHP_EOL, ($flow->steps_list ?? []))) }}</textarea>
        @error('steps') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mt-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            @foreach($statusOptions as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $flow->status ?? 'draft') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <small class="text-muted">Pilih <em>Draft</em> atau <em>Pending</em> jika menunggu persetujuan. Reviewer dapat langsung memilih <em>Terpublikasi</em>.</small>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mt-3">
        <label class="form-label">Gambar</label>
        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept=".jpg,.jpeg,.png">
        <small class="text-muted">Format JPG/PNG dengan ukuran maksimal 2 MB.</small>
        @error('image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        @if($flow->image)
            <div class="mt-2">
                <img src="{{ asset($flow->image) }}" alt="Gambar Alur" class="img-fluid rounded shadow-sm" style="max-height:180px; object-fit:cover;">
                <p class="text-muted small mb-0">Saat ini: {{ $flow->image }}</p>
            </div>
        @endif
    </div>

    <div class="text-end mt-4">
        <button class="btn btn-primary px-4">{{ $isEdit ? 'Perbarui' : 'Simpan' }}</button>
    </div>
</form>
@endsection
