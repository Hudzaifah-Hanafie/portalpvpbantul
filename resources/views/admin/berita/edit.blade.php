@extends('layouts.admin')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <h5 class="mb-0">Ubah Berita</h5>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Gagal menyimpan.</strong> Silakan perbaiki kesalahan berikut:
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="alert alert-light border mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted">Status Saat Ini:</span>
                    @php
                        $statusLabels = \App\Models\Berita::statuses();
                        $statusColors = [
                            \App\Models\Berita::STATUS_DRAFT => 'secondary',
                            \App\Models\Berita::STATUS_PENDING => 'warning',
                            \App\Models\Berita::STATUS_PUBLISHED => 'success',
                        ];
                    @endphp
                    <span class="badge text-bg-{{ $statusColors[$berita->status] ?? 'secondary' }}">{{ $statusLabels[$berita->status] ?? ucfirst($berita->status) }}</span>
                </div>
                <div class="d-flex gap-2">
                    @if($berita->status === \App\Models\Berita::STATUS_DRAFT)
                        <form action="{{ route('admin.berita.submit', $berita) }}" method="POST" onsubmit="return confirm('Ajukan berita ini untuk persetujuan?')">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-sm btn-outline-primary">Ajukan Persetujuan</button>
                        </form>
                    @endif
                    @if($berita->status === \App\Models\Berita::STATUS_PENDING && auth()->user()->hasPermission('approve-content'))
                        <form action="{{ route('admin.berita.approve', $berita) }}" method="POST" onsubmit="return confirm('Setujui dan terbitkan berita ini?')">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-sm btn-success">Setujui & Terbitkan</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <form action="{{ route('admin.berita.update', $berita->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Judul Berita</label>
                    <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $berita->judul) }}" maxlength="150" required>
                    @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                        @foreach(\App\Models\Berita::categories() as $key => $label)
                            <option value="{{ $key }}" {{ old('kategori', $berita->kategori) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Penulis</label>
                    <input type="text" name="author" class="form-control @error('author') is-invalid @enderror" value="{{ old('author', $berita->author) }}" maxlength="100">
                    @error('author') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Publikasi</label>
                    <input type="datetime-local" name="published_at" class="form-control @error('published_at') is-invalid @enderror" value="{{ old('published_at', optional($berita->published_at)->format('Y-m-d\TH:i')) }}">
                    @error('published_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status Konten</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        @foreach(\App\Models\Berita::statuses() as $key => $label)
                            @if($key === \App\Models\Berita::STATUS_PUBLISHED && !auth()->user()->hasPermission('approve-content'))
                                @continue
                            @endif
                            <option value="{{ $key }}" {{ old('status', $berita->status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3 mt-3">
                <label class="form-label">Gambar Utama</label>
                <div class="mb-2">
                    <img src="{{ $berita->gambar_utama }}" width="150" class="rounded border">
                </div>
                <input type="file" name="gambar_utama" class="form-control @error('gambar_utama') is-invalid @enderror" accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-validate-image>
                @error('gambar_utama') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar (maks 2MB, JPG/PNG).</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Ringkasan Singkat</label>
                <textarea name="excerpt" rows="3" class="form-control @error('excerpt') is-invalid @enderror" placeholder="Opsional, akan otomatis dibuat jika dikosongkan.">{{ old('excerpt', $berita->excerpt) }}</textarea>
                @error('excerpt') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Konten Berita</label>
                <textarea name="konten" rows="10" class="form-control @error('konten') is-invalid @enderror" required>{{ old('konten', $berita->konten) }}</textarea>
                @error('konten') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            @include('admin.partials.seo-tools', ['model' => $berita, 'baseUrl' => url('/berita')])

            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui Berita</button>
            <a href="{{ route('admin.berita.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-validate-image]').forEach(input => {
                input.addEventListener('change', function () {
                    const file = this.files?.[0];
                    if (!file) return;

                    const allowedTypes = ['image/jpeg', 'image/png'];
                    const maxBytes = 2 * 1024 * 1024;

                    if (!allowedTypes.includes(file.type)) {
                        alert('Format gambar harus JPG atau PNG.');
                        this.value = '';
                        return;
                    }
                    if (file.size > maxBytes) {
                        alert('Ukuran gambar melebihi 2MB.');
                        this.value = '';
                    }
                });
            });
        });
    </script>
@endpush
