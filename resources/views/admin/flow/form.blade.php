@extends('layouts.admin')

@section('content')
<div class="card shadow-sm border-0 col-lg-8">
    <div class="card-header bg-white">
        <h5 class="mb-0">{{ $flow->exists ? 'Ubah' : 'Tambah' }} Langkah Alur</h5>
    </div>
    <div class="card-body">
        <form action="{{ $action }}" method="POST">
            @csrf
            @if($method === 'PUT') @method('PUT') @endif
            <div class="mb-3">
                <label class="form-label fw-bold">Deskripsi</label>
                <textarea name="deskripsi" rows="3" class="form-control" placeholder="Tuliskan detail langkah">{{ old('deskripsi', $flow->deskripsi) }}</textarea>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Urutan</label>
                    <input type="number" name="urutan" class="form-control" value="{{ old('urutan', $flow->urutan ?? 0) }}">
                </div>
                <div class="col-md-6 d-flex align-items-center">
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" {{ old('is_active', $flow->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">Aktif</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success mt-3">Simpan</button>
            <a href="{{ route('admin.flow.index') }}" class="btn btn-secondary mt-3">Kembali</a>
        </form>
    </div>
</div>
@endsection
