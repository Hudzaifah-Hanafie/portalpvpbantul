@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1">Detail KPI Branding</h3>
        <p class="text-muted mb-0">Periode {{ \Carbon\Carbon::create()->month($branding_kpi->month)->translatedFormat('F') }} {{ $branding_kpi->year }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.branding-kpi.download', $branding_kpi) }}" class="btn btn-outline-success"><i class="fas fa-download me-1"></i> Unduh</a>
        <a href="{{ route('admin.branding-kpi.edit', $branding_kpi) }}" class="btn btn-primary">Ubah</a>
        <a href="{{ route('admin.branding-kpi.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Tanggal Rapat</small>
                <div class="h5 mb-0">{{ optional($branding_kpi->meeting_date)->translatedFormat('d F Y') ?? '-' }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Disusun oleh</small>
                <div class="h5 mb-0">{{ $branding_kpi->reported_by ?? '-' }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Disetujui oleh</small>
                <div class="h5 mb-0">{{ $branding_kpi->approved_by ?? 'Koordinator PVP Bantul' }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Lampiran</small>
                <div class="h5 mb-0">
                    @if($branding_kpi->evidence_link)
                        <a href="{{ $branding_kpi->evidence_link }}" target="_blank">Lihat dokumen</a>
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $indicatorGroups = collect(config('branding_kpi.indicators'))->groupBy('category');
@endphp
@foreach($indicatorGroups as $category => $indicators)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 text-uppercase">{{ $category }}</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Indikator</th>
                        <th>Bulan Lalu</th>
                        <th>Bulan Ini</th>
                        <th>Target</th>
                        <th>Selisih</th>
                        <th>Pencapaian</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($indicators as $key => $indicator)
                        @php
                            $current = $branding_kpi->{$key . '_current'};
                            $previous = $branding_kpi->{$key . '_previous'};
                            $target = $branding_kpi->{$key . '_target'};
                            $difference = $branding_kpi->{$key . '_difference'};
                            $achievement = $branding_kpi->{$key . '_achievement'};
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $indicator['label'] }}</td>
                            <td>{{ $previous ?? '-' }}</td>
                            <td>{{ $current ?? '-' }}</td>
                            <td>{{ $target ?? '-' }}</td>
                            <td>{{ $difference ?? '-' }}</td>
                            <td>{{ $achievement ? number_format($achievement, 1) . '%' : '-' }}</td>
                            <td>{{ $branding_kpi->{$key . '_notes'} ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Evaluasi Kejuruan Sepi Peminat</h5>
    </div>
    <div class="card-body">
        @if($branding_kpi->lowInterestItems->isNotEmpty())
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Kejuruan</th>
                        <th>Masalah</th>
                        <th>Rencana Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branding_kpi->lowInterestItems as $item)
                        <tr>
                            <td>{{ $item->program }}</td>
                            <td>{{ $item->issue }}</td>
                            <td>{{ $item->action_plan }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="text-muted">Belum ada evaluasi kejuruan.</div>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h5 class="fw-bold mb-3">Catatan Rapat</h5>
        <p class="mb-0">{{ $branding_kpi->meeting_notes ?? 'Belum ada catatan tambahan.' }}</p>
    </div>
</div>
@endsection
