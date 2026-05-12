@extends('layouts.admin')

@section('content')
<div class="card border-0 shadow-sm col-md-8 mx-auto">
    <div class="card-header bg-white">
        <h5 class="mb-0">Unggah Foto Galeri</h5>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Gagal mengunggah.</strong> Periksa kembali input berikut:
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.galeri.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Keterangan Foto (Caption)</label>
                <input
                    type="text"
                    name="judul"
                    class="form-control @error('judul') is-invalid @enderror"
                    value="{{ old('judul') }}"
                    placeholder="Contoh: Kegiatan Upacara Pembukaan"
                    required
                >
                @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">File Foto</label>
                <input
                    type="file"
                    name="gambar"
                    class="form-control @error('gambar') is-invalid @enderror"
                    accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif"
                    data-validate-image
                    required
                >
                @error('gambar') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                <small class="text-muted">Format: JPG/PNG/GIF, maksimal 2MB.</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                @php($statusOptions = \App\Models\Galeri::statuses())
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected(old('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-success">Unggah</button>
            <a href="{{ route('admin.galeri.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.querySelector('[data-validate-image]');
            if (!input) return;

            input.addEventListener('change', function () {
                const file = this.files?.[0];
                if (!file) return;

                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                const maxBytes = 2 * 1024 * 1024; // 2MB

                if (!allowedTypes.includes(file.type)) {
                    alert('Format gambar harus JPG, PNG, atau GIF.');
                    this.value = '';
                    return;
                }
                if (file.size > maxBytes) {
                    alert('Ukuran gambar melebihi 2MB.');
                    this.value = '';
                }
            });
        });
    </script>
@endpush
