@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1">Dasbor Tracer Alumni</h3>
        <p class="text-muted mb-0">Distribusi status pekerjaan dan pendidikan terakhir berdasarkan gender.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.alumni-tracer.index') }}" class="btn btn-outline-secondary btn-sm">Kembali ke Tabel</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="fw-bold">Status Pekerjaan berdasarkan Gender</h5>
                <small class="text-muted">Proporsi status pekerjaan alumni.</small>
                <canvas id="statusChart" height="220" class="mt-3"></canvas>
                <ul class="mt-3 list-unstyled small row row-cols-2 g-2">
                    @foreach($statusLabels as $key => $label)
                        <li class="col">{{ $label }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="fw-bold">Distribusi Pendidikan berdasarkan Gender</h5>
                <small class="text-muted">Perbandingan pendidikan terakhir Laki-laki vs Perempuan.</small>
                <canvas id="educationChart" height="220" class="mt-3"></canvas>
                <ul class="mt-3 list-unstyled small row row-cols-2 g-2">
                    @foreach($educationLabels as $key => $label)
                        <li class="col">{{ $label }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = @json($statusChart);
    const statusColors = ['#0ea5e9','#22c55e','#6366f1','#f59e0b','#ef4444','#14b8a6'];
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusData.displayLabels,
            datasets: [{
                data: statusData.data,
                backgroundColor: statusColors.slice(0, statusData.data.length),
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    const eduCtx = document.getElementById('educationChart').getContext('2d');
    const educationData = @json($educationChart);
    const genderPalette = ['#0ea5e9', '#8b5cf6', '#f97316', '#64748b'];
    const datasets = educationData.datasets.map((ds, idx) => ({
        label: ds.label,
        data: ds.data,
        backgroundColor: genderPalette[idx % genderPalette.length],
        borderWidth: 1,
    }));
    new Chart(eduCtx, {
        type: 'bar',
        data: {
            labels: educationData.displayLabels,
            datasets: datasets,
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endpush
