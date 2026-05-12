@extends('layouts.app')

@php
    $resolveImage = function ($path, $fallback = null) {
        if (!$path) {
            return $fallback;
        }
        return \Illuminate\Support\Str::startsWith($path, ['http', 'https']) ? $path : asset($path);
    };
    $heroImage = $resolveImage($setting->hero_image, 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1400&q=70');
@endphp

@push('styles')
<style>
    .contact-hero {
        background:linear-gradient(135deg, rgba(7,60,108,.92), rgba(7,108,103,.85));
        border-bottom-left-radius:48px;
        border-bottom-right-radius:48px;
        padding:4rem 0;
        color:#fff;
    }
    .contact-hero-card {
        border-radius:32px;
        overflow:hidden;
        box-shadow:0 40px 110px -60px rgba(0,0,0,.65);
        background:rgba(255,255,255,.08);
        border:1px solid rgba(255,255,255,.2);
        min-height:360px;
    }
    .contact-hero-card img{ width:100%; height:100%; object-fit:cover; }
    .contact-hero-card .hero-text{ padding:3rem; }
    .contact-badge{
        background:rgba(255,255,255,.2);
        color:#fff;
        padding:.4rem 1.2rem;
        border-radius:999px;
        letter-spacing:.12em;
        font-weight:600;
        font-size:.8rem;
    }
    .contact-hero-card .hero-text p{
        color:rgba(255,255,255,.7);
    }
    .contact-map-card iframe{
        border:0;
        width:100%;
        height:360px;
        border-radius:24px;
    }
    .contact-info-grid .card{
        border:1px solid #dbe6f3;
        border-radius:20px;
        padding:1.5rem;
        min-height:180px;
    }
    .contact-info-grid .icon-circle{
        width:48px;
        height:48px;
        border-radius:12px;
        background:#eaf4ff;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#0d5c84;
        margin-bottom:1rem;
        font-size:1.2rem;
    }
    .contact-cta{
        border-radius:32px;
        background:linear-gradient(120deg,#def2fb,#e6fff3);
        padding:3rem;
        box-shadow:0 30px 70px -55px rgba(0,0,0,.35);
    }
</style>
@endpush

@section('content')
<section class="contact-hero">
    <div class="container">
        <div class="contact-hero-card row g-0 align-items-stretch">
            <div class="col-lg-7 hero-text">
                <span class="contact-badge d-inline-block mb-3">{{ $setting->hero_subtitle ?? 'Hubungi Kami' }}</span>
                <h1 class="display-5 fw-bold">{{ $setting->hero_title ?? 'Hubungi Kami' }}</h1>
                <p class="text-muted mb-4">{{ $setting->hero_description ?? 'Hubungi kami dan kami siap membantu Anda!' }}</p>
                @if($setting->hero_button_text)
                    <a href="{{ $setting->hero_button_link ?? '#kontak' }}" class="btn btn-success rounded-pill px-4">{{ $setting->hero_button_text }}</a>
                @endif
            </div>
            <div class="col-lg-5">
                <img src="{{ $heroImage }}" alt="Hubungi Kami">
            </div>
        </div>
    </div>
</section>

<section class="py-5" id="kontak">
    <div class="container">
        <div class="mb-4">
            <h2 class="fw-bold">{{ $setting->map_title ?? 'Temukan Kami' }}</h2>
            <p class="text-muted mb-4">{{ $setting->map_description ?? 'Temukan kami dengan menemukam alamat dan informasi kontak kami dengan mudah.' }}</p>
            <div class="contact-map-card">
                {!! \App\Support\HtmlSanitizer::cleanEmbed($setting->map_embed ?? '<div class="bg-light border rounded-4 p-5 text-center text-muted">Embed peta belum tersedia.</div>') !!}
            </div>
        </div>

        <div class="mt-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1">{{ $setting->info_section_title ?? 'Layanan Informasi Untuk Anda' }}</h3>
                    <p class="text-muted mb-0">{{ $setting->info_section_description ?? 'Layanan informasi untuk Anda dengan sumber informasi terpercaya untuk memenuhi kebutuhan Anda.' }}</p>
                </div>
            </div>
            <div class="row g-4 contact-info-grid">
                @forelse($channels as $channel)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            @if($channel->icon)
                                <div class="icon-circle">
                                    <i class="{{ $channel->icon }}"></i>
                                </div>
                            @endif
                            <h5 class="fw-bold mb-1">{{ $channel->title }}</h5>
                            <p class="text-muted small mb-2">{{ $channel->subtitle }}</p>
                            @if($channel->link)
                                <a href="{{ $channel->link }}" class="fw-semibold" target="_blank">{{ $channel->label ?? $channel->link }}</a>
                            @elseif($channel->label)
                                <p class="fw-semibold mb-0">{{ $channel->label }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted">Belum ada data kontak.</div>
                @endforelse
            </div>
        </div>

        <div class="row g-4 align-items-start mt-5">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3">Kirim Pesan / Pengaduan</h4>
                        <p class="text-muted mb-4">Masukan dan saran Anda sangat berarti bagi kemajuan pelayanan kami.</p>
                        <ul class="list-unstyled small mb-0">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Respon maksimal 3 hari kerja</li>
                            <li class="mb-0"><i class="fas fa-check-circle text-success me-2"></i> Data Anda kami jaga kerahasiaannya</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <form action="{{ route('kontak.store') }}" method="POST" class="card border-0 shadow-sm rounded-4 p-4">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control bg-light @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                            @error('nama')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control bg-light @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Subjek</label>
                        <input type="text" name="subjek" class="form-control bg-light @error('subjek') is-invalid @enderror" value="{{ old('subjek') }}" required>
                        @error('subjek')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Isi Pesan</label>
                        <textarea name="pesan" rows="5" class="form-control bg-light @error('pesan') is-invalid @enderror" required>{{ old('pesan') }}</textarea>
                        @error('pesan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Verifikasi Keamanan</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-semibold">{{ $captchaQuestion ?? '' }}</span>
                            <input type="text" name="captcha_answer" class="form-control bg-light @error('captcha_answer') is-invalid @enderror" value="{{ old('captcha_answer') }}" required>
                        </div>
                        @error('captcha_answer')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="text-end mt-3">
                        <button class="btn btn-primary px-4 py-2"><i class="fas fa-paper-plane me-2"></i> Kirim Pesan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="contact-cta mt-5">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <h3 class="fw-bold mb-2">{{ $setting->cta_title ?? 'Anda Siap Tingkatkan Skill dengan Kami?' }}</h3>
                    <p class="text-muted mb-0">{{ $setting->cta_description ?? 'Pilih topik pelatihan sesuai minatmu dan segera pelajari materinya untuk menguasai keahlian yang kamu butuhkan.' }}</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    @if($setting->cta_primary_text)
                        <a href="{{ $setting->cta_primary_link ?? route('pelatihan.katalog') }}" class="btn btn-primary rounded-pill px-4 me-2">{{ $setting->cta_primary_text }}</a>
                    @endif
                    @if($setting->cta_secondary_text)
                        <a href="{{ $setting->cta_secondary_link ?? '#' }}" class="btn btn-outline-primary rounded-pill px-4">{{ $setting->cta_secondary_text }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
