@extends('layouts.admin')

@section('content')
<div class="card shadow-sm border-0 col-lg-8">
    <div class="card-header bg-white">
        <h5 class="mb-0">{{ $struktur->exists ? 'Ubah' : 'Tambah' }} Struktur</h5>
    </div>
    <div class="card-body">
        <form action="{{ $action }}" method="POST">
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif

            <div class="mb-3">
                <label class="form-label fw-bold">Nama Pegawai/Bagian</label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama', $struktur->nama) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Jabatan</label>
                <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', $struktur->jabatan) }}">
                <small class="text-muted">Opsional, misal: Kepala, Sekretaris, dll.</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Parent</label>
                <select name="parent_id" class="form-select">
                    <option value="">Tanpa parent (root)</option>
                    @foreach($nodes as $node)
                        <option value="{{ $node->id }}" @selected(old('parent_id', $struktur->parent_id) == $node->id)>
                            {{ $node->nama }} @if($node->jabatan) - {{ $node->jabatan }} @endif
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Pilih parent untuk membuat hierarki.</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Urutan</label>
                <input type="number" name="urutan" class="form-control" value="{{ old('urutan', $struktur->urutan ?? 0) }}">
                <small class="text-muted">Angka kecil muncul lebih atas di level yang sama.</small>
            </div>

            <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Simpan</button>
            <a href="{{ route('admin.struktur.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection
