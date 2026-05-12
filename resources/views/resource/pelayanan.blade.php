@extends('layouts.app')

@php
    $resolveImage = function ($path, $fallback = null) {
        if (!$path) {
            return $fallback;
        }
        return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
    };
    $heroImage = $resolveImage($setting->hero_image, 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=1200&q=70');
    $policyImage = $resolveImage($setting->policy_image, 'https://images.unsplash.com/photo-1497493292307-31c376b6e479?auto=format&fit=crop&w=1200&q=70');
@endphp

@push('styles')
<style>
    .ps-hero {
        background: linear-gradient(135deg, rgba(7,60,108,.92), rgba(7,108,103,.85));
        border-bottom-left-radius: 48px;
        border-bottom-right-radius: 48px;
        padding: 4rem 0;
        color: #fff;
    }
    .ps-hero-card {
        background: rgba(255,255,255,.1);
        border-radius: 32px;
        border: 1px solid rgba(255,255,255,.2);
        padding: 3rem;
        height: 100%;
        min-height: 360px;
        box-shadow: 0 40px 110px -60px rgba(0,0,0,.65);
        backdrop-filter: blur(6px);
    }
    .ps-hero-card p {
        color: rgba(255,255,255,.8);
    }
    .ps-hero-image {
        height: 360px;
    }
    .ps-hero-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 32px;
        box-shadow: 0 30px 70px -45px rgba(0,0,0,.6);
    }
    .ps-badge {
        background: rgba(255,255,255,.2);
        color: #fff;
        font-size: .8rem;
        font-weight: 600;
        letter-spacing: .1em;
        border-radius: 999px;
        padding: .45rem 1.2rem;
    }
    .ps-content {
        background: #fff;
        border-radius: 28px;
        padding: 2rem;
        box-shadow: 0 25px 60px -45px rgba(0,0,0,.6);
    }
    .ps-policy {
        background: linear-gradient(135deg, #f8fcff, #e0f3ff);
        border-radius: 28px;
        padding: 2.5rem;
    }
    .ps-policy-card {
        border-radius: 24px;
        padding: 2rem;
        background: #fff;
        box-shadow: 0 25px 60px -45px rgba(3,43,84,.8);
    }
    .ps-flow-card {
        background: #fff;
        border-radius: 28px;
        box-shadow: 0 22px 60px -45px rgba(6,35,76,.5);
        overflow: hidden;
        height: 100%;
    }
    .ps-flow-card img {
        width: 100%;
        height: 230px;
        object-fit: cover;
    }
    .ps-flow-card ol {
        counter-reset: list-counter;
        list-style: none;
        padding-left: 0;
    }
    .ps-flow-card ol li {
        counter-increment: list-counter;
        margin-bottom: .65rem;
        padding-left: 2.2rem;
        position: relative;
    }
    .ps-flow-card ol li::before {
        content: counter(list-counter);
        position: absolute;
        left: 0;
        top: .25rem;
        width: 1.8rem;
        height: 1.8rem;
        border-radius: 50%;
        background: #0d9488;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    .ps-cta {
        border-radius: 32px;
        background: linear-gradient(110deg, #e3f6ff, #e7fbff);
        padding: 2.5rem;
    }
</style>
@endpush

@section('content')
<section class="ps-hero">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                <div class="ps-hero-card">
                    <span class="ps-badge d-inline-block mb-3">{{ $setting->hero_subtitle ?? 'Pelayanan Publik' }}</span>
                    <h1 class="display-4 fw-bold text-white">{{ $setting->hero_title ?? 'Pelayanan Publik' }}</h1>
                    <p class="text-white-50 mb-4">{{ $setting->hero_description ?? 'Pelayanan ramah, responsif, dan terintegrasi untuk seluruh masyarakat.' }}</p>
                    @if($setting->hero_button_text)
                        <a href="{{ $setting->hero_button_link ?? '#maklumat' }}" class="btn btn-outline-light rounded-pill px-4">{{ $setting->hero_button_text }}</a>
                    @endif
                </div>
            </div>
            <div class="col-lg-5">
                <div class="ps-hero-image">
                    <img src="{{ $heroImage }}" alt="Pelayanan Publik">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="ps-content mb-5">
            <div class="row g-5">
                <div class="col-lg-7">
                    <h2 class="fw-bold mb-3">{{ $setting->intro_title ?? 'Pelayanan Publik' }}</h2>
                    <p class="text-muted">{{ $setting->intro_description ?? 'Pelayanan publik disiapkan dengan standar kepuasan masyarakat dan service excellence.' }}</p>
                    <div class="mt-4 content">
                        {!! \App\Support\HtmlSanitizer::clean($setting->intro_content ?? '<p>Tim pelayanan kami siap memfasilitasi informasi pelatihan, pelayanan pengaduan, hingga konsultasi layanan produktivitas.</p>') !!}
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="border rounded-4 p-4 h-100 bg-light">
                        <h5 class="fw-bold mb-3">{{ $setting->regulation_title ?? 'Dasar Hukum Pelayanan' }}</h5>
                        <ul class="list-unstyled text-muted mb-0">
                            @forelse($setting->regulation_list as $item)
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i> {{ $item }}
                                </li>
                            @empty
                                <li class="text-muted">Belum ada regulasi yang ditambahkan.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div id="maklumat" class="ps-policy mb-5">
            <div class="row g-4 align-items-center">
                <div class="col-lg-5">
                    <img src="{{ $policyImage }}" alt="Maklumat" class="img-fluid rounded-4 shadow">
                </div>
                <div class="col-lg-7">
                    <div class="ps-policy-card">
                        <p class="text-uppercase text-primary small fw-semibold mb-2">{{ $setting->policy_title ?? 'Maklumat Pelayanan' }}</p>
                        <h3 class="fw-bold mb-3">{{ $setting->policy_subtitle ?? 'Dengan ini kami menyatakan:' }}</h3>
                        <div class="text-muted mb-4 content">
                            {!! nl2br(e($setting->policy_description ?? '1. Sangup menyelenggarakan pelayanan sesuai standar yang telah ditetapkan.<br>2. Menyediakan layanan sesuai kesepakatan dan melakukan perbaikan secara terus menerus.')) !!}
                        </div>
                        <div>
                            <p class="mb-0 fw-semibold">{{ $setting->policy_signature ?? 'Kepala Satpel PVP Bantul' }}</p>
                            <small class="text-muted">{{ $setting->policy_position ?? 'Pejabat Penandatangan Maklumat' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-5">
            <div class="row g-4 align-items-center">
                <div class="col-lg-5">
                    <div class="bg-white border rounded-4 p-4 shadow-sm h-100">
                        <span class="badge bg-success-subtle text-success fw-semibold mb-2">{{ $setting->standard_document_badge ?? 'Dokumen' }}</span>
                        <h3 class="fw-bold">{{ $setting->standard_title ?? 'Standar Pelayanan Publik' }}</h3>
                        <p class="text-muted mb-0">{{ $setting->standard_description ?? 'Standar pelayanan kami terus ditingkatkan agar kualitas layanan terjaga.' }}</p>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="border rounded-4 p-4 bg-white shadow-sm">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <span class="display-6 text-primary"><i class="fas fa-file-pdf"></i></span>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">{{ $setting->standard_document_title ?? 'Standar Pelayanan Publik BPVP' }}</h5>
                                <p class="text-muted mb-3">{{ $setting->standard_document_description ?? 'Dokumen standar pelayanan publik terbaru yang memuat indikator mutu, waktu layanan, dan hak masyarakat.' }}</p>
                                @if($setting->standard_document_file)
                                    <a href="{{ asset($setting->standard_document_file) }}" class="btn btn-outline-primary rounded-pill px-4" target="_blank">Unduh Dokumen</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-5">
            <div class="text-center mb-4">
                <span class="ps-badge mb-2">{{ $setting->flow_section_title ?? 'Alur Pelayanan & Pengaduan' }}</span>
                <p class="text-muted mb-0">{{ $setting->flow_section_description ?? 'Ikuti langkah berikut untuk mengakses pelayanan pelatihan hingga penyampaian pengaduan.' }}</p>
            </div>
            @forelse($flows as $category => $categoryFlows)
                <div class="mb-4">
                    <h4 class="fw-bold text-primary mb-3 text-capitalize">{{ str_replace('_', ' ', $category) }}</h4>
                    <div class="row g-4">
                        @foreach($categoryFlows as $flow)
                            <div class="col-lg-6">
                                <div class="ps-flow-card">
                                    @if($flow->image)
                                        <img src="{{ $resolveImage($flow->image, 'https://placehold.co/600x360?text=Pelayanan') }}" alt="{{ $flow->title }}">
                                    @endif
                                    <div class="p-4">
                                        <h5 class="fw-bold">{{ $flow->title }}</h5>
                                        <p class="text-muted small">{{ $flow->subtitle }}</p>
                                        @if($flow->steps_list)
                                            <ol class="mt-3">
                                                @foreach($flow->steps_list as $step)
                                                    <li>{{ $step }}</li>
                                                @endforeach
                                            </ol>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-center text-muted">Belum ada alur pelayanan yang diinputkan.</p>
            @endforelse
        </div>

        <div class="ps-cta">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <h3 class="fw-bold text-dark mb-2">{{ $setting->cta_title ?? 'Anda Siap Tingkatkan Skill dengan Kami?' }}</h3>
                    <p class="text-muted mb-0">{{ $setting->cta_description ?? 'Pilih moda pelayanan yang Anda butuhkan dan kami akan membantu Anda menuntaskan prosedurnya.' }}</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    @if($setting->cta_primary_text)
                        <a href="{{ $setting->cta_primary_link ?? '#' }}" class="btn btn-primary rounded-pill px-4 me-2">{{ $setting->cta_primary_text }}</a>
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
