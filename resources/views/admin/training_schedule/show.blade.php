@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0">Detail Jadwal</h3>
        <small class="text-muted">Data bersifat read-only (sumber Skillhub).</small>
    </div>
    @if($schedule->pendaftaran_link)
        <a href="{{ $schedule->pendaftaran_link }}" target="_blank" class="btn btn-outline-primary btn-sm">Buka Halaman Pendaftaran</a>
    @endif
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-8">
                <h5 class="fw-bold mb-2">{{ $schedule->judul }}</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Penyelenggara</dt>
                    <dd class="col-sm-8">{{ $schedule->penyelenggara ?? '-' }}</dd>
                    <dt class="col-sm-4 text-muted">Lokasi</dt>
                    <dd class="col-sm-8">{{ $schedule->lokasi ?? '-' }}</dd>
                    <dt class="col-sm-4 text-muted">Mulai - Selesai</dt>
                    <dd class="col-sm-8">
                        {{ $schedule->mulai?->format('d M Y') ?? '-' }} -
                        {{ $schedule->selesai?->format('d M Y') ?? '-' }}
                    </dd>
                    <dt class="col-sm-4 text-muted">Kuota</dt>
                    <dd class="col-sm-8">{{ $schedule->kuota ?? '-' }}</dd>
                    <dt class="col-sm-4 text-muted">Status</dt>
                    <dd class="col-sm-8">
                        <span class="badge {{ $schedule->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $schedule->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </dd>
                </dl>
                @if($schedule->catatan)
                    <div class="mt-3">
                        <div class="fw-semibold small text-uppercase text-muted">Catatan</div>
                        <div class="text-muted">{!! nl2br(e($schedule->catatan)) !!}</div>
                    </div>
                @endif
            </div>
            <div class="col-lg-4">
                <div class="border rounded p-3 bg-light">
                    <div class="fw-semibold small text-uppercase text-muted mb-2">Metadata</div>
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">External ID</dt>
                        <dd class="col-7 fw-semibold">{{ $schedule->external_id ?? '-' }}</dd>
                        <dt class="col-5 text-muted">Batch ID</dt>
                        <dd class="col-7 fw-semibold">{{ $schedule->batch_id ?? '-' }}</dd>
                        <dt class="col-5 text-muted">Bulan/Tahun</dt>
                        <dd class="col-7 fw-semibold">{{ $schedule->bulan ?? '-' }} {{ $schedule->tahun }}</dd>
                        <dt class="col-5 text-muted">Tautan Daftar</dt>
                        <dd class="col-7">
                            @if($schedule->pendaftaran_link)
                                <a href="{{ $schedule->pendaftaran_link }}" target="_blank" class="text-decoration-none">{{ $schedule->pendaftaran_link }}</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </dd>
                        <dt class="col-5 text-muted">Diperbarui</dt>
                        <dd class="col-7">{{ optional($schedule->updated_at)->format('d M Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
