@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>{{ $metric->exists ? 'Ubah' : 'Tambah' }} Metric</h3>
    <a href="{{ route('admin.infographic-metric.index') }}" class="btn btn-secondary">Kembali</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Validasi gagal.</strong> Periksa kembali kolom yang melebihi batas atau urutan bernilai negatif.
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
                            <option value="{{ $id }}" {{ old('infographic_year_id', $metric->infographic_year_id) == $id ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('infographic_year_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Label</label>
                    <input type="text" name="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label', $metric->label) }}" maxlength="255" required>
                    @error('label') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nilai</label>
                    <input type="text" name="value" class="form-control @error('value') is-invalid @enderror" value="{{ old('value', $metric->value) }}" maxlength="255" required>
                    @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Urutan</label>
                    <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror" value="{{ old('urutan', $metric->urutan ?? 0) }}" min="0">
                    <small class="text-muted">Kosongkan untuk otomatis menjadi 0.</small>
                    @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(\App\Models\InfographicMetric::statuses() as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $metric->status ?? null) === $key)>{{ $label }}</option>
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
