@extends('layouts.app')

@php
    $resolveImage = function ($path, $fallback = null) {
        if (!$path) {
            return $fallback;
        }
        return \Illuminate\Support\Str::startsWith($path, ['http', 'https']) ? $path : asset($path);
    };
    $heroImage = $resolveImage($setting->hero_image, 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1400&q=70');
    $featuredCategory = $categories->first();
    $featuredFaq = $featuredCategory?->items->first();
@endphp

@push('styles')
<style>
    .faq-hero {
        background: linear-gradient(135deg, rgba(7,60,108,.92), rgba(7,108,103,.85));
        border-bottom-left-radius: 48px;
        border-bottom-right-radius: 48px;
        padding: 4rem 0;
        color: #fff;
    }
    .faq-hero-card {
        border-radius: 32px;
        box-shadow: 0 40px 120px -60px rgba(0,0,0,.65);
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.2);
        background: rgba(255,255,255,.08);
        min-height: 360px;
    }
    .faq-hero-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .faq-hero-card .hero-text { padding: 3rem; }
    .faq-badge {
        background: rgba(255,255,255,.2);
        color: #fff;
        padding: .4rem 1.2rem;
        border-radius: 999px;
        letter-spacing: .12em;
        font-size: .75rem;
        font-weight: 600;
    }
    .faq-hero-card .hero-text p{
        color: rgba(255,255,255,.75);
    }
    .faq-featured {
        border-radius: 24px;
        background: #0f7c6c;
        color: #fff;
        padding: 2rem;
        box-shadow: 0 35px 70px -40px rgba(0,0,0,.45);
    }
    .faq-info-card {
        background: #fff;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 20px 60px -55px rgba(8,27,81,.7);
        height: 100%;
    }
    .faq-stat {
        border: 1px solid #e7edf3;
        border-radius: 18px;
        padding: 1rem;
    }
    .faq-accordion .accordion-item {
        border: 1px solid #e7edf3;
        border-radius: 18px;
        margin-bottom: .75rem;
        overflow: hidden;
    }
    .faq-accordion .accordion-button {
        font-weight: 600;
        padding: 1.1rem 1.25rem;
    }
    .faq-accordion .accordion-button:not(.collapsed) {
        background: #e7f5ff;
        color: #0b5d8f;
        box-shadow: inset 0 -1px 0 rgba(0,0,0,.05);
    }
    .faq-contact {
        border-radius: 28px;
        background: linear-gradient(120deg,#def2fb,#e6fff3);
        padding: 3rem;
        box-shadow: 0 35px 80px -55px rgba(0,0,0,.4);
    }
</style>
@endpush

@section('content')
<section class="faq-hero">
    <div class="container">
        <div class="faq-hero-card row g-0 align-items-stretch">
            <div class="col-lg-7 hero-text">
                <span class="faq-badge d-inline-block mb-3">{{ $setting->hero_subtitle ?? 'Pertanyaan Populer' }}</span>
                <h1 class="display-5 fw-bold text-dark">{{ $setting->hero_title ?? 'Frequently Asked Questions (FAQ)' }}</h1>
                <p class="text-muted mb-4">{{ $setting->hero_description ?? 'Pertanyaan umum dan temukan jawaban untuk pertanyaan-pertanyaan Anda di sini.' }}</p>
                @if($setting->hero_button_text)
                    <a href="{{ $setting->hero_button_link ?? '#faq' }}" class="btn btn-success rounded-pill px-4">{{ $setting->hero_button_text }}</a>
                @endif
            </div>
            <div class="col-lg-5">
                <img src="{{ $heroImage }}" alt="FAQ">
            </div>
        </div>
    </div>
</section>

<section id="faq" class="py-5">
    <div class="container">
        <div class="row g-4 align-items-stretch mb-4">
            <div class="col-lg-7">
                <div class="faq-featured h-100">
                    <p class="text-uppercase small fw-semibold text-white-50 mb-1">{{ $setting->intro_title ?? 'Pertanyaan Utama' }}</p>
                    <h3 class="fw-bold mb-2">{{ $featuredFaq->question ?? 'Siap membantu kebutuhan informasi Anda' }}</h3>
                    <div class="text-white-50">
                        {!! \App\Support\HtmlSanitizer::clean($featuredFaq->answer ?? 'Tidak menemukan informasi yang dibutuhkan? Tim layanan kami siap menjawab pertanyaan Anda melalui FAQ ini atau kanal kontak resmi kami.') !!}
                    </div>
                    @if($setting->intro_description)
                        <div class="bg-white text-dark rounded-3 p-3 mt-3">
                            <p class="mb-0">{{ $setting->intro_description }}</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-5">
                <div class="faq-info-card d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="fw-bold text-primary mb-2">{{ $setting->info_title ?? 'Butuh bantuan lebih lanjut?' }}</h5>
                        <p class="text-muted mb-4">{{ $setting->info_description ?? 'Hubungi petugas front office kami di jam kerja atau kirimkan pertanyaan melalui formulir kontak.' }}</p>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="faq-stat text-center h-100">
                                <p class="text-muted small mb-1">{{ $setting->info_stat_primary_label ?? 'Pertanyaan Terjawab' }}</p>
                                <h3 class="fw-bold text-primary mb-0">{{ $setting->info_stat_primary_value ?? '250+' }}</h3>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="faq-stat text-center h-100">
                                <p class="text-muted small mb-1">{{ $setting->info_stat_secondary_label ?? 'Waktu Respon' }}</p>
                                <h3 class="fw-bold text-primary mb-0">{{ $setting->info_stat_secondary_value ?? '<24 Jam' }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @foreach($categories as $category)
            @php
                $items = $category->items;
                if($category->is($featuredCategory) && $featuredFaq) {
                    $items = $items->skip(1);
                }
            @endphp
            <div class="mb-5">
                <div class="d-flex align-items-center mb-3">
                    @if($category->icon)
                        <span class="me-3 fs-4 text-primary"><i class="{{ $category->icon }}"></i></span>
                    @endif
                    <div>
                        <h4 class="fw-bold mb-1">{{ $category->title }}</h4>
                        <p class="text-muted mb-0">{{ $category->subtitle }}</p>
                    </div>
                </div>
                <div class="faq-accordion accordion" id="accordion-{{ $category->id }}">
                    @forelse($items as $item)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-{{ $item->id }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $item->id }}">
                                    {{ $item->question }}
                                </button>
                            </h2>
                            <div id="collapse-{{ $item->id }}" class="accordion-collapse collapse" data-bs-parent="#accordion-{{ $category->id }}">
                                <div class="accordion-body">{!! \App\Support\HtmlSanitizer::clean($item->answer) !!}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">Belum ada FAQ pada kategori ini.</div>
                    @endforelse
                </div>
            </div>
        @endforeach

        <div class="faq-contact mt-5">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <h3 class="fw-bold mb-2 text-primary">{{ $setting->contact_title ?? 'Anda Punya Pertanyaan?' }}</h3>
                    <p class="text-muted mb-0">{{ $setting->contact_description ?? 'Hubungi kami jika Anda masih memiliki pertanyaan lainnya, tim kami akan merespon pertanyaan Anda secepatnya.' }}</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    @if($setting->contact_button_text)
                        <a href="{{ $setting->contact_button_link ?? route('kontak') }}" class="btn btn-success rounded-pill px-4">{{ $setting->contact_button_text }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
