@extends('layouts.app')

@section('content')
@push('styles')
<style>
    .schedule-hero{
        background:linear-gradient(135deg, rgba(7,60,108,.92), rgba(7,108,103,.85));
        border-bottom-left-radius:48px;
        border-bottom-right-radius:48px;
        padding:4rem 0;
        color:#fff;
    }
    .schedule-hero-card{
        border-radius:32px;
        background:rgba(255,255,255,.08);
        border:1px solid rgba(255,255,255,.2);
        padding:3rem;
        min-height:360px;
        box-shadow:0 40px 110px -60px rgba(0,0,0,.6);
    }
    .schedule-hero-card p{color:rgba(255,255,255,.75);}
    .schedule-hero-image{
        height:360px;
        border-radius:32px;
        overflow:hidden;
        box-shadow:0 35px 80px -55px rgba(0,0,0,.5);
    }
    .schedule-hero-image img{
        width:100%;
        height:100%;
        object-fit:cover;
    }
</style>
@endpush
@php
    $monthsOrder = [
        'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'
    ];
    $grouped = $schedules->groupBy('bulan')->sortBy(function($_, $k) use ($monthsOrder) {
        $idx = array_search($k, $monthsOrder);
        return $idx === false ? 99 : $idx;
    });
@endphp

<section class="schedule-hero">
    <div class="container">
        <div class="row align-items-stretch g-4">
            <div class="col-lg-8">
                <div class="schedule-hero-card h-100">
                    <span class="badge bg-white text-primary fw-semibold mb-3 shadow-sm">Kalender</span>
                    <h1 class="fw-bold text-white mb-2">Jadwal Pelatihan</h1>
                    <p>Jadwal Program Pelatihan Berbasis Kompetensi Satpel PVP Bantul {{ date('Y') }}</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="#jadwal" class="btn btn-outline-light rounded-pill px-4">Lihat Jadwal</a>
                        <a href="{{ route('pelatihan.katalog') }}" class="btn btn-outline-light rounded-pill px-4">Katalog Pelatihan</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="schedule-hero-image">
                    <img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1200&q=70" alt="Jadwal Pelatihan">
                </div>
            </div>
        </div>
    </div>
</section>

<section id="jadwal" class="py-5">
    <div class="container">
    <div class="text-muted mb-4">Berikut jadwal pelatihan, lokasi, dan penyelenggara. Klik tombol daftar melalui portal Satpel.</div>
        @forelse($grouped as $bulan => $items)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-teal text-white fw-bold" style="background:#0f7b7b;">
                    {{ $bulan ?? 'Jadwal' }} @if($items->first()->tahun) - {{ $items->first()->tahun }} @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Pelatihan</th>
                                    <th>Penyelenggara</th>
                                    <th>Lokasi</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th>Kuota</th>
                                    <th>Daftar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $row)
                                <tr>
                                    <td class="fw-semibold">{{ $row->judul }}</td>
                                    <td>{{ $row->penyelenggara }}</td>
                                    <td>{{ $row->lokasi }}</td>
                                    <td>{{ $row->mulai ? $row->mulai->format('d M Y') : '-' }}</td>
                                    <td>{{ $row->selesai ? $row->selesai->format('d M Y') : '-' }}</td>
                                    <td>{{ $row->kuota }}</td>
                                    <td>
                                        @auth
                                            @php
                                                $isEnrolled = in_array($row->id, $enrolledScheduleIds ?? []);
                                            @endphp
                                            @if($isEnrolled)
                                                <span class="badge bg-success">Terdaftar</span>
                                            @else
                                                <form action="{{ route('training.register', $row->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-primary rounded-pill">Daftar di Portal</button>
                                                </form>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary rounded-pill">Login untuk daftar</a>
                                        @endauth
                                    </td>
                                </tr>
                                @if($row->catatan)
                                <tr>
                                    <td colspan="7" class="text-muted small ps-4">{{ $row->catatan }}</td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">Belum ada jadwal pelatihan.</div>
        @endforelse
    </div>
</section>

@include('components.announcement-widget', [
    'announcements' => $announcementWidget ?? collect(),
    'title' => 'Pengumuman Jadwal',
    'subtitle' => 'Perhatikan perubahan jadwal atau persyaratan terbaru.'
])
@endsection
