@extends('layouts.app')

@section('content')
@php
    $heroTitle = $settings['home_hero_title'] ?? 'Tingkatkan Potensi Dirimu, Siap Sukses Bersama PVP Bantul';
    $heroSubtitle = $settings['home_hero_subtitle'] ?? 'Program pelatihan vokasi dan pemberdayaan yang selangkah lebih maju, dipandu instruktur bersertifikat dan mitra industri.';
    $heroImage = $settings['home_hero_image'] ?? 'https://placehold.co/620x420?text=Hero+Image';
    $heroCta1Text = $settings['home_hero_cta_primary_text'] ?? 'Daftar di Portal';
    $heroCta1Link = $settings['home_hero_cta_primary_link'] ?? route('pelatihan.katalog');
    $heroCta2Text = $settings['home_hero_cta_secondary_text'] ?? 'Baca Selengkapnya';
    $heroCta2Link = $settings['home_hero_cta_secondary_link'] ?? route('program');
    $heroBg = $settings['home_hero_bg'] ?? $heroImage;
    $benefitHeroTitle = $settings['home_benefit_title'] ?? 'Kenapa Harus Ikut Pelatihan di Satpel PVP Bantul?';
    $benefitHeroImage = $settings['home_benefit_image'] ?? 'https://placehold.co/380x420?text=Instruktur';
    $programSectionTitle = $settings['home_program_title'] ?? 'Jelajahi Pelatihan';
    $programSectionSubtitle = $settings['home_program_subtitle'] ?? 'Mulai perjalanan kariermu dengan pelatihan yang sesuai kebutuhan.';
    $whySectionTitle = $settings['home_why_title'] ?? 'Kenapa Harus Ikut Pelatihan di Satpel PVP Bantul?';
    $whySectionImage = $settings['home_why_image'] ?? 'https://placehold.co/420x440?text=Instruktur';
    $flowTitle = $settings['home_flow_title'] ?? 'Alur Pelatihan di Satpel PVP Bantul';
    $flowImage = $settings['home_flow_image'] ?? 'https://placehold.co/380x420?text=CTA';
    $flowSubtitle = $settings['home_flow_subtitle'] ?? 'Bagi kamu yang ingin mengikuti pelatihan, silakan cek alur berikut ini sebagai panduan.';
    $newsTitle = $settings['home_news_title'] ?? 'Berita Terkini';
    $newsSubtitle = $settings['home_news_subtitle'] ?? 'Informasi terbaru seputar kegiatan Satpel PVP Bantul.';
    $testimonialTitle = $settings['home_testimonial_title'] ?? 'Kata Mereka Setelah Selesai Pelatihan';
    $testimonialSubtitle = $settings['home_testimonial_subtitle'] ?? 'Testimoni peserta tentang pengalaman belajar di Satpel PVP Bantul.';
    $partnerTitle = $settings['home_partner_title'] ?? 'Partner Kami';
    $partnerSubtitle = $settings['home_partner_subtitle'] ?? 'Kolaborasi dengan para pelaku industri yang membantu mewujudkan sukses bersama.';
    $instructorTitle = $settings['home_instructor_title'] ?? 'Instruktur Ahli dalam Bidangnya';
    $instructorSubtitle = $settings['home_instructor_subtitle'] ?? 'Berpengalaman sebagai praktisi industri dan instruktur Satpel PVP Bantul.';
    $galleryTitle = $settings['home_gallery_title'] ?? 'Galeri Kegiatan';
    $gallerySubtitle = $settings['home_gallery_subtitle'] ?? 'Dokumentasi aktivitas pelatihan di Satpel PVP Bantul.';
@endphp

@push('styles')
<style>
    .home-hero.with-image {
        background-image:
            linear-gradient(135deg, rgba(11, 77, 120, 0.86), rgba(17, 122, 159, 0.82), rgba(25, 182, 215, 0.78)),
            url("{{ $heroBg }}");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    .home-hero.with-image::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(8, 29, 46, 0.12);
        pointer-events: none;
    }
    /* Pastikan konten utama lebar normal di desktop */
    .home-hero .container,
    .announcement-hero .container,
    .benefit-modern .container,
    .section-shell > .container {
        max-width: 1200px;
    }
</style>
@endpush

