@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0">Detail Program</h3>
        <small class="text-muted">Data bersifat read-only (sumber Skillhub).</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('program.show', $program->id) }}" class="btn btn-outline-primary btn-sm" target="_blank">Lihat di Portal</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-8">
                <h5 class="fw-bold mb-2">{{ $program->judul }}</h5>
                <p class="text-muted mb-3">{{ $program->deskripsi }}</p>
                @if($program->info_tambahan)
                    <div class="mb-3">
                        <div class="fw-semibold small text-uppercase text-muted">Info Tambahan</div>
                        <div class="text-muted">{!! nl2br(e($program->info_tambahan)) !!}</div>
                    </div>
                @endif
                @if($program->kode_unit_kompetensi)
                    <div class="mb-3">
                        <div class="fw-semibold small text-uppercase text-muted">Unit Kompetensi</div>
                        <pre class="bg-light p-3 rounded small mb-0">{{ $program->kode_unit_kompetensi }}</pre>
                    </div>
                @endif
            </div>
            <div class="col-lg-4">
                <div class="border rounded p-3 bg-light">
                    <div class="fw-semibold small text-uppercase text-muted mb-2">Metadata</div>
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">External ID</dt>
                        <dd class="col-7 fw-semibold">{{ $program->external_id ?? '-' }}</dd>
                        <dt class="col-5 text-muted">Status</dt>
                        <dd class="col-7 fw-semibold text-capitalize">{{ $program->status ?? 'draft' }}</dd>
                        <dt class="col-5 text-muted">Tautan Daftar</dt>
                        <dd class="col-7">
                            @if($program->pendaftaran_link)
                                <a href="{{ $program->pendaftaran_link }}" target="_blank" class="text-decoration-none">{{ $program->pendaftaran_link }}</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </dd>
                        <dt class="col-5 text-muted">Diperbarui</dt>
                        <dd class="col-7">{{ optional($program->updated_at)->format('d M Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
