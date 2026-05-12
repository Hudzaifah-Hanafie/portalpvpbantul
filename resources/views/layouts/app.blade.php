<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
@php
    $globalSettings = $footerSettings ?? collect();
    $siteName = $globalSettings['site_name'] ?? 'Satpel PVP Bantul';
    $siteTagline = $globalSettings['site_tagline'] ?? 'Kemnaker RI';
    $metaTitle = trim($__env->yieldContent('title'));
    $metaTitle = $metaTitle ? $metaTitle . ' | ' . $siteName : $siteName;
    $metaDescription = trim($__env->yieldContent('meta_description') ?: ($globalSettings['meta_description'] ?? 'Informasi resmi Satpel PVP Bantul, pelatihan vokasi, jadwal kelas, layanan publik, dan kolaborasi industri.'));
    $metaImage = $globalSettings['meta_image'] ?? asset('image/logo/logo_kemnaker.svg');
    $canonicalUrl = url()->current();
    $currentUser = auth()->user();
    $hasLmsAccess = $currentUser?->enrollments()
        ->whereIn('status', ['active', 'approved', 'completed', 'pending'])
        ->exists();
@endphp
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $globalSettings['meta_keywords'] ?? 'pelatihan vokasi, bpvp bantul, kemnaker, satpel' }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="icon" type="image/png" href="{{ $globalSettings['favicon'] ?? asset('image/logo/logo_kemnaker.svg') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @stack('meta')
    @stack('styles')