<section class="home-hero with-image">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6 position-relative">
                <h1 class="fw-bold lh-sm">{{ $heroTitle }}</h1>
                <p class="mt-3 text-white-50">{{ $heroSubtitle }}</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ $heroCta1Link }}" target="_blank" class="btn btn-cta">{{ $heroCta1Text }}</a>
                    <a href="{{ $heroCta2Link }}" class="btn btn-cta-ghost">{{ $heroCta2Text }}</a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="{{ $heroImage }}" class="img-fluid rounded-4 shadow-soft" alt="Hero banner">
            </div>
        </div>
    </div>
</section>

@if($latestAnnouncements->isNotEmpty())
@php $mainAnn = $latestAnnouncements->first(); $otherAnn = $latestAnnouncements->skip(1); @endphp
@push('styles')
<style>
    .announcement-hero {
        position: relative;
        background: linear-gradient(135deg, #0b4d78, #0f6b7a);
    }
</style>
@endpush
<section class="py-4 announcement-hero">
    <div class="container">
        <div class="p-4 p-lg-5 rounded-4 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(255,255,255,.12), rgba(255,255,255,.05)); border:1px solid rgba(255,255,255,.35); box-shadow:0 22px 60px -45px rgba(0,0,0,.8);">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="bg-white text-primary rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width:44px; height:44px;"><i class="fas fa-bullhorn"></i></span>
                    <div>
                        <div class="fw-bold text-uppercase small mb-0">Pengumuman</div>
                        <small class="text-white-75">Update Satpel PVP Bantul</small>
                    </div>
                </div>
                <a href="{{ route('pengumuman.index') }}" class="btn btn-light text-primary fw-semibold rounded-pill px-4">Lihat Semua</a>
            </div>
            <div class="d-flex flex-column flex-lg-row align-items-start gap-4">
                <div class="flex-grow-1">
                    <p class="text-white fw-semibold mb-1" style="letter-spacing:0.3px;">{{ optional($mainAnn->published_at ?? $mainAnn->created_at)->translatedFormat('d M Y') }}</p>
                    <h4 class="fw-bold mb-2 text-white" style="text-shadow:0 2px 6px rgba(0,0,0,.25);">{{ $mainAnn->judul }}</h4>
                    <p class="text-white mb-0" style="text-shadow:0 1px 3px rgba(0,0,0,.25);">{{ Str::limit(strip_tags($mainAnn->isi), 180) }}</p>
                </div>
                @if($otherAnn->isNotEmpty())
                <div class="bg-white text-primary rounded-3 p-3 shadow-sm" style="min-width:260px; max-width:320px;">
                    <div class="fw-semibold mb-2">Pengumuman Lainnya</div>
                    <div class="d-flex flex-column gap-2">
                        @foreach($otherAnn->take(3) as $item)
                            <div class="p-2 rounded-2 bg-light border border-light">
                                <small class="text-muted d-block">{{ optional($item->published_at ?? $item->created_at)->translatedFormat('d M Y') }}</small>
                                <a href="{{ route('pengumuman.index') }}" class="text-primary text-decoration-none fw-semibold">{{ Str::limit($item->judul, 60) }}</a>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

@endif

<section class="section-shell bg-white">
    <div class="container">
        <div class="text-center section-header">
            <h3 class="fw-bold">Layanan Pelatihan di Satpel PVP Bantul</h3>
            <p class="text-muted">Pilihan skema pelatihan dengan fasilitas berbeda untuk kebutuhan vokasi dan industri.</p>
        </div>
        <div class="row g-3">
            @forelse($trainingServices as $service)
            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100">
                    @if($service->gambar)
                        <img src="{{ asset($service->gambar) }}" class="card-img-top" alt="{{ $service->judul }}" style="object-fit:cover; height:200px;">
                    @else
                        <img src="https://placehold.co/520x200?text={{ urlencode($service->judul) }}" class="card-img-top" alt="{{ $service->judul }}" style="object-fit:cover; height:200px;">
                    @endif
                    @php
                        $cleanDesc = strip_tags($service->deskripsi, '<p><br><strong><em><b><i><u>');
                        $rawFasilitas = $service->fasilitas ?? '';
                        $hasList = \Illuminate\Support\Str::contains($rawFasilitas, ['<ul', '<ol', '<li>']);
                        $fasilitasHtml = $rawFasilitas;
                        if (! $hasList) {
                            $items = collect(preg_split('/\r\n|\r|\n/', trim(strip_tags($rawFasilitas))))
                                ->filter()
                                ->values();
                            if ($items->isNotEmpty()) {
                                $fasilitasHtml = '<ul class="mb-0">' . $items->map(fn($i) => '<li>' . e($i) . '</li>')->implode('') . '</ul>';
                            } else {
                                $fasilitasHtml = nl2br(e(strip_tags($rawFasilitas)));
                            }
                        }
                    @endphp
                    <div class="p-3">
                        <h5 class="fw-bold">{{ $service->judul }}</h5>
                        <div class="text-muted small mb-2">{!! $cleanDesc ?: 'Belum ada deskripsi.' !!}</div>
                        <div class="small text-muted">{!! $fasilitasHtml ?: 'Belum ada fasilitas.' !!}</div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center text-muted empty-state">Belum ada data layanan pelatihan.</div>
            @endforelse
        </div>
    </div>
</section>


<section class="section-shell bg-light">
    <div class="container">
        <div class="text-center section-header">
            <h3 class="fw-bold">{{ $programSectionTitle }}</h3>
            <p class="text-muted">{{ $programSectionSubtitle }}</p>
        </div>
        <div class="row g-3">
            @foreach($programs as $program)
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="feature-card h-100">
                    <img src="{{ $program->gambar ? asset($program->gambar) : 'https://placehold.co/400x240?text=Program' }}" class="card-img-top" style="height:180px; object-fit:cover;" alt="{{ $program->judul }}">
                    <div class="p-3">
                        <h6 class="fw-bold">{{ $program->judul }}</h6>
                        <p class="text-muted small">{{ Str::limit($program->deskripsi ?? '', 80) }}</p>
                        <a href="{{ route('program.show', $program->id) }}" class="btn btn-sm btn-primary pill-btn">Lihat Detail</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-3">
            <a href="{{ route('program') }}" class="btn btn-outline-primary pill-btn px-4">Lihat semua</a>
        </div>
    </div>
</section>

@push('styles')
<style>
    .benefit-modern {
        background: #0f6070;
        color: #f8fbff;
    }
    .benefit-hero-card {
        background: #e9f5f9;
        border-radius: 28px;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 30px 70px -40px rgba(0,0,0,0.35);
        min-height: 380px;
        display: grid;
        place-items: center;
    }
    .benefit-hero-card img {
        border-radius: 18px;
        object-fit: cover;
        width: 100%;
        max-height: 420px;
    }
    .benefit-hero-badge {
        position: absolute;
        bottom: 1rem;
        left: 50%;
        transform: translateX(-50%);
        background: transparent;
        color: #0f172a;
        padding: 0;
        border-radius: 14px;
        box-shadow: none;
        font-weight: 700;
        min-width: 0;
        text-align: center;
        display: none;
    }
    .benefit-list .benefit-item {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 18px;
        padding: 1rem 1.25rem;
        backdrop-filter: blur(2px);
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    .benefit-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        background: #0b4d78;
        display: grid;
        place-items: center;
        overflow: hidden;
        flex-shrink: 0;
    }
    .benefit-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .benefit-title {
        color: #f8fbff;
        margin-bottom: 0.2rem;
        font-size: 1.05rem;
    }
    .benefit-desc {
        color: rgba(248, 251, 255, 0.85);
        margin: 0;
        line-height: 1.45;
    }
    @media (max-width: 991.98px) {
        .benefit-list .benefit-item {
            align-items: center;
        }
    }
</style>
@endpush
<section class="py-5 benefit-modern">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-5 order-lg-1 order-2">
                <div class="benefit-hero-card">
                    <img src="{{ $benefitHeroImage }}" alt="Ilustrasi benefit">
                </div>
            </div>
            <div class="col-lg-7 order-lg-2 order-1">
                <h3 class="fw-bold mb-3 text-white">{{ $benefitHeroTitle }}</h3>
                <div class="benefit-list vstack gap-3">
                    @forelse($benefits as $benefit)
                        <div class="benefit-item d-flex gap-3">
                            <div class="benefit-icon">
                                @if($benefit->ikon)
                                    <img src="{{ asset($benefit->ikon) }}" alt="{{ $benefit->judul }}">
                                @else
                                    <span class="text-white-75 fw-bold">{{ Str::substr($benefit->judul, 0, 1) }}</span>
                                @endif
                            </div>
                            <div>
                                <h6 class="benefit-title fw-bold">{{ $benefit->judul }}</h6>
                                <p class="benefit-desc small">{{ $benefit->deskripsi }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-white">Belum ada data benefit.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    .flow-section {
        background: linear-gradient(180deg, #f7fbfd 0%, #ffffff 45%, #f7fbfd 100%);
    }
    .flow-wrapper {
        max-width: 1140px;
        margin: 0 auto;
    }
    .flow-lead {
        color: #5c7083;
    }
    .flow-step-card {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        padding: 1rem 1.25rem;
        border-radius: 18px;
        background: linear-gradient(90deg, #f5fbfc 0%, #eef6f7 100%);
        border: 1px solid #e4edf2;
        box-shadow: 0 16px 40px -24px rgba(15, 23, 42, 0.25);
    }
    .flow-step-number {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: #0f7a74;
        color: #fff;
        display: grid;
        place-items: center;
        font-weight: 800;
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    .flow-step-title {
        color: #0f172a;
        font-weight: 700;
    }
    .flow-step-desc {
        margin-bottom: 0;
        color: #3b4b5d;
        line-height: 1.55;
    }
    .flow-illustration {
        max-width: 420px;
        width: 100%;
    }
    @media (max-width: 575.98px) {
        .flow-step-card {
            padding: 0.85rem 1rem;
        }
        .flow-step-number {
            width: 42px;
            height: 42px;
            font-size: 1.1rem;
        }
    }
</style>
@endpush
<section class="section-shell bg-white flow-section">
    <div class="container">
        <div class="flow-wrapper">
            <div class="text-center mb-3">
                <h3 class="fw-bold mb-2">{{ $flowTitle }}</h3>
                <p class="flow-lead mb-0">{{ $flowSubtitle }}</p>
            </div>
            <div class="row g-4 align-items-start">
                @if($flowImage)
                <div class="col-lg-5 text-center">
                    <img src="{{ $flowImage }}" class="img-fluid rounded-4 shadow-soft flow-illustration" alt="Ilustrasi alur pelatihan">
                </div>
                @endif
                <div class="col-lg-7">
                    <div class="vstack gap-3">
                        @forelse($flowSteps as $step)
                            <div class="flow-step-card">
                                <div class="flow-step-number">{{ $loop->iteration }}</div>
                                <div>
                                    <p class="flow-step-desc mb-0">{{ $step->deskripsi ?: $step->judul }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info text-center mb-0">Belum ada data alur.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-shell bg-white">
    <div class="container">
        <div class="text-center section-header">
            <h3 class="fw-bold">{{ $newsTitle }}</h3>
            <p class="text-muted">{{ $newsSubtitle }}</p>
        </div>
        <div class="row g-3">
            @forelse($beritaTerbaru as $news)
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <img src="{{ $news->gambar_utama ? asset($news->gambar_utama) : 'https://placehold.co/520x260?text=Berita' }}" class="card-img-top" style="height:200px; object-fit:cover;" alt="{{ $news->judul }}">
                    <div class="p-3">
                        <small class="text-muted"><i class="far fa-calendar-alt me-1"></i> {{ $news->created_at->translatedFormat('d F Y') }}</small>
                        <h6 class="fw-bold mt-2">
                            <a href="{{ route('berita.show', $news->slug) }}" class="text-decoration-none text-dark">{{ Str::limit($news->judul, 70) }}</a>
                        </h6>
                        <p class="text-muted small mb-3">{{ Str::limit(strip_tags($news->konten), 90) }}</p>
                        <a href="{{ route('berita.show', $news->slug) }}" class="btn btn-sm btn-outline-primary">Baca</a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center text-muted">Belum ada berita terbaru.</div>
            @endforelse
        </div>
    </div>
</section>

<section class="py-5" style="background: linear-gradient(120deg, #0b4d78 0%, #117a9f 55%, #19b6d7 100%); color:white;">
    <div class="container text-center">
        <h3 class="fw-bold mb-2 text-white">{{ $settings['cta_title'] ?? 'Tunggu Apalagi? Yuk Langsung Daftar Kelasnya' }}</h3>
        <p class="mb-4 text-white">{{ $settings['cta_subtitle'] ?? 'Ikuti program pelatihan terbaik untuk meningkatkan kompetensi dan siap kerja.' }}</p>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="{{ $settings['cta_button_1_link'] ?? route('pelatihan.katalog') }}" class="btn btn-cta">{{ $settings['cta_button_1_text'] ?? 'Daftar di Portal' }}</a>
            <a href="{{ $settings['cta_button_2_link'] ?? route('kontak') }}" class="btn btn-cta-ghost">{{ $settings['cta_button_2_text'] ?? 'Hubungi Kami' }}</a>
        </div>
    </div>
</section>

<section class="section-shell bg-white">
    <div class="container">
        <div class="text-center section-header">
            <h3 class="fw-bold">{{ $testimonialTitle }}</h3>
            <p class="text-muted">{{ $testimonialSubtitle }}</p>
        </div>
        @php
            $embedUrl = function (?string $url): ?string {
                if (! $url) return null;

                $videoId = null;
                $start = null;

                try {
                    $parts = parse_url($url);
                    $host = $parts['host'] ?? '';
                    $path = $parts['path'] ?? '';
                    parse_str($parts['query'] ?? '', $query);

                    // Handle youtu.be short link
                    if (str_contains($host, 'youtu.be')) {
                        $videoId = trim($path, '/');
                    }

                    // Handle youtube.com/watch or embed
                    if (str_contains($host, 'youtube.com')) {
                        if (isset($query['v'])) {
                            $videoId = $query['v'];
                        } elseif (str_contains($path, '/embed/')) {
                            $videoId = last(explode('/embed/', $path));
                        } elseif (str_contains($path, '/shorts/')) {
                            $videoId = last(explode('/shorts/', $path));
                        }
                    }

                    // Start time (t= or start=)
                    if (isset($query['t'])) {
                        // t may be like 1m30s or seconds
                        $t = $query['t'];
                        if (preg_match('/(?:(\\d+)h)?(?:(\\d+)m)?(?:(\\d+)s)?|(\\d+)/', $t, $m)) {
                            $hours = (int)($m[1] ?? 0);
                            $mins = (int)($m[2] ?? 0);
                            $secs = (int)($m[3] ?? ($m[4] ?? 0));
                            $start = $hours * 3600 + $mins * 60 + $secs;
                        }
                    } elseif (isset($query['start'])) {
                        $start = (int) $query['start'];
                    }
                } catch (\Throwable $e) {
                    return null;
                }

                if (! $videoId) {
                    return null;
                }

                $params = ['rel' => 0];
                if ($start) $params['start'] = $start;

                return 'https://www.youtube-nocookie.com/embed/' . $videoId . ($params ? ('?' . http_build_query($params)) : '');
            };
        @endphp
        <div class="row g-3">
            @forelse($testimonials as $testi)
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="p-3">
                        <h6 class="fw-bold mb-1">{{ $testi->nama }}</h6>
                        <div class="text-muted small mb-2">{{ $testi->jabatan }}</div>
                        <p class="text-secondary small">{{ $testi->pesan }}</p>
                        @php
                            $videoSrc = $embedUrl($testi->video_url);
                        @endphp
                        @if($videoSrc)
                            <div class="ratio ratio-16x9 mt-2">
                                <iframe
                                    src="{{ $videoSrc }}"
                                    title="Testimoni {{ $testi->nama }}"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center text-muted">Belum ada testimoni.</div>
            @endforelse
        </div>
    </div>
</section>

<section class="section-shell bg-light">
    <div class="container">
        <div class="text-center section-header">
            <h3 class="fw-bold">{{ $partnerTitle }}</h3>
            <p class="text-muted">{{ $partnerSubtitle }}</p>
        </div>
        @if($partners->isEmpty())
            <div class="alert alert-info text-center">Belum ada data partner.</div>
        @else
            <div id="partnerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2500">
                <div class="carousel-inner">
                    @foreach($partners->chunk(6) as $chunkIndex => $chunk)
                    <div class="carousel-item {{ $chunkIndex === 0 ? 'active' : '' }}">
                        <div class="d-flex justify-content-center align-items-center gap-4 flex-wrap">
                            @foreach($chunk as $partner)
                                <div class="p-3 bg-white rounded shadow-sm">
                                    @if($partner->logo)
                                        @if($partner->tautan)
                                            <a href="{{ $partner->tautan }}" target="_blank" rel="noopener">
                                                <img src="{{ asset($partner->logo) }}" alt="{{ $partner->nama }}" style="height:60px; object-fit:contain;">
                                            </a>
                                        @else
                                            <img src="{{ asset($partner->logo) }}" alt="{{ $partner->nama }}" style="height:60px; object-fit:contain;">
                                        @endif
                                    @else
                                        <span class="fw-bold">{{ $partner->nama }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#partnerCarousel" data-bs-slide="prev" aria-label="Partner sebelumnya">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#partnerCarousel" data-bs-slide="next" aria-label="Partner berikutnya">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>
        @endif
    </div>
</section>

<section class="section-shell bg-white">
    <div class="container">
        <div class="text-center section-header">
            <h3 class="fw-bold">{{ $instructorTitle }}</h3>
            <p class="text-muted">{{ $instructorSubtitle }}</p>
        </div>
        @if($instructors->isEmpty())
            <div class="alert alert-info text-center">Belum ada data instruktur.</div>
        @else
            <div id="instructorCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                <div class="carousel-inner">
                    @foreach($instructors->chunk(4) as $chunkIndex => $chunk)
                    <div class="carousel-item {{ $chunkIndex === 0 ? 'active' : '' }}">
                        <div class="row g-3 justify-content-center">
                            @foreach($chunk as $instruktur)
                            <div class="col-md-3 col-6">
                                <div class="card h-100 border-0 shadow-sm text-center p-3">
                                    <div class="d-flex justify-content-center mb-3">
                                        <div class="rounded-circle overflow-hidden" style="width:80px; height:80px;">
                                            <img src="{{ $instruktur->foto ? asset($instruktur->foto) : 'https://placehold.co/160x160' }}" class="w-100 h-100" style="object-fit: cover;" alt="{{ $instruktur->nama }}">
                                        </div>
                                    </div>
                                    <h6 class="fw-bold mb-1">{{ $instruktur->nama }}</h6>
                                    <div class="text-muted small mb-2">{{ $instruktur->keahlian }}</div>
                                    <p class="small text-secondary">{{ Str::limit($instruktur->deskripsi, 80) }}</p>
                                    <div class="d-flex justify-content-center gap-2">
                                        @if($instruktur->linkedin)
                                            <a href="{{ $instruktur->linkedin }}" target="_blank" class="text-primary"><i class="fab fa-linkedin fa-lg"></i></a>
                                        @endif
                                        @if($instruktur->whatsapp)
                                            <a href="https://wa.me/{{ preg_replace('/\\D+/', '', $instruktur->whatsapp) }}" target="_blank" class="text-success"><i class="fab fa-whatsapp fa-lg"></i></a>
                                        @endif
                                        @if($instruktur->email)
                                            <a href="mailto:{{ $instruktur->email }}" class="text-secondary"><i class="fas fa-envelope fa-lg"></i></a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#instructorCarousel" data-bs-slide="prev" aria-label="Instruktur sebelumnya">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#instructorCarousel" data-bs-slide="next" aria-label="Instruktur berikutnya">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>
        @endif
    </div>
</section>

<section class="section-shell bg-light">
    <div class="container">
        <div class="text-center section-header">
            <h3 class="fw-bold">{{ $galleryTitle }}</h3>
            <p class="text-muted">{{ $gallerySubtitle }}</p>
        </div>
        <div class="row g-2">
            @forelse($galeris as $foto)
            <div class="col-lg-3 col-md-4 col-6">
                <a href="{{ asset($foto->gambar) }}" target="_blank" rel="noopener" class="d-block overflow-hidden rounded position-relative group-hover" aria-label="Lihat foto {{ $foto->judul }}">
                    <img src="{{ asset($foto->gambar) }}" class="img-fluid w-100 hover-zoom" alt="{{ $foto->judul }}" style="height: 200px; object-fit: cover;">
                    <div class="position-absolute bottom-0 start-0 w-100 bg-dark bg-opacity-75 text-white p-2 small text-truncate">
                        {{ $foto->judul }}
                    </div>
                </a>
            </div>
            @empty
            <div class="col-12 text-center text-muted">Belum ada foto di galeri.</div>
            @endforelse
        </div>
    </div>
</section>
@endsection
