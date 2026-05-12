@extends('layouts.app')

@section('content')
<section class="section-shell bg-white">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Berita</li>
                    </ol>
                </nav>

                <article class="feature-card p-4">
                    <h1 class="fw-bold mb-3">{{ $berita->judul }}</h1>
                    <div class="text-muted mb-4 d-flex align-items-center gap-3 flex-wrap">
                        <span class="meta-item"><i class="far fa-calendar-alt"></i>{{ $berita->created_at->format('d M Y') }}</span>
                        <span class="meta-item"><i class="far fa-user"></i>Admin PVP</span>
                    </div>

                    <img src="{{ $berita->gambar_utama }}" class="img-fluid rounded mb-4 w-100" alt="{{ $berita->judul }}">

                    <div class="content-body" style="line-height: 1.8; font-size: 1.05rem;">
                        {!! \App\Support\HtmlSanitizer::clean($berita->konten) !!}
                    </div>
                </article>

                <div class="alert alert-info border-0 shadow-sm rounded-4 mt-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <strong>Diskusikan topik ini</strong>
                        <p class="mb-0 small text-muted">Bagikan pandangan atau tanya jawab alumni terkait berita ini di forum.</p>
                    </div>
                    <a href="{{ route('alumni.forum.index') }}" class="btn btn-outline-primary pill-btn">Diskusikan di Forum Alumni</a>
                </div>
                
                <a href="{{ route('home') }}" class="btn btn-secondary mt-4 pill-btn"><i class="fas fa-arrow-left me-2"></i> Kembali</a>
            </div>

            <div class="col-lg-4">
                <div class="feature-card p-0 mb-4">
                    <div class="p-3 border-bottom fw-bold">
                        <i class="fas fa-newspaper text-primary me-2"></i> Berita Lainnya
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($beritaLain as $item)
                            <a href="{{ route('berita.show', $item->slug) }}" class="list-group-item list-group-item-action py-3">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $item->gambar_utama }}" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;" alt="{{ $item->judul }}">
                                    <div>
                                        <h6 class="mb-1" style="font-size: 0.9rem; font-weight: 600;">{{ Str::limit($item->judul, 40) }}</h6>
                                        <small class="text-muted" style="font-size: 0.75rem;">{{ $item->created_at->format('d M Y') }}</small>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="feature-card p-3">
                    <h5 class="fw-bold">Pendaftaran Pelatihan</h5>
                    <p class="text-muted small">Ingin mengikuti pelatihan di Satpel PVP Bantul? Cek jadwal batch dan daftar melalui portal Satpel (login dengan akun SIAP Kerja).</p>
                    <a href="{{ route('pelatihan.jadwal') }}" class="btn btn-primary w-100 pill-btn">Lihat Jadwal Pelatihan</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
