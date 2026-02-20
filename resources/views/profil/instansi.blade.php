@extends('layouts.app')

@section('content')
@push('styles')
<style>
    .profile-hero{
        background:linear-gradient(135deg, rgba(7,60,108,.92), rgba(7,108,103,.85));
        border-bottom-left-radius:48px;
        border-bottom-right-radius:48px;
        padding:4rem 0;
        color:#fff;
    }
    .profile-hero-card{
        border-radius:32px;
        background:rgba(255,255,255,.08);
        border:1px solid rgba(255,255,255,.2);
        padding:3rem;
        min-height:360px;
        box-shadow:0 40px 110px -60px rgba(0,0,0,.6);
    }
    .profile-hero-card p{color:rgba(255,255,255,.75);}
    .profile-hero-image{
        height:360px;
        border-radius:32px;
        overflow:hidden;
        box-shadow:0 35px 80px -55px rgba(0,0,0,.5);
    }
    .profile-hero-image img{
        width:100%;
        height:100%;
        object-fit:cover;
    }
    .map-embed{
        position:relative;
        padding-top:56.25%;
        border-radius:24px;
        overflow:hidden;
        box-shadow:0 25px 60px -40px rgba(0,0,0,.45);
    }
    .map-embed iframe{
        position:absolute;
        inset:0;
        width:100%;
        height:100%;
        border:0;
    }
</style>
@endpush
<section class="profile-hero">
    <div class="container">
        <div class="row align-items-stretch g-4">
            <div class="col-lg-6">
                <div class="profile-hero-card h-100">
                    <span class="badge bg-white text-success fw-semibold mb-3 shadow-sm">Profil Satpel PVP Bantul</span>
                    <h1 class="fw-bold text-white mb-3">{{ $profilInstansi->judul ?? 'Profil Satpel PVP Bantul' }}</h1>
                    <p>{{ strip_tags(\Illuminate\Support\Str::limit($profilInstansi->konten ?? '', 200)) }}</p>
                    <a href="#profil-intro" class="btn btn-outline-light rounded-pill px-4">Lihat Selengkapnya</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="profile-hero-image">
                    <img src="{{ $profilInstansi->gambar ? asset($profilInstansi->gambar) : 'https://placehold.co/920x520?text=BPVP+Bantul' }}" alt="Profil Instansi">
                </div>
            </div>
        </div>
    </div>
</section>

<section id="profil-intro" class="py-5 bg-white">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-5">
                <div class="rounded-4 overflow-hidden shadow-sm">
                    <img src="{{ $selayang?->gambar ? asset($selayang->gambar) : 'https://placehold.co/600x420?text=BPVP+Bantul' }}" class="img-fluid" alt="Gedung BPVP">
                </div>
            </div>
            <div class="col-lg-7">
                <h3 class="fw-bold mb-3">{{ $profilInstansi->judul ?? 'Profil Satpel PVP Bantul' }}</h3>
                <div class="text-muted" style="line-height:1.7;">
                    {!! $profilInstansi->konten !!}
                </div>
            </div>
        </div>
    </div>
</section>

@if($sejarah)
<section class="py-5" style="background:#f8f9fb;">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <span class="badge bg-primary-subtle text-primary fw-semibold mb-2">Sejarah PVP Bantul</span>
                <h3 class="fw-bold mb-3">{{ $sejarah->judul ?? 'Sejarah Satpel PVP Bantul' }}</h3>
                <div class="text-muted" style="line-height:1.8;">
                    {!! $sejarah->konten !!}
                </div>
            </div>
            <div class="col-lg-6">
                <div class="rounded-4 overflow-hidden shadow-sm">
                    <img src="{{ $sejarah->gambar ? asset($sejarah->gambar) : 'https://placehold.co/720x480?text=Sejarah+PVP+Bantul' }}" class="img-fluid" alt="Sejarah Satpel PVP Bantul">
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@if($selayang)
<section class="py-5" style="background:#f2fbf8;">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <h4 class="fw-bold mb-3">Selayang Pandang</h4>
                <div class="text-muted">{!! $selayang->konten !!}</div>
            </div>
            <div class="col-lg-6">
                <div class="rounded-4 overflow-hidden shadow-sm">
                    <img src="{{ $selayang->gambar ? asset($selayang->gambar) : 'https://placehold.co/640x420?text=Selayang+Pandang' }}" class="img-fluid" alt="Selayang Pandang">
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@if($visiMisi)
@php
    $visiData = null;
    if ($visiMisi?->konten) {
        $decodedVisi = json_decode($visiMisi->konten, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedVisi)) {
            $visiData = [
                'visi' => $decodedVisi['visi'] ?? '',
                'misi' => $decodedVisi['misi'] ?? '',
            ];
        }
    }
@endphp
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-4">
            <span class="badge bg-light text-primary fw-semibold mb-2">Visi & Misi</span>
            <h3 class="fw-bold">Visi, Misi, dan Strategi Satpel PVP Bantul</h3>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Visi</h5>
                        <div class="text-muted">{!! $visiData['visi'] ?? $visiMisi->konten !!}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Misi</h5>
                        <div class="text-muted">{!! $visiData['misi'] ?? $visiMisi->konten !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<section class="py-5" style="background:#f0f8ff;">
    <div class="container">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">{{ $strukturProfile->judul ?? 'Struktur Organisasi' }}</h3>
            <p class="text-muted mb-0">{!! $strukturProfile->konten ?? 'Mewujudkan tata kelola Satpel PVP Bantul yang profesional & kolaboratif.' !!}</p>
        </div>
        @if($strukturProfile?->gambar)
            <div class="text-center mb-4">
                <img src="{{ asset($strukturProfile->gambar) }}" class="img-fluid rounded shadow-sm" alt="{{ $strukturProfile->judul }}" style="max-height:400px;object-fit:cover;">
            </div>
        @endif
        @php
            $renderTree = function($nodes) use (&$renderTree) {
                if ($nodes->isEmpty()) {
                    return '';
                }
                $html = '<ul class="list-unstyled ps-4">';
                foreach ($nodes as $node) {
                    $html .= '<li class="mb-3">';
                    $html .= '<div class="fw-semibold">'.e($node->nama).'</div>';
                    if ($node->jabatan) {
                        $html .= '<div class="text-muted small">'.e($node->jabatan).'</div>';
                    }
                    if ($node->children->count()) {
                        $html .= $renderTree($node->children);
                    }
                    $html .= '</li>';
                }
                $html .= '</ul>';
                return $html;
            };
        @endphp
        @if($structures->isEmpty())
            <div class="alert alert-info text-center">Belum ada data struktur organisasi.</div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    {!! $renderTree($structures) !!}
                </div>
            </div>
        @endif
    </div>
</section>

@if($denah)
@php
    $denahData = ['map' => '', 'subjudul' => 'Temukan lokasi workshop, asrama, area publik, dan fasilitas pendukung lainnya melalui denah terbaru kami.'];
    if ($denah?->konten) {
        $decodedDenah = json_decode($denah->konten, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedDenah)) {
            $denahData['map'] = $decodedDenah['map'] ?? '';
            $denahData['subjudul'] = $decodedDenah['subjudul'] ?? $denahData['subjudul'];
        } else {
            $denahData['map'] = $denah->konten;
        }
    }
@endphp
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-5">
                <h3 class="fw-bold mb-3">{{ $denah->judul ?? 'Denah Lokasi Satpel PVP Bantul' }}</h3>
                <p class="text-muted">{{ $denahData['subjudul'] }}</p>
            </div>
            <div class="col-lg-7">
                @php
                    $denahEmbed = trim($denahData['map'] ?? '');
                @endphp
                @if($denahEmbed)
                    <div class="map-embed">
                        @if(\Illuminate\Support\Str::contains($denahEmbed, '<iframe'))
                            {!! $denahEmbed !!}
                        @else
                            <iframe src="{{ $denahEmbed }}" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
                        @endif
                    </div>
                @elseif($denah->gambar)
                    <div class="rounded-4 overflow-hidden shadow-sm">
                        <img src="{{ asset($denah->gambar) }}" class="img-fluid" alt="Denah BPVP">
                    </div>
                @else
                    <div class="alert alert-info">Belum ada peta yang ditambahkan.</div>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

<section class="py-5" style="background:#f8fafc;">
    <div class="container">
        <div class="text-center mb-4">
            <h3 class="fw-bold">Fasilitas & Aktivitas</h3>
            <p class="text-muted">Cuplikan kegiatan dan fasilitas penunjang pelatihan di Satpel PVP Bantul.</p>
        </div>
        <div class="row g-4">
            @forelse($galeris as $foto)
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="{{ asset($foto->gambar) }}" class="card-img-top" alt="{{ $foto->judul }}">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-1">{{ $foto->judul }}</h6>
                            <p class="text-muted small mb-0">{{ \Illuminate\Support\Str::limit($foto->deskripsi, 80) }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted">Belum ada dokumentasi galeri.</div>
            @endforelse
        </div>
    </div>
</section>

<section class="py-5" style="background:#e9f6f3;">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h3 class="fw-bold text-primary mb-2">Anda siap tingkatkan skill bersama kami?</h3>
                <p class="text-muted mb-0">Segera eksplorasi pilihan pelatihan, fasilitas, dan layanan sertifikasi di Satpel PVP Bantul.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('pelatihan.katalog') }}" class="btn btn-primary rounded-pill px-4 me-2">Lihat Pelatihan</a>
                <a href="{{ route('sertifikasi') }}" class="btn btn-outline-primary rounded-pill px-4">Sertifikasi</a>
            </div>
        </div>
    </div>
</section>
@endsection
