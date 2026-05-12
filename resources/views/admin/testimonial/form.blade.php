@extends('layouts.admin')

@php
    $statusOptions = \App\Models\Testimonial::statuses();
@endphp

@section('content')
<div class="card shadow-sm border-0 col-lg-8">
    <div class="card-header bg-white">
        <h5 class="mb-0">{{ $testimonial->exists ? 'Ubah' : 'Tambah' }} Testimoni</h5>
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

        <form action="{{ $action }}" method="POST" novalidate>
            @csrf
            @if($method === 'PUT') @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nama</label>
                    <input 
                        type="text" 
                        name="nama" 
                        class="form-control @error('nama') is-invalid @enderror" 
                        value="{{ old('nama', $testimonial->nama) }}" 
                        maxlength="255"
                        required
                    >
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Jabatan / Status</label>
                    <input 
                        type="text" 
                        name="jabatan" 
                        class="form-control @error('jabatan') is-invalid @enderror" 
                        value="{{ old('jabatan', $testimonial->jabatan) }}"
                        maxlength="255"
                    >
                    @error('jabatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="mb-3 mt-3">
                <label class="form-label fw-bold">Pesan</label>
                <textarea 
                    name="pesan" 
                    rows="4" 
                    class="form-control @error('pesan') is-invalid @enderror" 
                    maxlength="2000"
                >{{ old('pesan', $testimonial->pesan) }}</textarea>
                @error('pesan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Video YouTube URL (opsional)</label>
                <input 
                    type="url" 
                    name="video_url" 
                    class="form-control @error('video_url') is-invalid @enderror" 
                    placeholder="https://www.youtube.com/watch?v=..."
                    value="{{ old('video_url', $testimonial->video_url) }}"
                >
                @error('video_url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Urutan</label>
                    <input 
                        type="number" 
                        name="urutan" 
                        class="form-control @error('urutan') is-invalid @enderror" 
                        value="{{ old('urutan', $testimonial->urutan ?? 0) }}"
                        min="0"
                    >
                    @error('urutan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        @foreach($statusOptions as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $testimonial->status ?? 'draft') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Draft/Pending untuk menunggu review; Reviewer/Admin dapat langsung publikasikan.</small>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="mt-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" {{ old('is_active', $testimonial->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label">Aktif</label>
                </div>
            </div>
            <button type="submit" class="btn btn-success mt-3">Simpan</button>
            <a href="{{ route('admin.testimonial.index') }}" class="btn btn-secondary mt-3">Kembali</a>
        </form>
    </div>
</div>
@endsection
