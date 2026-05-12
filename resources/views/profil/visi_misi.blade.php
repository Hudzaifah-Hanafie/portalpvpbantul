@extends('layouts.app')

@section('content')
@php
    $visiDecoded = null;
    if ($data?->konten) {
        $json = json_decode($data->konten, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            $visiDecoded = [
                'visi' => $json['visi'] ?? '',
                'misi' => $json['misi'] ?? '',
            ];
        }
    }
@endphp
<div class="bg-primary py-5 text-white mb-5">
    <div class="container text-center">
        <h1 class="fw-bold">{{ $data->judul }}</h1>
        <p class="lead mb-0">Satpel PVP Bantul</p>
    </div>
</div>

<div class="container mb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $data->judul }}</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            @if($data->gambar)
                <div class="text-center mb-5">
                    <img src="{{ asset($data->gambar) }}" class="img-fluid rounded shadow-lg" alt="{{ $data->judul }}" style="max-height: 500px;">
                </div>
            @endif

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-5">
                    @if($visiDecoded)
                        <div class="mb-4">
                            <h3 class="fw-bold text-primary">Visi</h3>
                            <div class="text-muted" style="font-size: 1.1rem; line-height: 1.8;">
                                {!! \App\Support\HtmlSanitizer::clean($visiDecoded['visi']) !!}
                            </div>
                        </div>
                        <div>
                            <h3 class="fw-bold text-primary">Misi</h3>
                            <div class="text-muted" style="font-size: 1.1rem; line-height: 1.8;">
                                {!! \App\Support\HtmlSanitizer::clean($visiDecoded['misi']) !!}
                            </div>
                        </div>
                    @else
                        <div class="content-body" style="font-size: 1.1rem; line-height: 1.8;">
                            {!! \App\Support\HtmlSanitizer::clean($data->konten) !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
