@extends('layouts.admin')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Tambah Program Pelatihan Baru</h5>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Form belum valid.</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.program.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Nama Kejuruan/Program</label>
                <input 
                    type="text" 
                    name="judul" 
                    value="{{ old('judul') }}"
                    class="form-control @error('judul') is-invalid @enderror" 
                    placeholder="Contoh: Teknik Las Listrik" 
                    required
                >
                @error('judul')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Gambar Ilustrasi</label>
                <input 
                    type="file" 
                    name="gambar" 
                    class="form-control @error('gambar') is-invalid @enderror" 
                    accept="image/jpeg,image/png,image/jpg,image/gif" 
                    required
                >
                <small class="text-muted d-block">Format: JPG/PNG/GIF maks. 2MB. Disarankan rasio 4:3 (landscape).</small>
                @error('gambar')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Label Biaya</label>
                    <input
                        type="text"
                        name="biaya_label"
                        value="{{ old('biaya_label', 'Gratis') }}"
                        class="form-control @error('biaya_label') is-invalid @enderror"
                        placeholder="Gratis / Berbayar"
                    >
                    @error('biaya_label')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Label Sertifikat</label>
                    <input
                        type="text"
                        name="sertifikat_label"
                        value="{{ old('sertifikat_label', 'Sertifikat Mengikuti Pelatihan') }}"
                        class="form-control @error('sertifikat_label') is-invalid @enderror"
                        placeholder="Sertifikat Mengikuti Pelatihan"
                    >
                    @error('sertifikat_label')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Label Bahasa</label>
                    <input
                        type="text"
                        name="bahasa_label"
                        value="{{ old('bahasa_label', 'Bahasa Indonesia') }}"
                        class="form-control @error('bahasa_label') is-invalid @enderror"
                        placeholder="Bahasa Indonesia"
                    >
                    @error('bahasa_label')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi & Fasilitas</label>
                <textarea 
                    name="deskripsi" 
                    rows="6" 
                    class="form-control @error('deskripsi') is-invalid @enderror" 
                    placeholder="Jelaskan tentang kejuruan ini..." 
                    required
                >{{ old('deskripsi') }}</textarea>
                @error('deskripsi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Tautan Pendaftaran</label>
                <input
                    type="url"
                    name="pendaftaran_link"
                    value="{{ old('pendaftaran_link', 'https://skillhub.kemnaker.go.id/app/pelatihan') }}"
                    class="form-control @error('pendaftaran_link') is-invalid @enderror"
                    placeholder="https://siapkerja.kemnaker.go.id/app/pelatihan"
                >
                <small class="text-muted">Opsional. Jika dikosongkan, tombol daftar akan diarahkan ke Siap Kerja.</small>
                @error('pendaftaran_link')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Kode Unit Kompetensi</label>
                <textarea
                    name="kode_unit_kompetensi"
                    rows="4"
                    class="form-control @error('kode_unit_kompetensi') is-invalid @enderror"
                    placeholder="Contoh format: &#10;- UJK.PVP.01 Mengoperasikan mesin las&#10;- UJK.PVP.02 Membaca gambar teknik"
                >{{ old('kode_unit_kompetensi') }}</textarea>
                <small class="text-muted">Dapat berisi daftar kode/nomor unit kompetensi yang dicakup.</small>
                @error('kode_unit_kompetensi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Fasilitas & Keunggulan</label>
                <textarea
                    name="fasilitas_keunggulan"
                    rows="4"
                    class="form-control @error('fasilitas_keunggulan') is-invalid @enderror"
                    placeholder="Contoh: Workshop ber-AC, alat praktik industri, mentor bersertifikat, dll."
                >{{ old('fasilitas_keunggulan') }}</textarea>
                @error('fasilitas_keunggulan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Info Tambahan</label>
                <textarea
                    name="info_tambahan"
                    rows="4"
                    class="form-control @error('info_tambahan') is-invalid @enderror"
                    placeholder="Catatan penting, jadwal batch berikutnya, atau tautan registrasi khusus."
                >{{ old('info_tambahan') }}</textarea>
                @error('info_tambahan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(\App\Models\Program::statuses() as $key => $label)
                        <option value="{{ $key }}" @selected(old('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Program</button>
            <a href="{{ route('admin.program.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
