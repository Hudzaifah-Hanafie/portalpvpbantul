@extends('layouts.admin')

@section('content')
<div class="card border-0 shadow-sm col-md-8 mx-auto">
    <div class="card-header bg-white">
        <h5 class="mb-0">Ubah Info Galeri</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.galeri.update', $galeri->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label class="form-label">Keterangan Foto</label>
                <input type="text" name="judul" class="form-control" value="{{ $galeri->judul }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Ganti Foto (Opsional)</label>
                <div class="mb-2">
                    <img src="{{ asset($galeri->gambar) }}" width="100" class="rounded">
                </div>
                <input type="file" name="gambar" class="form-control" accept="image/*">
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(\App\Models\Galeri::statuses() as $key => $label)
                        <option value="{{ $key }}" @selected($galeri->status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('admin.galeri.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