</head>
<body>
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>

    <nav class="navbar navbar-expand-lg main-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <img src="{{ asset('image/logo/logo_kemnaker.svg') }}" alt="Logo Kemnaker" height="42" class="me-2">
                <div class="fw-bold text-uppercase lh-sm" style="letter-spacing:0.5px;">
                    <span class="brand-name">{{ $siteName }}</span>
                    <span class="d-block brand-tagline">{{ $globalSettings['partner_name'] ?? 'BPVP Surakarta' }}</span>
                    <span class="d-block brand-tagline">{{ $siteTagline }}</span>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Buka navigasi utama" aria-expanded="false" aria-controls="navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Beranda</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('profil.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">Tentang Kami</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('profil.instansi') }}">Profil Instansi</a></li>
                            <li><a class="dropdown-item" href="{{ route('profil.instruktur') }}">Profil Instruktur</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('pelatihan.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">Pelatihan</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('pelatihan.katalog') }}">Katalog Pelatihan</a></li>
                            <li><a class="dropdown-item" href="{{ route('pelatihan.jadwal') }}">Jadwal Pelatihan</a></li>
                            <li><a class="dropdown-item" href="{{ route('pelatihan.pemberdayaan') }}">Pemberdayaan</a></li>
                            <li><a class="dropdown-item" href="{{ route('pelatihan.produktivitas') }}">Peningkatan Produktivitas</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('sertifikasi') || request()->routeIs('program*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">Layanan Unggulan</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('sertifikasi') }}">Sertifikasi</a></li>
                            <li><a class="dropdown-item" href="{{ route('program') }}">Program</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('berita.*') || request()->routeIs('pengumuman.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">Berita</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('berita.terkini') }}">Berita Terkini</a></li>
                            <li><a class="dropdown-item" href="{{ route('berita.lowongan') }}">Lowongan Kerja</a></li>
                            <li><a class="dropdown-item" href="{{ route('pengumuman.index') }}">Pengumuman</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('resource.*') || request()->routeIs('ppid') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">Pustaka</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('resource.infografis') }}">Infografis Alumni</a></li>
                            <li><a class="dropdown-item" href="{{ route('resource.publikasi') }}">Publikasi</a></li>
                            <li><a class="dropdown-item" href="{{ route('resource.pelayanan') }}">Pelayanan Publik</a></li>
                            <li><a class="dropdown-item" href="{{ route('resource.faq') }}">Frequently Asked Questions (FAQ)</a></li>
                            <li><a class="dropdown-item" href="{{ route('resource.hubungi') }}">Hubungi Kami</a></li>
                            <li><a class="dropdown-item" href="{{ route('ppid') }}">PPID</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('alumni.forum.*') ? 'active' : '' }}" href="{{ route('alumni.forum.index') }}">Forum Alumni</a></li>
                </ul>

                <div class="d-flex align-items-center ms-lg-3 my-2 my-lg-0 gap-2 flex-nowrap w-100">
                    <form action="{{ route('search') }}" method="GET" class="input-group flex-fill" role="search" aria-label="Pencarian global" style="border:1px solid #6c7a89; border-radius:12px; overflow:hidden;">
                        <label for="navbar-search" class="visually-hidden">Cari informasi di situs</label>
                        <input id="navbar-search" class="form-control form-control-sm border-0" type="search" name="q" placeholder="Cari info..." aria-label="Kolom pencarian global" value="{{ request('q') }}" required>
                        <button class="btn btn-light border-0 text-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                    @auth
                        @php
                            $lmsRoute = null;
                            if ($currentUser?->hasAnyRole(['instructor', 'instruktur'])) {
                                $lmsRoute = route('instructor.dashboard');
                            } elseif ($currentUser?->hasRole('participant')) {
                                $lmsRoute = route('participant.dashboard');
                            }
                        @endphp
                        @if($lmsRoute)
                            <a href="{{ $lmsRoute }}" class="btn btn-primary btn-sm ms-2">Masuk LMS</a>
                        @endif
                        <div class="dropdown ms-2">
                            <button class="btn btn-outline-secondary btn-sm rounded-circle p-2" type="button" id="alumniActions" data-bs-toggle="dropdown" data-bs-auto-close="outside" data-bs-placement="bottom" title="Menu akun" data-bs-toggle="tooltip">
                                <i class="fas fa-user-circle fa-lg"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="alumniActions">
                                @if($currentUser)
                                    <li class="px-3 py-2">
                                        <div class="fw-semibold">{{ $currentUser->name }}</div>
                                        <small class="text-muted">{{ $currentUser->email }}</small>
                                    </li>
                                    <li><hr class="my-1"></li>
                                @endif
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}">Profil Saya</a></li>
                                @if($lmsRoute)
                                    <li><a class="dropdown-item" href="{{ $lmsRoute }}">Masuk LMS</a></li>
                                @endif
                                @if($currentUser?->hasRole('alumni'))
                                    <li><a class="dropdown-item" href="{{ route('alumni.tracer') }}">Tracer Study</a></li>
                                @endif
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endauth
                </div>

            </div>
        </div>
    </nav>

    @if(session('impersonator_id'))
        <div class="alert alert-warning mb-0 rounded-0">
            <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    Mode impersonasi aktif: Anda sedang masuk sebagai <strong>{{ auth()->user()->name ?? 'pengguna lain' }}</strong>.
                    <span class="text-muted">Akun asli: {{ session('impersonator_name') }}.</span>
                </div>
                <form action="{{ route('impersonate.stop') }}" method="POST" class="m-0">
                    @csrf
                    <button class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-rotate-left"></i> Kembali ke akun asli
                    </button>
                </form>
            </div>
        </div>
    @endif

    <main class="site-main" id="main-content" tabindex="-1">
        @yield('content')
    </main>

    @php
        $settings = ($footerSettings ?? collect([])) ?: ($settings ?? collect([]));
    @endphp
    @php
        $footerSpacingClass = request()->routeIs('sertifikasi') ? '' : 'mt-5';
    @endphp
    <footer class="{{ $footerSpacingClass }} site-footer">
        <div class="container py-4">
            <div class="row g-4">
                <div class="col-md-6">
                    <h5 class="fw-bold">PVP BANTUL</h5>
                    <p class="mb-2">{{ $settings['footer_address'] ?? 'Jl. Parangtritis Km 10, Bantul, DI Yogyakarta.' }}</p>
                    <p class="mb-1">{{ $settings['footer_phone'] ?? '(0274) 367123' }} @if(!empty($settings['footer_phone_alt'])) / {{ $settings['footer_phone_alt'] }} @endif</p>
                    <p class="mb-1">{{ $settings['footer_email'] ?? 'info@bpvpbantul.kemnaker.go.id' }}</p>
                    <p class="mb-0">{{ $settings['footer_operasional'] ?? "Jam Operasional:\nSenin - Kamis: 07.30 - 16.00 WIB\nJumat: 07.30 - 16.30 WIB" }}</p>
                    <div class="mt-3 d-flex gap-3">
                        @if(!empty($settings['footer_instagram']))
                            <a class="text-white" href="{{ $settings['footer_instagram'] }}" target="_blank"><i class="fab fa-instagram fa-lg"></i></a>
                        @endif
                        @if(!empty($settings['footer_facebook']))
                            <a class="text-white" href="{{ $settings['footer_facebook'] }}" target="_blank"><i class="fab fa-facebook fa-lg"></i></a>
                        @endif
                        @if(!empty($settings['footer_twitter']))
                            <a class="text-white" href="{{ $settings['footer_twitter'] }}" target="_blank"><i class="fab fa-twitter fa-lg"></i></a>
                        @endif
                        @if(!empty($settings['footer_youtube']))
                            <a class="text-white" href="{{ $settings['footer_youtube'] }}" target="_blank"><i class="fab fa-youtube fa-lg"></i></a>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <h5 class="fw-bold">Lokasi</h5>
                    @if(!empty($settings['footer_embed_map']))
                        {!! \App\Support\HtmlSanitizer::cleanEmbed($settings['footer_embed_map']) !!}
                    @else
                        <div class="bg-dark bg-opacity-25 text-center py-4 rounded">Embed peta</div>
                    @endif
                </div>
            </div>
            <div class="text-center mt-4 pt-3 border-top border-light">
                <small>&copy; {{ date('Y') }} Satpel PVP Bantul. All Rights Reserved.</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-bs-toggle=\"tooltip\"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    </script>
    @stack('scripts')
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('{{ asset('sw-survey.js') }}').catch(() => {});
        }
    </script>
</body>
</html>
