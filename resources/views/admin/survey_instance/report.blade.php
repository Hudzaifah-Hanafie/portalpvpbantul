@extends('layouts.admin')

@section('content')
@php
    $filterQuery = http_build_query([
        'submitted_from' => $submittedFrom,
        'submitted_to' => $submittedTo,
    ]);
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Laporan Survei Instans</h4>
        <small class="text-muted">{{ $survey_instance->survey->title ?? '-' }} | {{ $survey_instance->course->title ?? '-' }} | {{ $survey_instance->instructor->name ?? '-' }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.survey-instance.export.aggregates', $survey_instance->id) . ($filterQuery ? '?' . $filterQuery : '') }}" class="btn btn-outline-primary btn-sm">Ekspor Agregat (CSV)</a>
        <a href="{{ route('admin.survey-instance.export.responses', $survey_instance->id) . ($filterQuery ? '?' . $filterQuery : '') }}" class="btn btn-outline-primary btn-sm">Ekspor Respons (CSV)</a>
        <a href="{{ route('admin.survey-instance.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>
</div>

<form method="GET" class="card shadow-sm border-0 mb-3">
    <div class="card-body row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label mb-1">Dari Tanggal</label>
            <input type="date" name="submitted_from" value="{{ $submittedFrom }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
            <label class="form-label mb-1">Sampai Tanggal</label>
            <input type="date" name="submitted_to" value="{{ $submittedTo }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
            <label class="form-label mb-1"> </label>
            <div>
                <button class="btn btn-sm btn-outline-primary">Terapkan Filter</button>
                @if($filterQuery)
                    <a href="{{ route('admin.survey-instance.report', $survey_instance->id) }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                @endif
            </div>
        </div>
        <div class="col-md-3 text-muted small">
            Filter tanggal akan diterapkan ke statistik, daftar respons, dan ekspor.
        </div>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Status</div>
                <div class="fw-bold">{{ \App\Models\SurveyInstance::statuses()[$survey_instance->status] ?? $survey_instance->status }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Respons</div>
                <div class="fw-bold">{{ $totalResponses }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Rata-rata Numerik</div>
                <div class="fw-bold">{{ $canShowAnalytics && $avgNumeric ? number_format($avgNumeric, 2) : '-' }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Ambang Anonimitas</div>
                <div class="fw-bold">{{ $survey_instance->min_responses_threshold }} respons</div>
            </div>
        </div>
    </div>
</div>

@if(!$canShowAnalytics)
    <div class="alert alert-warning small">
        Detail analitik disembunyikan hingga minimal {{ $minResponses }} respons tercapai. Saat ini: {{ $totalResponses }} respons.
    </div>
@endif

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Ringkasan per Pertanyaan</h6>
            <span class="text-muted small">{{ $questionStats->count() }} pertanyaan</span>
        </div>
        @if(!$canShowAnalytics)
            <div class="text-muted small">Menunggu batas minimal respons sebelum menampilkan ringkasan per-pertanyaan.</div>
        @elseif($questionStats->isEmpty())
            <div class="text-muted small">Belum ada data.</div>
        @else
            @foreach($questionStats as $stat)
                <div class="mb-4 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold">{{ $stat['question']->question }}</div>
                        <div class="text-muted small">{{ ucfirst(str_replace('_', ' ', $stat['type'])) }} • {{ $stat['total'] }} respons</div>
                    </div>

                    @if(!empty($stat['distribution']))
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-2">
                                <thead class="table-light">
                                    <tr>
                                        <th>Opsi</th>
                                        <th class="text-end">Respons</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stat['distribution'] as $row)
                                        <tr>
                                            <td>{{ $row['label'] }}</td>
                                            <td class="text-end">{{ $row['count'] }} ({{ number_format($row['percent'], 1) }}%)</td>
                                        </tr>
                                    @endforeach
                                    @if(($stat['other_count'] ?? 0) > 0)
                                        <tr>
                                            <td>Lainnya / jawaban bebas</td>
                                            <td class="text-end">{{ $stat['other_count'] }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @elseif(isset($stat['scale']))
                        <div class="d-flex gap-3 small">
                            <span>Rata-rata: <strong>{{ $stat['scale']['avg'] ? number_format($stat['scale']['avg'], 2) : '-' }}</strong></span>
                            <span>Min: <strong>{{ $stat['scale']['min'] ?? '-' }}</strong></span>
                            <span>Maks: <strong>{{ $stat['scale']['max'] ?? '-' }}</strong></span>
                            <span>Total nilai: <strong>{{ $stat['scale']['count'] }}</strong></span>
                        </div>
                    @elseif(!empty($stat['top_words']) && $stat['top_words'] instanceof \Illuminate\Support\Collection)
                        <div class="small text-muted mb-1">Top kata:</div>
                        <ul class="mb-0">
                            @foreach($stat['top_words'] as $word => $count)
                                <li class="small"><strong>{{ $word }}</strong> — {{ $count }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-muted small">Belum ada ringkasan untuk tipe ini.</div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <h6 class="mb-3">Top Kata (indikatif)</h6>
        @if(!$canShowAnalytics)
            <div class="text-muted small">Menunggu minimal {{ $minResponses }} respons.</div>
        @elseif($topWords->isEmpty())
            <div class="text-muted small">Belum ada data.</div>
        @else
            <ul class="mb-0">
                @foreach($topWords as $word => $count)
                    <li><strong>{{ $word }}</strong> — {{ $count }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <h6 class="mb-3">20 Respons Terbaru</h6>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Peserta</th>
                        <th>Dikirim</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($responses as $resp)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $resp->user->name ?? $resp->user_id ?? '-' }}</td>
                            <td>{{ $resp->submitted_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Belum ada respons.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
