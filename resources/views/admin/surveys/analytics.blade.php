@extends('layouts.admin')

@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;
    $questionTypeLabel = [
        'short_text' => 'Jawaban singkat',
        'long_text' => 'Paragraf',
        'choice_single' => 'Pilihan ganda',
        'choice_multiple' => 'Checkbox',
        'dropdown' => 'Dropdown',
        'linear_scale' => 'Skala',
        'date' => 'Tanggal',
        'time' => 'Waktu',
    ];
    $sections = $survey->sections;
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Analitik Survei: {{ $survey->title }}</h4>
        <small class="text-muted">Pantau tren respons dan detail jawaban.</small>
    </div>
    <div class="btn-group">
        <a href="{{ route('admin.surveys.edit', $survey) }}" class="btn btn-outline-secondary btn-sm">Ubah Survei</a>
        <a href="{{ route('surveys.show', $survey) }}" class="btn btn-outline-dark btn-sm" target="_blank">Lihat Form</a>
        <a href="{{ route('admin.surveys.export', $survey) }}" class="btn btn-outline-primary btn-sm">Ekspor CSV</a>
        <a href="{{ route('admin.surveys.export-xlsx', $survey) }}" class="btn btn-outline-success btn-sm">Ekspor XLSX</a>
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>
    </div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="border rounded-3 p-3 h-100">
            <div class="text-muted small mb-1">Total Respons</div>
            <div class="fs-3 fw-bold">{{ $responsesCount }}</div>
            <small class="text-muted">Anonim: {{ $anonymousResponses }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="border rounded-3 p-3 h-100">
            <div class="text-muted small mb-1">Responden Masuk</div>
            <div class="fs-3 fw-bold">{{ $uniqueRespondents }}</div>
            <small class="text-muted">Kebutuhan login: {{ $survey->require_login ? 'Ya' : 'Opsional' }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="border rounded-3 p-3 h-100">
            <div class="text-muted small mb-1">Status Survei</div>
            <div class="fs-3 fw-bold">{{ $survey->isOpen() ? 'Terbuka' : 'Tutup' }}</div>
            <small class="text-muted">
                @if($survey->opens_at) Dibuka {{ $survey->opens_at->format('d M Y H:i') }} @else Tanpa jadwal @endif
                @if($survey->closes_at)<br>Tutup {{ $survey->closes_at->format('d M Y H:i') }}@endif
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="border rounded-3 p-3 h-100">
            <div class="text-muted small mb-1">Limit Respons</div>
            <div class="fs-3 fw-bold">{{ $survey->max_responses ?? 'Tidak dibatasi' }}</div>
            <small class="text-muted">Slug: /survei/{{ $survey->slug }}</small>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0">Respons per Hari</h6>
            <small class="text-muted">Pantau lonjakan pengisian form</small>
        </div>
        <form method="GET" class="d-flex gap-2 align-items-center">
            <input type="date" name="start" class="form-control form-control-sm" value="{{ request('start') }}">
            <input type="date" name="end" class="form-control form-control-sm" value="{{ request('end') }}">
            <button class="btn btn-sm btn-outline-primary">Filter</button>
        </form>
    </div>
    <div class="card-body">
        <canvas id="dailyResponsesChart" height="120"></canvas>
    </div>
</div>

@if($sections->count())
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0">
                <h6 class="mb-0">Completion per Section</h6>
                <small class="text-muted">Persentase respons yang menjawab pertanyaan di section</small>
            </div>
            <div class="card-body">
                <canvas id="sectionCompletionChart" height="140"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0">
                <h6 class="mb-0">Completion Rate</h6>
                <small class="text-muted">Perbandingan respons selesai vs total</small>
            </div>
            <div class="card-body">
                <canvas id="completionRateChart" height="140"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row g-3">
    @foreach($questionStats as $stat)
        <div class="col-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold">{{ $loop->iteration }}. {{ $stat['question']->question }}</div>
                            <small class="text-muted">Tipe: {{ $questionTypeLabel[$stat['question']->type] ?? Str::title(str_replace('_',' ', $stat['question']->type)) }} · {{ $stat['responses'] }} respons</small>
                        </div>
                        <span class="badge bg-light text-dark">{{ $stat['question']->is_required ? 'Wajib' : 'Opsional' }}</span>
                    </div>

                    <div class="mt-3">
                        @if(isset($stat['option_stats']))
                            <canvas class="question-chart" data-type="options" data-question="{{ $stat['question']->id }}"></canvas>
                        @elseif(isset($stat['scale']))
                            <div class="row g-2 mb-2">
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 h-100 text-center">
                                        <div class="text-muted small">Rata-rata</div>
                                        <div class="fs-4 fw-bold">{{ $stat['scale']['avg'] ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 h-100 text-center">
                                        <div class="text-muted small">Terendah</div>
                                        <div class="fs-4 fw-bold">{{ $stat['scale']['min'] ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 h-100 text-center">
                                        <div class="text-muted small">Tertinggi</div>
                                        <div class="fs-4 fw-bold">{{ $stat['scale']['max'] ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <canvas class="question-chart" data-type="scale" data-question="{{ $stat['question']->id }}"></canvas>
                        @else
                            <div class="text-muted small mb-1">Sampel jawaban</div>
                            @forelse($stat['samples'] ?? [] as $sample)
                                <div class="border rounded p-2 mb-2">{{ $sample }}</div>
                            @empty
                                <div class="text-muted fst-italic">Belum ada jawaban teks.</div>
                            @endforelse
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@php
    $questionStatsPayload = collect($questionStats)->map(function ($s) {
        return [
            'question_id' => $s['question']->id,
            'type' => $s['question']->type,
            'option_stats' => $s['option_stats'] ?? null,
            'scale' => $s['scale'] ?? null,
        ];
    })->values();
    $sectionCompletionPayload = $sections->map(function ($section) use ($survey) {
        $totalResponses = $survey->responses_count ?: $survey->responses()->count();
        $answered = $totalResponses
            ? \App\Models\SurveyAnswer::whereHas('question', fn($q)=>$q->where('survey_section_id', $section->id))
                ->distinct('survey_response_id')
                ->count('survey_response_id')
            : 0;
        $percent = $totalResponses ? round(($answered / $totalResponses) * 100, 1) : 0;
        return ['label' => $section->title, 'percent' => $percent];
    })->values();
@endphp

@push('scripts')
<script>
    const dailyCtx = document.getElementById('dailyResponsesChart').getContext('2d');
    const dailyData = {
        labels: @json($dailyResponses->map(fn($row) => Carbon::parse($row->date)->format('d M'))),
        datasets: [{
            label: 'Respons',
            data: @json($dailyResponses->pluck('total')),
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.2)',
            tension: 0.4,
            fill: true,
        }]
    };
    new Chart(dailyCtx, {
        type: 'line',
        data: dailyData,
        options: {
            plugins: { legend: { display: false }},
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    const questionStats = @json($questionStatsPayload);

    document.querySelectorAll('.question-chart').forEach((canvas) => {
        const qid = canvas.dataset.question;
        const stat = questionStats.find(s => s.question_id === qid);
        if (!stat) return;
        const ctx = canvas.getContext('2d');
        if (stat.option_stats) {
            const labels = stat.option_stats.map(o => o.label);
            const data = stat.option_stats.map(o => o.count);
            new Chart(ctx, {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Total', data, backgroundColor: '#2563eb' }] },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });
        } else if (stat.scale?.distribution) {
            const labels = stat.scale.distribution.map(d => d.value);
            const data = stat.scale.distribution.map(d => d.count);
            new Chart(ctx, {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Jumlah', data, backgroundColor: '#22c55e' }] },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });
        }
    });

    @if($sections->count())
    const sectionCompletion = @json($sectionCompletionPayload);
    const sectionCtx = document.getElementById('sectionCompletionChart').getContext('2d');
    new Chart(sectionCtx, {
        type: 'bar',
        data: {
            labels: sectionCompletion.map(s => s.label),
            datasets: [{ label:'% selesai', data: sectionCompletion.map(s => s.percent), backgroundColor:'#22c55e' }]
        },
        options: { plugins:{legend:{display:false}}, scales:{ y:{beginAtZero:true, max:100} } }
    });
    const completionRateCtx = document.getElementById('completionRateChart').getContext('2d');
    new Chart(completionRateCtx, {
        type: 'doughnut',
        data: {
            labels: ['Selesai', 'Belum'],
            datasets: [{
                data: [{{ $survey->responses_count ?? 0 }}, {{ max(0, ($survey->max_responses ?? $survey->responses_count) - ($survey->responses_count ?? 0)) }}],
                backgroundColor:['#2563eb','#e5e7eb']
            }]
        },
        options: { cutout:'60%', plugins:{legend:{position:'bottom'}} }
    });
    @endif
</script>
@endpush
