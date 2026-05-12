@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>{{ $vacancy->exists ? 'Ubah' : 'Tambah' }} Lowongan</h3>
    <a href="{{ route('admin.lowongan.index') }}" class="btn btn-secondary">Kembali</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Form belum lengkap.</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @php
            $jobTypes = ['Full Time', 'Part Time', 'Magang', 'Freelance', 'Kontrak'];
        @endphp
        <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Judul</label>
                    <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $vacancy->judul) }}" required maxlength="255">
                    @error('judul')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Perusahaan / Instansi</label>
                    <input type="text" name="perusahaan" class="form-control @error('perusahaan') is-invalid @enderror" value="{{ old('perusahaan', $vacancy->perusahaan) }}" maxlength="255">
                    @error('perusahaan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="lokasi" class="form-control @error('lokasi') is-invalid @enderror" value="{{ old('lokasi', $vacancy->lokasi) }}" maxlength="255">
                    @error('lokasi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipe Pekerjaan</label>
                    <select name="tipe_pekerjaan" class="form-select @error('tipe_pekerjaan') is-invalid @enderror">
                        <option value="">-- Pilih Tipe --</option>
                        @foreach($jobTypes as $type)
                            <option value="{{ $type }}" {{ old('tipe_pekerjaan', $vacancy->tipe_pekerjaan) === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('tipe_pekerjaan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Deadline</label>
                    <input type="date" name="deadline" class="form-control @error('deadline') is-invalid @enderror" value="{{ old('deadline', optional($vacancy->deadline)->format('Y-m-d')) }}">
                    @error('deadline')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi Singkat</label>
                    <textarea name="deskripsi" rows="4" class="form-control @error('deskripsi') is-invalid @enderror">{{ old('deskripsi', $vacancy->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Kualifikasi / Benefit</label>
                    <textarea name="kualifikasi" rows="4" class="form-control @error('kualifikasi') is-invalid @enderror" placeholder="Pisahkan poin dengan enter">{{ old('kualifikasi', $vacancy->kualifikasi) }}</textarea>
                    @error('kualifikasi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tautan Pendaftaran</label>
                    <input type="url" name="link_pendaftaran" class="form-control @error('link_pendaftaran') is-invalid @enderror" value="{{ old('link_pendaftaran', $vacancy->link_pendaftaran) }}" placeholder="https://">
                    @error('link_pendaftaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gambar / Banner</label>
                    <input type="file" name="gambar" class="form-control @error('gambar') is-invalid @enderror" accept="image/jpeg,image/png">
                    <small class="text-muted">Format JPG/PNG, maks. 2MB.</small>
                    @error('gambar')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @if($vacancy->gambar)
                        <img src="{{ asset($vacancy->gambar) }}" class="img-fluid rounded mt-2" style="max-height:120px;">
                    @endif
                </div>
                <div class="col-md-4">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="aktif" value="1" {{ old('is_active', $vacancy->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="aktif">Tampilkan</label>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(\App\Models\JobVacancy::statuses() as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $vacancy->status ?? null) === $key)>{{ $label }}</option>
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
