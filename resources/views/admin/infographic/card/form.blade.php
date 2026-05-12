@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>{{ $card->exists ? 'Ubah' : 'Tambah' }} Kartu</h3>
    <a href="{{ route('admin.infographic-card.index') }}" class="btn btn-secondary">Kembali</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Validasi gagal.</strong> Pastikan judul diisi, urutan tidak negatif, dan panjang teks sesuai batas.
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ $action }}" method="POST" novalidate>
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tahun</label>
                    <select name="infographic_year_id" class="form-select @error('infographic_year_id') is-invalid @enderror" required>
                        <option value="">Pilih Tahun</option>
                        @foreach($years as $id => $label)
                            <option value="{{ $id }}" {{ old('infographic_year_id', $card->infographic_year_id) == $id ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('infographic_year_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Judul Kartu</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $card->title) }}" maxlength="255" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Urutan</label>
                    <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror" value="{{ old('urutan', $card->urutan ?? 0) }}" min="0">
                    <small class="text-muted">Kosongkan untuk otomatis menjadi 0.</small>
                    @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Daftar Poin (pisahkan tiap baris)</label>
                    <textarea name="entries" rows="5" class="form-control @error('entries') is-invalid @enderror">{{ old('entries', isset($card->entries) ? implode("\n", (array) $card->entries) : '') }}</textarea>
                    @error('entries') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(\App\Models\InfographicCard::statuses() as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $card->status ?? null) === $key)>{{ $label }}</option>
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
