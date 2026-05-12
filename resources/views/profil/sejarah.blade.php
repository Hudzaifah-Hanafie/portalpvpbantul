@extends('layouts.app')

@section('content')
<div class="bg-light py-5 mb-5 border-bottom">
    <div class="container text-center">
        <h2 class="fw-bold text-primary display-5">{{ $data->judul }}</h2>
        <p class="text-muted">Perjalanan dan Transformasi Satpel PVP Bantul</p>
    </div>
</div>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            @if($data->gambar)
                <div class="mb-4 text-center">
                    <img src="{{ asset($data->gambar) }}" class="img-fluid rounded border shadow-sm w-100" alt="Sejarah">
                    <small class="text-muted fst-italic mt-2 d-block">Dokumentasi Sejarah</small>
                </div>
            @endif

            <div class="bg-white p-4 p-md-5 shadow-sm rounded">
                <div class="content-body" style="text-align: justify; line-height: 1.8; color: #333;">
                    {!! \App\Support\HtmlSanitizer::clean($data->konten) !!}
                </div>
            </div>

            <div class="mt-5 text-center">
                <a href="{{ route('profil.visimisi') }}" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="fas fa-arrow-right me-2"></i> Lihat Visi Misi
                </a>
            </div>

        </div>
    </div>
</div>
@endsection
