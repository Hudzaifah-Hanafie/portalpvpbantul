@extends('layouts.admin')

@section('content')
<div class="card shadow-sm border-0 col-lg-8">
    <div class="card-header bg-white">
        <h5 class="mb-0">{{ $instructor->exists ? 'Ubah' : 'Tambah' }} Instruktur</h5>
    </div>
    <div class="card-body">
        <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif

            <div class="mb-3">
                <label class="form-label fw-bold">Nama</label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama', $instructor->nama) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Keahlian / Jabatan</label>
                <input type="text" name="keahlian" class="form-control" value="{{ old('keahlian', $instructor->keahlian) }}">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Deskripsi Singkat</label>
                <textarea name="deskripsi" rows="4" class="form-control" placeholder="Jelaskan pengalaman/keahlian instruktur">{{ old('deskripsi', $instructor->deskripsi) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Foto</label>
                @if($instructor->foto)
                    <div class="mb-2"><img src="{{ asset($instructor->foto) }}" width="100" class="rounded-circle"></div>
                @endif
                <input type="file" name="foto" class="form-control" accept="image/*">
                <small class="text-muted">PNG/JPG, maks 2MB.</small>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">LinkedIn</label>
                    <input type="url" name="linkedin" class="form-control" placeholder="https://linkedin.com/in/..." value="{{ old('linkedin', $instructor->linkedin) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">WhatsApp</label>
                    <input type="text" name="whatsapp" class="form-control" placeholder="+62..." value="{{ old('whatsapp', $instructor->whatsapp) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $instructor->email) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Urutan</label>
                    <input type="number" name="urutan" class="form-control" value="{{ old('urutan', $instructor->urutan ?? 0) }}">
                </div>
            </div>

            <div class="form-check form-switch mt-3 mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active_instruktur" name="is_active" value="1" {{ old('is_active', $instructor->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active_instruktur">Tampilkan di halaman utama</label>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Status</label>
                <select name="status" class="form-select">
                    @foreach(\App\Models\Instructor::statuses() as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $instructor->status ?? null) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Simpan</button>
            <a href="{{ route('admin.instructor.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection
