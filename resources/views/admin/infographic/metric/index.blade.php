@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\InfographicMetric::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3>Metric Infografis</h3>
        <p class="text-muted mb-0">Data angka utama yang tampil di dashboard tiap tahun.</p>
    </div>
    <a href="{{ route('admin.infographic-metric.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah Metric</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-sm-4 col-md-3">
                <label class="form-label mb-1">Filter Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('status', $statusFilter ?? null) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">Terapkan</button>
            </div>
            @if(request('status'))
                <div class="col-auto">
                    <a href="{{ route('admin.infographic-metric.index') }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </form>
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tahun</th>
                    <th>Label</th>
                    <th>Nilai</th>
                    <th>Urutan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($metrics as $metric)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $metric->year->tahun ?? '-' }}</td>
                        <td>{{ $metric->label }}</td>
                        <td class="fw-semibold">{{ $metric->value }}</td>
                        <td>{{ $metric->urutan }}</td>
                        <td class="text-nowrap">
                            @php
                                $status = $metric->status ?? 'draft';
                                $badgeClass = [
                                    'draft' => 'bg-secondary',
                                    'pending' => 'bg-warning text-dark',
                                    'published' => 'bg-success',
                                ][$status] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $statusOptions[$status] ?? ucfirst($status) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.infographic-metric.edit', $metric->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.infographic-metric.destroy', $metric->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus metric ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada metric.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
