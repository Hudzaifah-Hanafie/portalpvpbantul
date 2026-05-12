@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\SurveyInstance::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Survei Instans</h4>
        <small class="text-muted">Hubungkan survei dinamis ke kelas/instruktur tertentu dengan jadwal buka/tutup terpisah.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.survey-instance.dashboard') }}" class="btn btn-outline-primary btn-sm">Dasbor Survei</a>
        <a href="{{ route('admin.survey-instance.create') }}" class="btn btn-primary btn-sm">Buat Instans</a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-sm-3">
                <label class="form-label mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('status', $statusFilter ?? null) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label mb-1">Survei</label>
                <select name="survey_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($surveys as $id => $title)
                        <option value="{{ $id }}" @selected(request('survey_id', $surveyFilter ?? null) == $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label mb-1">Kelas</label>
                <select name="class_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($classes as $id => $title)
                        <option value="{{ $id }}" @selected(request('class_id', $classFilter ?? null) == $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">Terapkan</button>
            </div>
            @if(request('status') || request('survey_id') || request('class_id'))
                <div class="col-auto">
                    <a href="{{ route('admin.survey-instance.index') }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Survei</th>
                        <th>Kelas</th>
                        <th>Instruktur</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($instances as $instance)
                        <tr>
                            <td>{{ $instances->firstItem() + $loop->index }}</td>
                            <td>{{ $instance->survey->title ?? '-' }}</td>
                            <td>{{ $instance->course->title ?? '-' }}</td>
                            <td>{{ $instance->instructor->name ?? '-' }}</td>
                            <td class="small">
                                {{ $instance->opens_at?->format('d M Y') ?? '-' }} s/d {{ $instance->closes_at?->format('d M Y') ?? '-' }}
                            </td>
                            <td>
                                @php
                                    $badgeClass = [
                                        'draft' => 'bg-secondary',
                                        'open' => 'bg-success',
                                        'closed' => 'bg-dark',
                                    ][$instance->status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $statusOptions[$instance->status] ?? $instance->status }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.survey-instance.report', $instance->id) }}" class="btn btn-sm btn-outline-primary">Laporan</a>
                                <a href="{{ route('admin.survey-instance.edit', $instance->id) }}" class="btn btn-sm btn-warning">Ubah</a>
                                <form action="{{ route('admin.survey-instance.destroy', $instance->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus instans ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada instans survei.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $instances->links() }}
        </div>
    </div>
</div>
@endsection
