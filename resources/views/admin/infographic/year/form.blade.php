@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>{{ $year->exists ? 'Ubah' : 'Tambah' }} Tahun Infografis</h3>
    <a href="{{ route('admin.infographic-year.index') }}" class="btn btn-secondary">Kembali</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Validasi gagal.</strong> Pastikan tahun berupa angka, urutan tidak negatif, dan file sesuai ketentuan.
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ $action }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <input type="number" name="tahun" class="form-control @error('tahun') is-invalid @enderror" value="{{ old('tahun', $year->tahun) }}" required min="1900" max="2999">
                    @error('tahun') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Urutan</label>
                    <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror" value="{{ old('urutan', $year->urutan ?? 0) }}" min="0">
                    <small class="text-muted">Biarkan kosong untuk otomatis menjadi 0.</small>
                    @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="aktif" {{ old('is_active', $year->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="aktif">Tampilkan di halaman</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Judul Singkat</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $year->title) }}" maxlength="255">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Headline</label>
                    <input type="text" name="headline" class="form-control @error('headline') is-invalid @enderror" value="{{ old('headline', $year->headline) }}" maxlength="255">
                    @error('headline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $year->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Teks Tombol Hero</label>
                    <input type="text" name="hero_button_text" class="form-control @error('hero_button_text') is-invalid @enderror" value="{{ old('hero_button_text', $year->hero_button_text) }}" maxlength="100">
                    @error('hero_button_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tautan Tombol Hero</label>
                    <input type="text" name="hero_button_link" class="form-control @error('hero_button_link') is-invalid @enderror" value="{{ old('hero_button_link', $year->hero_button_link) }}" maxlength="255">
                    @error('hero_button_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gambar Hero</label>
                    <input type="file" name="hero_image" class="form-control @error('hero_image') is-invalid @enderror" accept=".jpg,.jpeg,.png">
                    <small class="text-muted d-block">Format JPG/PNG, maksimal 2 MB.</small>
                    @error('hero_image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    @if($year->hero_image)
                        <img src="{{ asset($year->hero_image) }}" class="img-fluid rounded mt-2" style="max-height:150px;">
                    @endif
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(\App\Models\InfographicYear::statuses() as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $year->status ?? null) === $key)>{{ $label }}</option>
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
