@extends('layouts.admin')

@section('content')
<div class="card shadow-sm border-0 col-lg-8">
    <div class="card-header bg-white">
        <h5 class="mb-0">{{ $partner->exists ? 'Ubah' : 'Tambah' }} Partner</h5>
    </div>
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

            <div class="mb-3">
                <label class="form-label fw-bold">Nama Partner</label>
                <input 
                    type="text" 
                    name="nama" 
                    class="form-control @error('nama') is-invalid @enderror" 
                    value="{{ old('nama', $partner->nama) }}" 
                    maxlength="255"
                    required
                >
                @error('nama')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Logo</label>
                @if($partner->logo)
                    <div class="mb-2"><img src="{{ asset($partner->logo) }}" width="100" class="img-thumbnail"></div>
                @endif
                <input 
                    type="file" 
                    name="logo" 
                    class="form-control @error('logo') is-invalid @enderror" 
                    accept="image/jpeg,image/png"
                >
                <small class="text-muted d-block">Format JPG/PNG, ukuran maksimal 2MB.</small>
                @error('logo')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Tautan</label>
                <input 
                    type="url" 
                    name="tautan" 
                    class="form-control @error('tautan') is-invalid @enderror" 
                    placeholder="https://contoh.com" 
                    value="{{ old('tautan', $partner->tautan) }}"
                    maxlength="255"
                >
                @error('tautan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Urutan</label>
                <input 
                    type="number" 
                    name="urutan" 
                    class="form-control @error('urutan') is-invalid @enderror" 
                    value="{{ old('urutan', $partner->urutan ?? 0) }}"
                    min="0"
                >
                @error('urutan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $partner->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Tampilkan di halaman utama</label>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Status</label>
                <select name="status" class="form-select">
                    @foreach(\App\Models\Partner::statuses() as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $partner->status ?? null) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Simpan</button>
            <a href="{{ route('admin.partner.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection
