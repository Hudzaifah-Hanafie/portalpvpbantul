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
    .ppid-hero {
        background:linear-gradient(135deg, rgba(7,60,108,.92), rgba(7,108,103,.85));
        border-bottom-left-radius:48px;
        border-bottom-right-radius:48px;
        padding:4rem 0;
        color:#fff;
    }
    .ppid-hero-card{
        border-radius:32px;
        overflow:hidden;
        background:rgba(255,255,255,.08);
        border:1px solid rgba(255,255,255,.2);
        box-shadow:0 40px 110px -60px rgba(0,0,0,.6);
        min-height:360px;
    }
    .ppid-hero-card img{width:100%;height:100%;object-fit:cover;border-radius:32px;}
    .ppid-hero-card .hero-text{padding:3rem;}
    .ppid-hero-card .hero-text p{color:rgba(255,255,255,.75);}
    .ppid-badge{
        background:rgba(255,255,255,.2);
        color:#fff;
        font-weight:600;
        letter-spacing:.1em;
        border-radius:999px;
        padding:.4rem 1.2rem;
        font-size:.75rem;
    }
    .ppid-highlight-card{
        border:1px solid #dce7f2;
        border-radius:20px;
        padding:1.5rem;
        min-height:140px;
        background:#fff;
        box-shadow:0 15px 50px -40px rgba(0,0,0,.4);
    }
    .ppid-highlight-card .icon{
        font-size:2rem;
        color:#0b6a6a;
        margin-bottom:.75rem;
    }
    .ppid-profile{
        border-radius:20px;
        background:#0b6a6a;
        color:#fff;
        padding:2rem;
        box-shadow:0 25px 60px -50px rgba(0,0,0,.5);
    }
</style>
@endpush

@section('content')
<section class="ppid-hero">
    <div class="container">
        <div class="ppid-hero-card row g-0 align-items-stretch">
            <div class="col-lg-7 hero-text">
                <span class="ppid-badge d-inline-block mb-3">PPID</span>
                <h1 class="display-5 fw-bold text-white mb-3">{{ $setting->hero_title ?? 'Profil PPID' }}</h1>
                <p class="mb-4">{{ $setting->hero_description ?? 'Pejabat Pengelola Informasi dan Dokumentasi Satpel PVP Bantul' }}</p>
                @if($setting->hero_button_text)
                    <a href="{{ $setting->hero_button_link ?? '#form' }}" class="btn btn-outline-light rounded-pill px-4">{{ $setting->hero_button_text }}</a>
                @endif
            </div>
            <div class="col-lg-5">
                <img src="{{ $heroImage }}" alt="PPID" class="h-100">
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4 mb-4">
            @forelse($highlights as $highlight)
                <div class="col-md-4">
                    <div class="ppid-highlight-card h-100">
                        @if($highlight->icon)
                            <div class="icon"><i class="{{ $highlight->icon }}"></i></div>
                        @endif
                        <h5 class="fw-bold">{{ $highlight->title }}</h5>
                        <p class="text-muted mb-0">{{ $highlight->description }}</p>
                    </div>
                </div>
            @empty
                <div class="col-12 text-muted">Belum ada highlight PPID.</div>
            @endforelse
        </div>

        <div class="ppid-profile mb-5">
            <h3 class="fw-bold mb-3">{{ $setting->profile_title ?? 'Profil PPID' }}</h3>
            <p class="mb-0">{{ $setting->profile_description ?? 'Pejabat Pengelola Informasi dan Dokumentasi (PPID) bertanggung jawab dalam pengelolaan informasi publik di lingkungan Satpel PVP Bantul.' }}</p>
        </div>

        <div id="form" class="bg-white rounded-4 shadow-sm p-4">
            <h4 class="fw-bold mb-2">{{ $setting->form_title ?? 'Permohonan Informasi Publik' }}</h4>
            <p class="text-muted mb-4">{{ $setting->form_description ?? 'Silakan isi formulir berikut untuk mengajukan permohonan informasi publik Satpel PVP Bantul.' }}</p>
            @if($setting->form_embed)
                @php
                    $embed = $setting->form_embed;
                    $isIframe = \Illuminate\Support\Str::contains($embed, '<iframe');
                @endphp
                <div class="ratio ratio-16x9 mb-4">
                    {!! \App\Support\HtmlSanitizer::cleanEmbed($isIframe ? $embed : '<iframe src="'.e($embed).'" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>') !!}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Periksa kembali data yang Anda isi:</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('ppid.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control bg-light" value="{{ old('nama') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nomor Identitas/KTP</label>
                        <input type="text" name="nomor_identitas" class="form-control bg-light" value="{{ old('nomor_identitas') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NPWP</label>
                        <input type="text" name="npwp" class="form-control bg-light" value="{{ old('npwp') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pekerjaan</label>
                        <input type="text" name="pekerjaan" class="form-control bg-light" value="{{ old('pekerjaan') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jenis Pemohon</label>
                        <select name="jenis_pemohon" class="form-select bg-light">
                            <option value="">Pilih Jenis Pemohon</option>
                            <option value="Perorangan" {{ old('jenis_pemohon') === 'Perorangan' ? 'selected' : '' }}>Perorangan</option>
                            <option value="Badan Hukum" {{ old('jenis_pemohon') === 'Badan Hukum' ? 'selected' : '' }}>Badan Hukum</option>
                            <option value="Lembaga" {{ old('jenis_pemohon') === 'Lembaga' ? 'selected' : '' }}>Lembaga</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" rows="2" class="form-control bg-light">{{ old('alamat') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">No HP</label>
                        <input type="text" name="no_hp" class="form-control bg-light" value="{{ old('no_hp') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control bg-light" value="{{ old('email') }}" required>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Informasi yang Dimohonkan</label>
                    <textarea name="informasi_dimohon" rows="3" class="form-control bg-light" required>{{ old('informasi_dimohon') }}</textarea>
                </div>
                <div class="mt-3">
                    <label class="form-label">Tujuan Penggunaan Informasi</label>
                    <textarea name="tujuan_penggunaan" rows="3" class="form-control bg-light">{{ old('tujuan_penggunaan') }}</textarea>
                </div>
                <div class="mt-3">
                    <label class="form-label">Cara Memperoleh Informasi</label>
                    <textarea name="cara_memperoleh" rows="3" class="form-control bg-light">{{ old('cara_memperoleh') }}</textarea>
                </div>
                <div class="mt-3">
                    <label class="form-label">Unggah Tanda Tangan (JPG/PNG/PDF)</label>
                    <input type="file" name="tanda_tangan" class="form-control bg-light" accept=".jpg,.jpeg,.png,.pdf">
                    <small class="text-muted">Format JPG/PNG/PDF dengan ukuran maksimal 2 MB.</small>
                </div>
                <div class="mt-4">
                    <label class="form-label">Verifikasi Keamanan</label>
                    <div class="d-flex gap-3 align-items-center">
                        <span class="fw-semibold">{{ $captchaQuestion ?? '' }}</span>
                        <input type="number" name="captcha_answer" class="form-control bg-light" style="max-width: 180px;" required>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button class="btn btn-success px-4 py-2">Ajukan Permohonan</button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
