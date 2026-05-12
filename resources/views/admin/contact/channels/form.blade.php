@extends('layouts.admin')

@php
    $isEdit = $channel->exists;
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $isEdit ? 'Ubah' : 'Tambah' }} Saluran Kontak</h4>
        <small class="text-muted">Isi informasi sesuai dengan kartu yang ingin ditampilkan pada halaman Hubungi Kami.</small>
    </div>
    <a href="{{ route('admin.contact-channel.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Validasi gagal.</strong> Pastikan judul diisi dan nilai lain tidak melebihi batas.
    </div>
@endif

<form action="{{ $isEdit ? route('admin.contact-channel.update', $channel) : route('admin.contact-channel.store') }}" method="POST" class="bg-white rounded shadow-sm p-4" novalidate>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row g-4">
        <div class="col-md-6">
            <label class="form-label">Judul</label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $channel->title) }}" maxlength="255" required>
            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Subjudul</label>
            <input type="text" name="subtitle" class="form-control @error('subtitle') is-invalid @enderror" value="{{ old('subtitle', $channel->subtitle) }}" maxlength="255">
            @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Label / Konten</label>
            <input type="text" name="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label', $channel->label) }}" maxlength="255">
            @error('label') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Tautan (opsional)</label>
            <input type="text" name="link" class="form-control @error('link') is-invalid @enderror" value="{{ old('link', $channel->link) }}" placeholder="https://..." maxlength="255">
            @error('link') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Icon (Font Awesome)</label>
            <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror" value="{{ old('icon', $channel->icon) }}" placeholder="mis. fas fa-phone" maxlength="255">
            @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">Urutan</label>
            <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror" value="{{ old('urutan', $channel->urutan ?? 0) }}" min="0">
            @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active" {{ old('is_active', $channel->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="active">Aktif</label>
            </div>
        </div>
    </div>

    <div class="text-end mt-4">
        <button class="btn btn-primary px-4">{{ $isEdit ? 'Perbarui' : 'Simpan' }}</button>
    </div>
</form>
@endsection
