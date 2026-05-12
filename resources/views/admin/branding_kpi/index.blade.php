@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1">Laporan Branding KPI</h3>
        <p class="text-muted mb-0">Rekap manual monitoring branding setiap bulan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.branding-kpi.integrations') }}" class="btn btn-outline-primary">
            <i class="fas fa-plug me-1"></i> Integrasi & Sinkron
        </a>
        <a href="{{ route('admin.branding-kpi.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Laporan
        </a>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.branding-kpi.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Filter Tahun</label>
                <select name="year" class="form-select">
                    <option value="">Semua Tahun</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ $yearFilter == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Terapkan</button>
            </div>
        </form>
    </div>
</div>

@if($latestReport)
    <div class="row g-3 mb-4">
        @foreach(config('branding_kpi.indicators') as $key => $indicator)
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase">{{ $indicator['category'] }}</small>
                        <h6 class="fw-bold">{{ $indicator['label'] }}</h6>
                        @php
                            $current = $latestReport->{$key . '_current'};
                            $target = $latestReport->{$key . '_target'};
                            $achievement = $latestReport->{$key . '_achievement'};
                        @endphp
                        <div class="display-6">{{ $current ?? 'N/A' }}</div>
                        <div class="d-flex justify-content-between align-items-center text-muted small">
                            <span>Target: {{ $target ?? '-' }}</span>
                            @if(!is_null($achievement))
                                <span class="badge bg-success">{{ number_format($achievement, 1) }}%</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3">Tren 12 Bulan Terakhir</h5>
        <div style="height:340px">
        <canvas id="brandingKpiTrend"
            data-chart="{{ base64_encode(json_encode($chartData)) }}"></canvas>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Bulan</th>
                    @foreach($indicatorDefinitions as $indicator)
                        <th>{{ $indicator['label'] }}</th>
                    @endforeach
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::create()->month($report->month)->translatedFormat('F') }} {{ $report->year }}</td>
                        @foreach($indicatorDefinitions as $key => $indicator)
                            <td>{{ $report->{$key . '_current'} ?? '-' }}</td>
                        @endforeach
                        <td class="d-flex gap-2">
                            <a href="{{ route('admin.branding-kpi.show', $report) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.branding-kpi.edit', $report) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('admin.branding-kpi.download', $report) }}" class="btn btn-sm btn-outline-success"><i class="fas fa-download"></i></a>
                            <form action="{{ route('admin.branding-kpi.destroy', $report) }}" method="POST" onsubmit="return confirm('Hapus laporan ini?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Belum ada laporan.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">
            {{ $reports->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('brandingKpiTrend');
    if (!canvas) return;

    const chartData = JSON.parse(atob(canvas.dataset.chart));
    if (!chartData || !chartData.labels) return;

    const ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: chartData.datasets,
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            }
        },
        plugins: [{
            id: 'fixResponsive',
            resize(chart, size) {
                chart.canvas.parentNode.style.height = size.height + 'px';
            }
        }]
    });
});
</script>
@endpush
@endsection
