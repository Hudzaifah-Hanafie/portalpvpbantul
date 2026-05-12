@extends('layouts.admin')

@section('content')
<div class="card shadow-sm border-0 col-lg-10">
    <div class="card-header bg-white">
        <h5 class="mb-0">{{ $schedule->exists ? 'Ubah' : 'Tambah' }} Jadwal Pelatihan</h5>
    </div>
    <div class="card-body">
        <form action="{{ $action }}" method="POST">
            @csrf
            @if($method === 'PUT') @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Judul</label>
                    <input type="text" name="judul" class="form-control" value="{{ old('judul', $schedule->judul) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Bulan</label>
                    <input type="text" name="bulan" class="form-control" value="{{ old('bulan', $schedule->bulan) }}" placeholder="Januari">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Tahun</label>
                    <input type="text" name="tahun" class="form-control" value="{{ old('tahun', $schedule->tahun ?? date('Y')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Penyelenggara</label>
                    <input type="text" name="penyelenggara" class="form-control" value="{{ old('penyelenggara', $schedule->penyelenggara) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Lokasi</label>
                    <input type="text" name="lokasi" class="form-control" value="{{ old('lokasi', $schedule->lokasi) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Mulai</label>
                    <input type="date" name="mulai" class="form-control" value="{{ old('mulai', optional($schedule->mulai)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Selesai</label>
                    <input type="date" name="selesai" class="form-control" value="{{ old('selesai', optional($schedule->selesai)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Kuota</label>
                    <input type="text" name="kuota" class="form-control" value="{{ old('kuota', $schedule->kuota) }}" placeholder="mis. 20 peserta">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tautan Pendaftaran</label>
                    <input type="text" name="pendaftaran_link" class="form-control" value="{{ old('pendaftaran_link', $schedule->pendaftaran_link) }}" placeholder="https://...">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Catatan</label>
                    <textarea name="catatan" rows="3" class="form-control">{{ old('catatan', $schedule->catatan) }}</textarea>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" {{ old('is_active', $schedule->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">Aktif</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success mt-3">Simpan</button>
            <a href="{{ route('admin.training-schedule.index') }}" class="btn btn-secondary mt-3">Kembali</a>
        </form>
    </div>
</div>
@endsection
