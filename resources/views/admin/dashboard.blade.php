@extends('layouts.admin')

@section('content')
@if(isset($trainingMetrics))
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">Ringkasan LMS</h5>
                <small class="text-muted">Snapshot cepat untuk kelas dan penilaian.</small>
            </div>
            <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-class.index' : 'admin.course-class.index')) }}" class="btn btn-sm btn-outline-primary">Kelola Kelas</a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Total Kelas</div>
                        <div class="fs-4 fw-bold">{{ $trainingMetrics['classes_total'] ?? 0 }}</div>
                        <small class="text-muted">Aktif: {{ $trainingMetrics['classes_active'] ?? 0 }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Peserta Aktif</div>
                        <div class="fs-4 fw-bold">{{ $trainingMetrics['enrollments_active'] ?? 0 }}</div>
                        <small class="text-muted">Pending admin: {{ $trainingMetrics['enrollments_pending'] ?? 0 }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Submission Menunggu</div>
                        <div class="fs-4 fw-bold">{{ $trainingMetrics['pending_submissions'] ?? 0 }}</div>
                        <small class="text-muted">Butuh penilaian</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Sesi Terdekat</div>
                        @if(!empty($upcomingSessions) && $upcomingSessions->isNotEmpty())
                            <div class="fw-semibold">{{ $upcomingSessions->first()->course->title ?? 'Sesi' }}</div>
                            <small class="text-muted">{{ $upcomingSessions->first()->start_at?->format('d M Y H:i') }}</small>
                        @else
                            <div class="text-muted">Belum ada jadwal</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="row g-3 mb-4 dashboard-metrics row-cols-1 row-cols-md-2 row-cols-xl-3">
    @php
        $metricCards = [
            ['label' => 'Total Berita', 'value' => $metrics['berita'], 'icon' => 'fa-newspaper', 'theme' => 'primary'],
            ['label' => 'Program Pelatihan', 'value' => $metrics['program'], 'icon' => 'fa-graduation-cap', 'theme' => 'success'],
            ['label' => 'Pengumuman Aktif', 'value' => $metrics['pengumuman'], 'icon' => 'fa-bullhorn', 'theme' => 'warning'],
            ['label' => 'Pesan Masuk', 'value' => $metrics['pesan'], 'icon' => 'fa-envelope-open-text', 'theme' => 'danger', 'subtext' => $pesanUnread . ' belum diproses'],
            ['label' => 'Total Kunjungan', 'value' => number_format($metrics['visits']), 'icon' => 'fa-chart-line', 'theme' => 'info', 'subtext' => '7 hari terakhir: ' . $visitTrend->sum('total')],
            ['label' => 'Data Tracer Alumni', 'value' => $metrics['alumniTracer'], 'icon' => 'fa-user-graduate', 'theme' => 'secondary', 'subtext' => 'Perbarui real-time dari form tracer'],
        ];
    @endphp
    @foreach($metricCards as $card)
        <div class="col">
            <div class="card border-0 h-100 dashboard-card">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">{{ $card['label'] }}</p>
                        <h4 class="fw-bold mb-0">{{ $card['value'] }}</h4>
                        @isset($card['subtext'])
                            <small class="text-muted d-block">{{ $card['subtext'] }}</small>
                        @endisset
                    </div>
                    <span class="badge bg-{{ $card['theme'] }}-subtle text-{{ $card['theme'] }} p-3 rounded-circle">
                        <i class="fas {{ $card['icon'] }}"></i>
                    </span>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($latestBrandingKpi)
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-0 d-flex justify-content-between flex-wrap gap-2 align-items-center">
            <div>
                <h5 class="mb-0">Kinerja KPI Branding</h5>
                <small class="text-muted">Periode {{ \Carbon\Carbon::create($latestBrandingKpi->year, $latestBrandingKpi->month)->translatedFormat('F Y') }}</small>
            </div>
            <a href="{{ route('admin.branding-kpi.index') }}" class="btn btn-sm btn-outline-primary">Kelola KPI</a>
        </div>
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
                @foreach($brandingIndicators as $key => $indicator)
                    @php
                        $current = $latestBrandingKpi->{$key . '_current'};
                        $target = $latestBrandingKpi->{$key . '_target'};
                        $percentage = $target ? round(($current / $target) * 100, 1) : null;
                        $state = $percentage >= 100 ? 'success' : ($percentage >= 80 ? 'warning' : 'danger');
                    @endphp
                    <div class="col">
                        <div class="border rounded-3 h-100 p-3">
                            <p class="text-muted text-uppercase small mb-1">{{ $indicator['category'] }}</p>
                            <h6 class="fw-semibold mb-2">{{ $indicator['label'] }}</h6>
                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <div class="fs-3 fw-bold">{{ $current ?? '0' }}</div>
                                    <small class="text-muted">Target: {{ $target ?? 'Belum diisi' }}</small>
                                </div>
                                <span class="badge bg-{{ $state }}-subtle text-{{ $state }}">{{ $percentage ? $percentage.'%' : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow border-0 h-100">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Tren Berita Minggu Ini</h5>
                    <small class="text-muted">Jumlah artikel yang dipublikasikan per hari</small>
                </div>
            </div>
            <div class="card-body">
                <canvas id="newsTrendChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow border-0 h-100">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Analisis Cohort Alumni</h5>
                    <small class="text-muted">Distribusi status per tahun kelulusan</small>
                </div>
            </div>
            <div class="card-body">
                <canvas id="cohortChart" height="140"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-8">
        <div class="card shadow border-0 h-100">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">Funnel Layanan 30 Hari</h5>
                <small class="text-muted">Perjalanan pengguna dari kunjungan hingga tracer</small>
            </div>
            <div class="card-body">
                <canvas id="funnelChart" height="220"></canvas>
                <ul class="mt-3 list-unstyled small">
                    @php $previous = null; @endphp
                    @foreach($funnelStages as $label => $value)
                        @php
                            $conversion = $previous && $previous > 0 ? round(($value / $previous) * 100, 1) : null;
                            $previous = $value ?: $previous;
                        @endphp
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span>{{ $label }}</span>
                            <span class="fw-semibold">{{ number_format($value) }} @if(!is_null($conversion))<small class="text-muted">({{ $conversion }}%)</small>@endif</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow border-0 h-100">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Aktivitas Terbaru</h5>
                    <small class="text-muted">5 aktivitas terakhir oleh admin</small>
                </div>
                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($recentActivities as $activity)
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold text-primary">{{ $activity->action }}</div>
                                <small class="text-muted">{{ $activity->description ?? '-' }}</small>
                            </div>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</small>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">Belum ada aktivitas.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-4">
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">Ringkasan Konten</h5>
                <small class="text-muted">Data cepat untuk modul utama</small>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Lowongan Aktif
                        <span class="badge bg-primary rounded-pill">{{ $metrics['lowongan'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Program Pelatihan
                        <span class="badge bg-success rounded-pill">{{ $metrics['program'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Pengumuman
                        <span class="badge bg-warning rounded-pill">{{ $metrics['pengumuman'] }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow border-0">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">Selamat Datang Kembali!</h5>
                <p class="text-muted mb-4">Gunakan panel ini untuk memantau kinerja portal dan menindaklanjuti pesan/publikasi terbaru.</p>
                <a href="{{ route('admin.berita.create') }}" class="btn btn-primary w-100 mb-2">Buat Berita Baru</a>
                <a href="{{ route('admin.pesan.index') }}" class="btn btn-outline-secondary w-100">Tinjau Pesan Masuk</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('newsTrendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($trendLabels->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))),
                datasets: [{
                    label: 'Berita',
                    data: @json($beritaSeries),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.15)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Kunjungan',
                    data: @json($visitSeries),
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.15)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                plugins: { legend: { display: true } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
        const cohortCtx = document.getElementById('cohortChart').getContext('2d');
        new Chart(cohortCtx, {
            type: 'bar',
            data: {
                labels: @json($cohortYears->map(fn($year) => (string) $year)->values()),
                datasets: [
                    @php
                        $statusColors = [
                            'employed' => 'rgba(37, 99, 235, 0.8)',
                            'entrepreneur' => 'rgba(16, 185, 129, 0.8)',
                            'studying' => 'rgba(249, 115, 22, 0.8)',
                            'seeking' => 'rgba(239, 68, 68, 0.8)',
                            'other' => 'rgba(107, 114, 128, 0.8)',
                        ];
                    @endphp
                    @foreach($cohortStatusMap as $key => $label)
                        {
                            label: '{{ $label }}',
                            data: @json($cohortDatasets[$key]->values()),
                            backgroundColor: '{{ $statusColors[$key] ?? 'rgba(99,102,241,0.8)' }}',
                            stack: 'cohort'
                        },
                    @endforeach
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, beginAtZero: true }
                }
            }
        });

        const funnelCtx = document.getElementById('funnelChart').getContext('2d');
        new Chart(funnelCtx, {
            type: 'bar',
            data: {
                labels: @json(array_keys($funnelStages)),
                datasets: [{
                    label: 'Total',
                    data: @json(array_values($funnelStages)),
                    backgroundColor: 'rgba(56, 189, 248, 0.8)'
                }]
            },
            options: {
                indexAxis: 'y',
                scales: {
                    x: { beginAtZero: true, suggestedMax: {{ $funnelMax }} }
                },
                plugins: { legend: { display: false } }
            }
        });
    </script>
@endpush
@endsection
