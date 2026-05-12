@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Laporan Progress Peserta</h4>
        <small class="text-muted">Kehadiran, submission, dan nilai rata-rata per peserta dalam kelas.</small>
    </div>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <label class="form-label mb-1">Kelas</label>
                <select name="class_id" class="form-select form-select-sm" required>
                    <option value="">Pilih Kelas</option>
                    @foreach($classes as $id => $title)
                        <option value="{{ $id }}" @selected(request('class_id', $classFilter) == $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">Terapkan</button>
            </div>
            @if(request('class_id'))
                <div class="col-auto">
                    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-progress.index' : 'admin.course-progress.index')) }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </form>
    </div>
</div>

@if($selectedClass)
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h6 class="mb-3">Kelas: {{ $selectedClass->title }}</h6>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Peserta</th>
                            <th>Kehadiran</th>
                            <th>Submission</th>
                            <th>Nilai Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $row)
                            <tr>
                                <td>{{ $row['user']->name ?? $row['user']->email ?? '-' }}</td>
                                <td>
                                    @if($row['attendance_rate'] !== null)
                                        {{ $row['attended'] }}/{{ $row['total_sessions'] }} ({{ $row['attendance_rate'] }}%)
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $row['submitted'] }} terkumpul • {{ $row['graded'] }} dinilai</td>
                                <td>{{ $row['avg_score'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">Belum ada peserta terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
