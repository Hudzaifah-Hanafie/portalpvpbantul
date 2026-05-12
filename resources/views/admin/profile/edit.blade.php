@extends('layouts.admin')

@section('content')
<div class="card shadow-sm border-0 col-md-10 mx-auto">
    <div class="card-header bg-white">
        <h5 class="mb-0">Ubah Halaman: {{ $profile->judul }}</h5>
    </div>
    <div class="card-body">
        @php
            $isMainProfile = $profile->key === 'profil_instansi';
            $isDenahProfile = $profile->key === 'profil_denah';
            $isVisiMisiProfile = $profile->key === 'visi_misi';
            $visiDecoded = ['visi' => '', 'misi' => ''];
            if ($isVisiMisiProfile) {
                $decoded = json_decode($profile->konten ?? '', true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $visiDecoded['visi'] = $decoded['visi'] ?? '';
                    $visiDecoded['misi'] = $decoded['misi'] ?? '';
                } else {
                    $visiDecoded['visi'] = $profile->konten ?? '';
                }
            }
        @endphp
        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Validasi gagal.</strong> Mohon periksa kembali data yang diisi.
            </div>
        @endif
        <form action="{{ route('admin.profile.update', $profile->id) }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label class="form-label fw-bold">Judul Halaman</label>
                <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $profile->judul) }}" required>
                @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            @unless($isDenahProfile)
                <div class="mb-3">
                    <label class="form-label fw-bold">Gambar / Bagan</label>
                    @if($profile->gambar)
                        <div class="mb-2">
                            <img src="{{ asset($profile->gambar) }}" width="200" class="img-thumbnail">
                        </div>
                    @endif
                    <input type="file" name="gambar" class="form-control @error('gambar') is-invalid @enderror" accept=".jpg,.jpeg,.png">
                    <small class="text-muted d-block">
                        Format JPG/PNG dengan ukuran maksimal 2 MB.
                        @if($isMainProfile)
                            Gambar ini digunakan sebagai hero banner utama "Profil Satpel PVP Bantul".
                        @else
                            Gambar ini hanya tampil pada halaman "{{ $profile->judul }}" tanpa mengubah hero utama.
                        @endif
                    </small>
                    @error('gambar') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            @endunless

            @if($isVisiMisiProfile)
                <div class="mb-3">
                    <label class="form-label fw-bold">Visi</label>
                    <textarea name="visi_text" rows="6" class="form-control @error('visi_text') is-invalid @enderror" placeholder="Tulis visi Satpel PVP Bantul di sini...">{{ old('visi_text', $visiDecoded['visi']) }}</textarea>
                    <small class="text-muted">Gunakan kalimat naratif atau daftar poin (HTML sederhana diperbolehkan).</small>
                    @error('visi_text') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Misi</label>
                    <textarea name="misi_text" rows="8" class="form-control @error('misi_text') is-invalid @enderror" placeholder="Tulis misi Satpel PVP Bantul di sini...">{{ old('misi_text', $visiDecoded['misi']) }}</textarea>
                    <small class="text-muted">Anda dapat menuliskan poin per paragraf atau menggunakan elemen &lt;ul&gt;.&lt;li&gt;.</small>
                    @error('misi_text') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            @else
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        {{ $isDenahProfile ? 'Embed Map (Google Maps/OpenStreetMap)' : 'Isi Konten' }}
                    </label>
                    <textarea name="konten" rows="{{ $isDenahProfile ? 6 : 15 }}" class="form-control @error('konten') is-invalid @enderror" placeholder="{{ $isDenahProfile ? 'Tempelkan kode iframe atau tautan share dari Google Maps/OpenStreetMap...' : 'Tulis isi halaman di sini...' }}">{{ old('konten', $profile->konten) }}</textarea>
                    <small class="text-muted">
                        @if($isDenahProfile)
                            Salin URL berbagi (https://maps.google.com/... atau https://www.openstreetmap.org/...) atau kode &lt;iframe&gt; untuk ditampilkan sebagai peta interaktif.
                        @else
                            Anda bisa menggunakan tag HTML sederhana seperti &lt;p&gt;, &lt;b&gt;, &lt;ul&gt;.
                        @endif
                    </small>
                    @error('konten') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            @endif

            <button type="submit" class="btn btn-success"><i class="fas fa-save me-2"></i> Simpan Perubahan</button>
            <a href="{{ route('admin.profile.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection
