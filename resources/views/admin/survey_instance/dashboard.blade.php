@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Dasbor Survei Instans</h4>
        <small class="text-muted">Ringkasan respons, rata-rata, dan status anonimitas.</small>
    </div>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <label class="form-label mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected($statusFilter === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4">
                <label class="form-label mb-1">Survei</label>
                <select name="survey_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($surveys as $id => $title)
                        <option value="{{ $id }}" @selected($surveyFilter == $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">Terapkan</button>
            </div>
            @if($statusFilter || $surveyFilter)
                <div class="col-auto">
                    <a href="{{ route('admin.survey-instance.dashboard') }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Instans</div>
                <div class="fw-bold">{{ $totals['instances'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Total Respons</div>
                <div class="fw-bold">{{ $totals['responses'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Rata-rata Numerik</div>
                <div class="fw-bold">{{ $totals['avg_numeric'] ? number_format($totals['avg_numeric'], 2) : '-' }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Instans ≥ Ambang</div>
                <div class="fw-bold">{{ $totals['with_threshold'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <h6 class="mb-3">Detail Instans</h6>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Survei</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th>Respons</th>
                        <th>Rata-rata</th>
                        <th>Anonimitas</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($instances as $instance)
                        <tr>
                            <td>{{ $instance->survey->title ?? '-' }}</td>
                            <td>{{ $instance->course->title ?? '-' }}</td>
                            <td>{{ \App\Models\SurveyInstance::statuses()[$instance->status] ?? $instance->status }}</td>
                            <td>{{ $instance->responses_count ?? 0 }}</td>
                            <td>{{ $instance->avg_numeric ? number_format($instance->avg_numeric, 2) : '-' }}</td>
                            <td>
                                @php $ok = ($instance->responses_count ?? 0) >= ($instance->min_responses_threshold ?? 0); @endphp
                                <span class="badge {{ $ok ? 'bg-success' : 'bg-warning text-dark' }}">{{ $ok ? 'OK' : 'Belum' }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.survey-instance.report', $instance->id) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $instances->links() }}</div>
    </div>
</div>
@endsection
