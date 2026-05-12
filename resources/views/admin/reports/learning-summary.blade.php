@extends('layouts.admin')

@section('content')
@php
    $statusBadge = [
        'healthy' => 'bg-success',
        'warning' => 'bg-warning text-dark',
        'critical' => 'bg-danger',
        'upcoming' => 'bg-secondary',
    ];
    $statusLabel = [
        'healthy' => 'Stabil',
        'warning' => 'Perlu Atensi',
        'critical' => 'Kritis',
        'upcoming' => 'Belum Mulai',
    ];
    $phaseBadge = [
        'ongoing' => 'bg-primary-subtle text-primary',
        'completed' => 'bg-success-subtle text-success',
        'upcoming' => 'bg-secondary-subtle text-secondary',
    ];
    $phaseLabel = [
        'ongoing' => 'Berjalan',
        'completed' => 'Selesai',
        'upcoming' => 'Akan Datang',
    ];
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0">Monitoring Pelatihan</h4>
        <small class="text-muted">Pantau progres sesi, ketuntasan tugas, penilaian, dan risiko pelatihan dalam satu layar.</small>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.learning-summary') }}" class="row g-3 align-items-end">
            <div class="col-lg-3 col-md-6">
                <label class="form-label">Kelompokkan</label>
                <select name="group_by" class="form-select">
                    <option value="kejuruan" @selected($groupBy === 'kejuruan')>Kejuruan</option>
                    <option value="kelas" @selected($groupBy === 'kelas')>Kelas</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label">Kelas</label>
                <select name="class_id" class="form-select">
                    <option value="">Semua kelas</option>
                    @foreach($classes as $id => $label)
                        <option value="{{ $id }}" @selected((string) $classFilter === (string) $id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Fase</label>
                <select name="phase" class="form-select">
                    <option value="">Semua fase</option>
                    <option value="ongoing" @selected($phaseFilter === 'ongoing')>Berjalan</option>
                    <option value="completed" @selected($phaseFilter === 'completed')>Selesai</option>
                    <option value="upcoming" @selected($phaseFilter === 'upcoming')>Akan datang</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Status Monitoring</label>
                <select name="health" class="form-select">
                    <option value="">Semua status</option>
                    <option value="critical" @selected($healthFilter === 'critical')>Kritis</option>
                    <option value="warning" @selected($healthFilter === 'warning')>Perlu atensi</option>
                    <option value="healthy" @selected($healthFilter === 'healthy')>Stabil</option>
                    <option value="upcoming" @selected($healthFilter === 'upcoming')>Belum mulai</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-12 d-grid">
                <button class="btn btn-primary">Tampilkan</button>
            </div>
            @if($classFilter || $phaseFilter || $healthFilter || $groupBy !== 'kejuruan')
                <div class="col-12">
                    <a href="{{ route('admin.reports.learning-summary') }}" class="btn btn-link btn-sm text-decoration-none p-0">Atur Ulang Filter</a>
                </div>
            @endif
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Kelas Dipantau</div>
                <div class="h4 mb-0">{{ $overview['classes'] ?? 0 }}</div>
                <div class="small text-muted mt-1">
                    Berjalan {{ $overview['ongoing'] ?? 0 }} • Akan datang {{ $overview['upcoming'] ?? 0 }} • Selesai {{ $overview['completed'] ?? 0 }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Peserta Aktif</div>
                <div class="h4 mb-0">{{ $overview['participants'] ?? 0 }}</div>
                <div class="small text-muted mt-1">
                    Rata-rata nilai akhir: {{ isset($overview['avg_final']) && $overview['avg_final'] !== null ? number_format($overview['avg_final'], 2) : '-' }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Kehadiran</div>
                <div class="h4 mb-0">{{ isset($overview['avg_attendance']) && $overview['avg_attendance'] !== null ? number_format($overview['avg_attendance'], 2) . '%' : '-' }}</div>
                <div class="small text-muted mt-1">Rata-rata seluruh kelas</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Ketuntasan Tugas</div>
                <div class="h4 mb-0">{{ isset($overview['submission_completion']) && $overview['submission_completion'] !== null ? number_format($overview['submission_completion'], 2) . '%' : '-' }}</div>
                <div class="small text-muted mt-1">Submit / target</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Penilaian Tugas</div>
                <div class="h4 mb-0">{{ isset($overview['grading_completion']) && $overview['grading_completion'] !== null ? number_format($overview['grading_completion'], 2) . '%' : '-' }}</div>
                <div class="small text-muted mt-1">Sudah dinilai</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Status Kelas</div>
                <div class="small mt-1">
                    <span class="badge bg-danger me-1">Kritis: {{ $overview['critical'] ?? 0 }}</span>
                    <span class="badge bg-warning text-dark me-1">Atensi: {{ $overview['warning'] ?? 0 }}</span>
                    <span class="badge bg-success">Stabil: {{ $overview['healthy'] ?? 0 }}</span>
                </div>
                <div class="small text-muted mt-2">Tanpa dokumentasi: {{ $overview['without_docs'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

@if($alerts->isNotEmpty())
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Prioritas Tindak Lanjut</h6>
                <small class="text-muted">{{ $alerts->count() }} kelas perlu atensi</small>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kelas</th>
                            <th>Fase</th>
                            <th>Kehadiran</th>
                            <th>Ketuntasan</th>
                            <th>Penilaian</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alerts as $alert)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $alert['class_title'] }}</div>
                                    <div class="small text-muted">{{ $alert['kejuruan'] }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $phaseBadge[$alert['phase']] ?? 'bg-secondary' }}">
                                        {{ $phaseLabel[$alert['phase']] ?? ucfirst($alert['phase']) }}
                                    </span>
                                </td>
                                <td>{{ $alert['attendance_rate'] !== null ? number_format($alert['attendance_rate'], 2) . '%' : '-' }}</td>
                                <td>{{ $alert['submission_completion_rate'] !== null ? number_format($alert['submission_completion_rate'], 2) . '%' : '-' }}</td>
                                <td>{{ $alert['grading_completion_rate'] !== null ? number_format($alert['grading_completion_rate'], 2) . '%' : '-' }}</td>
                                <td>
                                    <span class="badge {{ $statusBadge[$alert['status']] ?? 'bg-secondary' }}">
                                        {{ $statusLabel[$alert['status']] ?? ucfirst($alert['status']) }}
                                    </span>
                                </td>
                                <td class="small">
                                    {{ collect($alert['notes'])->take(2)->implode(' ') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @if($groupBy === 'kejuruan')
            @if($rowsByKejuruan->isEmpty())
                <div class="text-center text-muted py-4">Belum ada data untuk filter yang dipilih.</div>
            @else
                <div class="accordion" id="learningSummaryAccordion">
                    @foreach($rowsByKejuruan as $kejuruan => $items)
                        @php
                            $groupId = 'kejuruan-summary-' . $loop->index;
                            $summaryRow = $summaryByKejuruan->get($kejuruan);
                        @endphp
                        <div class="accordion-item mb-2">
                            <h2 class="accordion-header" id="heading-{{ $groupId }}">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $groupId }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse-{{ $groupId }}">
                                    <div class="w-100">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold">{{ strtoupper($kejuruan) }}</span>
                                            <span class="badge bg-light text-muted">
                                                Kelas: {{ $summaryRow['classes'] ?? $items->count() }} • Peserta: {{ $summaryRow['participants'] ?? $items->sum('participants') }}
                                            </span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-3 small text-muted mt-1">
                                            <span>Akhir: {{ $summaryRow && $summaryRow['avg_final'] !== null ? number_format($summaryRow['avg_final'], 2) : '-' }}</span>
                                            <span>Kehadiran: {{ $summaryRow && $summaryRow['attendance_rate'] !== null ? number_format($summaryRow['attendance_rate'], 2) . '%' : '-' }}</span>
                                            <span>Progress sesi: {{ $summaryRow && $summaryRow['session_completion_rate'] !== null ? number_format($summaryRow['session_completion_rate'], 2) . '%' : '-' }}</span>
                                            <span>Ketuntasan: {{ $summaryRow && $summaryRow['submission_completion_rate'] !== null ? number_format($summaryRow['submission_completion_rate'], 2) . '%' : '-' }}</span>
                                            <span>Penilaian: {{ $summaryRow && $summaryRow['grading_completion_rate'] !== null ? number_format($summaryRow['grading_completion_rate'], 2) . '%' : '-' }}</span>
                                            <span>Dokumentasi: {{ $summaryRow && $summaryRow['doc_coverage_rate'] !== null ? number_format($summaryRow['doc_coverage_rate'], 2) . '%' : '-' }}</span>
                                            <span class="text-danger">Kritis: {{ $summaryRow['critical'] ?? 0 }}</span>
                                            <span class="text-warning">Atensi: {{ $summaryRow['warning'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse-{{ $groupId }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading-{{ $groupId }}" data-bs-parent="#learningSummaryAccordion">
                                <div class="accordion-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Kelas</th>
                                                    <th>Fase</th>
                                                    <th class="text-center">Peserta</th>
                                                    <th class="text-center">Nilai Akhir</th>
                                                    <th class="text-center">Kehadiran</th>
                                                    <th class="text-center">Sesi</th>
                                                    <th class="text-center">Ketuntasan</th>
                                                    <th class="text-center">Penilaian</th>
                                                    <th class="text-center">Dok.</th>
                                                    <th class="text-center">Kompeten/Belum</th>
                                                    <th class="text-center">Pending</th>
                                                    <th class="text-center">Status</th>
                                                    <th class="text-center">Rincian</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($items as $row)
                                                    @php
                                                        $detailId = $groupId . '-participants-' . $loop->index;
                                                        $avgFinal = $row['final_count'] > 0 ? $row['final_sum'] / $row['final_count'] : null;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $row['class_title'] }}</td>
                                                        <td>
                                                            <span class="badge {{ $phaseBadge[$row['phase']] ?? 'bg-secondary' }}">
                                                                {{ $phaseLabel[$row['phase']] ?? ucfirst($row['phase']) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">{{ $row['participants'] }}</td>
                                                        <td class="text-center">{{ $avgFinal !== null ? number_format($avgFinal, 2) : '-' }}</td>
                                                        <td class="text-center">{{ $row['attendance_rate'] !== null ? number_format($row['attendance_rate'], 2) . '%' : '-' }}</td>
                                                        <td class="text-center">
                                                            {{ $row['session_completed'] }}/{{ $row['session_total'] }}
                                                        </td>
                                                        <td class="text-center">{{ $row['submission_completion_rate'] !== null ? number_format($row['submission_completion_rate'], 2) . '%' : '-' }}</td>
                                                        <td class="text-center">{{ $row['grading_completion_rate'] !== null ? number_format($row['grading_completion_rate'], 2) . '%' : '-' }}</td>
                                                        <td class="text-center">
                                                            @if($row['documentation_total'] > 0)
                                                                <span class="badge bg-success">Ada</span>
                                                            @else
                                                                <span class="badge bg-danger">Kosong</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">{{ $row['lulus'] }}/{{ $row['tidak_lulus'] }}</td>
                                                        <td class="text-center">{{ $row['pending'] }}</td>
                                                        <td class="text-center">
                                                            <span class="badge {{ $statusBadge[$row['monitoring_status']] ?? 'bg-secondary' }}">
                                                                {{ $statusLabel[$row['monitoring_status']] ?? ucfirst($row['monitoring_status']) }}
                                                            </span>
                                                            @if(!empty($row['monitoring_notes']))
                                                                <div class="small text-muted mt-1">{{ $row['monitoring_notes'][0] }}</div>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $detailId }}" aria-expanded="false" aria-controls="{{ $detailId }}">
                                                                Detail
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="13" class="p-0">
                                                            <div class="collapse" id="{{ $detailId }}">
                                                                <div class="p-3">
                                                                    <div class="fw-semibold mb-2">Rincian Peserta ({{ $row['participants_detail']->count() }})</div>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm mb-0">
                                                                            <thead class="table-light">
                                                                                <tr>
                                                                                    <th>#</th>
                                                                                    <th>Nama</th>
                                                                                    <th>NIK</th>
                                                                                    <th>Status</th>
                                                                                    <th>Admin</th>
                                                                                    <th class="text-center">Pre</th>
                                                                                    <th class="text-center">Post</th>
                                                                                    <th class="text-center">Praktik</th>
                                                                                    <th class="text-center">Sikap</th>
                                                                                    <th class="text-center">Kehadiran (%)</th>
                                                                                    <th class="text-center">Nilai Akhir</th>
                                                                                    <th>Kompetensi</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @forelse($row['participants_detail'] as $detail)
                                                                                    <tr>
                                                                                        <td>{{ $loop->iteration }}</td>
                                                                                        <td>{{ $detail['name'] }}</td>
                                                                                        <td>{{ $detail['nik'] }}</td>
                                                                                        <td>{{ $detail['status'] }}</td>
                                                                                        <td>{{ $detail['admin_status'] }}</td>
                                                                                        <td class="text-center">{{ $detail['pre_test_score'] !== null ? number_format($detail['pre_test_score'], 2) : '-' }}</td>
                                                                                        <td class="text-center">{{ $detail['post_test_score'] !== null ? number_format($detail['post_test_score'], 2) : '-' }}</td>
                                                                                        <td class="text-center">{{ $detail['practice_score'] !== null ? number_format($detail['practice_score'], 2) : '-' }}</td>
                                                                                        <td class="text-center">{{ $detail['attitude_score'] !== null ? number_format($detail['attitude_score'], 2) : '-' }}</td>
                                                                                        <td class="text-center">{{ $detail['attendance_rate'] !== null ? number_format($detail['attendance_rate'], 2) : '-' }}</td>
                                                                                        <td class="text-center">{{ $detail['final_grade'] !== null ? number_format($detail['final_grade'], 2) : '-' }}</td>
                                                                                        <td>{{ $detail['competency_status'] ?? '-' }}</td>
                                                                                    </tr>
                                                                                @empty
                                                                                    <tr>
                                                                                        <td colspan="12" class="text-center text-muted">Belum ada peserta.</td>
                                                                                    </tr>
                                                                                @endforelse
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kelas</th>
                            <th>Fase</th>
                            <th class="text-center">Peserta</th>
                            <th class="text-center">Nilai Akhir</th>
                            <th class="text-center">Kehadiran</th>
                            <th class="text-center">Sesi</th>
                            <th class="text-center">Ketuntasan</th>
                            <th class="text-center">Penilaian</th>
                            <th class="text-center">Dok.</th>
                            <th class="text-center">Kompeten/Belum</th>
                            <th class="text-center">Pending</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Rincian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary as $row)
                            @php
                                $detailId = 'kelas-summary-' . $loop->index;
                            @endphp
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td>
                                    <span class="badge {{ $phaseBadge[$row['phase']] ?? 'bg-secondary' }}">
                                        {{ $phaseLabel[$row['phase']] ?? ucfirst($row['phase']) }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $row['participants'] }}</td>
                                <td class="text-center">{{ $row['avg_final'] !== null ? number_format($row['avg_final'], 2) : '-' }}</td>
                                <td class="text-center">{{ $row['attendance_rate'] !== null ? number_format($row['attendance_rate'], 2) . '%' : '-' }}</td>
                                <td class="text-center">{{ $row['session_completed'] }}/{{ $row['session_total'] }}</td>
                                <td class="text-center">{{ $row['submission_completion_rate'] !== null ? number_format($row['submission_completion_rate'], 2) . '%' : '-' }}</td>
                                <td class="text-center">{{ $row['grading_completion_rate'] !== null ? number_format($row['grading_completion_rate'], 2) . '%' : '-' }}</td>
                                <td class="text-center">
                                    @if($row['documentation_total'] > 0)
                                        <span class="badge bg-success">Ada</span>
                                    @else
                                        <span class="badge bg-danger">Kosong</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $row['lulus'] }}/{{ $row['tidak_lulus'] }}</td>
                                <td class="text-center">{{ $row['pending'] }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $statusBadge[$row['monitoring_status']] ?? 'bg-secondary' }}">
                                        {{ $statusLabel[$row['monitoring_status']] ?? ucfirst($row['monitoring_status']) }}
                                    </span>
                                    @if(!empty($row['monitoring_notes']))
                                        <div class="small text-muted mt-1">{{ $row['monitoring_notes'][0] }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $detailId }}" aria-expanded="false" aria-controls="{{ $detailId }}">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="13" class="p-0">
                                    <div class="collapse" id="{{ $detailId }}">
                                        <div class="p-3">
                                            <div class="fw-semibold mb-2">Rincian Peserta ({{ $row['participants_detail']->count() }})</div>
                                            <div class="table-responsive">
                                                <table class="table table-sm mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Nama</th>
                                                            <th>NIK</th>
                                                            <th>Status</th>
                                                            <th>Admin</th>
                                                            <th class="text-center">Pre</th>
                                                            <th class="text-center">Post</th>
                                                            <th class="text-center">Praktik</th>
                                                            <th class="text-center">Sikap</th>
                                                            <th class="text-center">Kehadiran (%)</th>
                                                            <th class="text-center">Nilai Akhir</th>
                                                            <th>Kompetensi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($row['participants_detail'] as $detail)
                                                            <tr>
                                                                <td>{{ $loop->iteration }}</td>
                                                                <td>{{ $detail['name'] }}</td>
                                                                <td>{{ $detail['nik'] }}</td>
                                                                <td>{{ $detail['status'] }}</td>
                                                                <td>{{ $detail['admin_status'] }}</td>
                                                                <td class="text-center">{{ $detail['pre_test_score'] !== null ? number_format($detail['pre_test_score'], 2) : '-' }}</td>
                                                                <td class="text-center">{{ $detail['post_test_score'] !== null ? number_format($detail['post_test_score'], 2) : '-' }}</td>
                                                                <td class="text-center">{{ $detail['practice_score'] !== null ? number_format($detail['practice_score'], 2) : '-' }}</td>
                                                                <td class="text-center">{{ $detail['attitude_score'] !== null ? number_format($detail['attitude_score'], 2) : '-' }}</td>
                                                                <td class="text-center">{{ $detail['attendance_rate'] !== null ? number_format($detail['attendance_rate'], 2) : '-' }}</td>
                                                                <td class="text-center">{{ $detail['final_grade'] !== null ? number_format($detail['final_grade'], 2) : '-' }}</td>
                                                                <td>{{ $detail['competency_status'] ?? '-' }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="12" class="text-center text-muted">Belum ada peserta.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center text-muted">Belum ada data untuk filter yang dipilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
