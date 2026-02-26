@extends('layouts.admin')

@section('content')
<div class="card shadow-sm border-0 col-lg-8">
    <div class="card-header bg-white">
        <h5 class="mb-0">{{ $benefit->exists ? 'Edit' : 'Tambah' }} Benefit</h5>
    </div>
    <div class="card-body">
        <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($method === 'PUT') @method('PUT') @endif
            <div class="mb-3">
                <label class="form-label fw-bold">Judul</label>
                <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $benefit->judul) }}" required>
                @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Deskripsi</label>
                <textarea name="deskripsi" rows="3" class="form-control @error('deskripsi') is-invalid @enderror">{{ old('deskripsi', $benefit->deskripsi) }}</textarea>
                @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Ikon (URL)</label>
                <input type="text" name="ikon" class="form-control @error('ikon') is-invalid @enderror" value="{{ old('ikon', $benefit->ikon) }}" placeholder="https://...">
                @error('ikon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="text-muted">Boleh dikosongkan jika mengunggah file di bawah.</small>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Ikon File (opsional)</label>
                @if($benefit->ikon && !str_starts_with($benefit->ikon, 'http'))
                    <div class="mb-2"><img src="{{ asset($benefit->ikon) }}" width="60" class="img-thumbnail"></div>
                @endif
                <input type="file" name="ikon_file" class="form-control @error('ikon_file') is-invalid @enderror" accept="image/*">
                @error('ikon_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="text-muted">PNG/JPG maks 2MB. Jika diisi, akan menimpa ikon URL.</small>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Urutan</label>
                    <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror" value="{{ old('urutan', $benefit->urutan ?? 0) }}" min="0">
                    @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 d-flex align-items-center">
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" {{ old('is_active', $benefit->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">Aktif</label>
                    </div>
                </div>
            </div>
            <div class="mb-3 mt-2">
                <label class="form-label fw-bold">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    @foreach(\App\Models\Benefit::statuses() as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $benefit->status ?? null) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-success mt-3">Simpan</button>
            <a href="{{ route('admin.benefit.index') }}" class="btn btn-secondary mt-3">Kembali</a>
        </form>
    </div>
</div>
@endsection
