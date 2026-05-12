@extends('layouts.app')

@section('content')
<section class="py-5" style="background:#fff;">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <span class="badge bg-primary-subtle text-primary mb-2">{{ $announcement->created_at?->translatedFormat('d F Y') ?? '-' }}</span>
                        <h2 class="fw-bold">{{ $announcement->judul }}</h2>
                        <div class="text-muted mb-4">
                            Status: <span class="badge bg-success text-white">Terbit</span>
                            @if($announcement->approved_at)
                                <span class="ms-2">Disetujui {{ $announcement->approved_at->translatedFormat('d F Y H:i') }}</span>
                            @endif
                        </div>
                        <article class="content mb-4" style="line-height:1.7;">
                            {!! \App\Support\HtmlSanitizer::clean($announcement->isi) !!}
                        </article>
                        <div class="alert alert-info border-0 shadow-sm rounded-4 mt-4 d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Diskusikan pengumuman ini</strong>
                                <p class="mb-0 small text-muted">Bagikan pertanyaan atau insight alumni terkait layanan ini di forum kami.</p>
                            </div>
                            <a href="{{ route('alumni.forum.index') }}" class="btn btn-outline-primary btn-sm">Diskusi Alumni</a>
                        </div>
                        @if($announcement->file_download)
                            <a href="{{ asset($announcement->file_download) }}" class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-paperclip me-1"></i> Unduh Lampiran
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">Pengumuman Lainnya</div>
                    <div class="list-group list-group-flush">
                        @foreach($recent as $item)
                            <a href="{{ route('pengumuman.show', $item->slug) }}" class="list-group-item list-group-item-action">
                                <small class="text-muted">{{ $item->created_at?->translatedFormat('d M Y') ?? '-' }}</small>
                                <div class="fw-semibold">{{ Str::limit($item->judul, 70) }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('pengumuman.index') }}" class="btn btn-primary w-100 rounded-pill">Kembali ke Daftar</a>
            </div>
        </div>
    </div>
</section>
@endsection
