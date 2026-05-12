@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\CourseAttendance::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Presensi Peserta</h4>
        <small class="text-muted">Kelola kehadiran per sesi.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-attendance.create' : 'admin.course-attendance.create')) }}" class="btn btn-primary btn-sm">Tambah Presensi</a>
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
                <label class="form-label mb-1">Kelas</label>
                <select name="class_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($classes as $id => $title)
                        <option value="{{ $id }}" @selected(request('class_id', $classFilter ?? null) === $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label mb-1">Sesi</label>
                <select name="session_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($sessions as $id => $title)
                        <option value="{{ $id }}" @selected(request('session_id', $sessionFilter ?? null) === $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">Terapkan</button>
            </div>
            @if(request('status') || request('class_id') || request('session_id'))
                <div class="col-auto">
                    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-attendance.index' : 'admin.course-attendance.index')) }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
            <div class="col-auto ms-auto">
                <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-attendance.export.csv' : 'admin.course-attendance.export.csv'), request()->all()) }}" class="btn btn-sm btn-outline-primary">Ekspor CSV</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Peserta</th>
                        <th>Kelas / Sesi</th>
                        <th>Status</th>
                        <th>Dicatat</th>
                        <th>Checked</th>
                        <th>Bukti</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $att)
                        <tr>
                            <td>{{ $attendances->firstItem() + $loop->index }}</td>
                            <td>{{ $att->user->name ?? $att->user_id }}</td>
                            <td>
                                <div class="small fw-bold">{{ $att->session->course->title ?? '-' }}</div>
                                <div class="small text-muted">{{ $att->session->title ?? '-' }}</div>
                            </td>
                            <td>
                                @php
                                    $badgeClass = [
                                        'hadir' => 'bg-success',
                                        'telat' => 'bg-warning text-dark',
                                        'izin' => 'bg-info text-dark',
                                        'absen' => 'bg-danger',
                                    ][$att->status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $statusOptions[$att->status] ?? $att->status }}</span>
                            </td>
                            <td class="small text-muted">
                                {{ $att->recorded_source === 'operator' ? 'Operator' : 'Peserta' }}
                                @if($att->recorded_by && $att->recorded_source === 'operator')
                                    <div>{{ $att->recorded_by }}</div>
                                @endif
                            </td>
                            <td class="small">
                                {{ $att->checked_at ? $att->checked_at->format('d M Y H:i') : '-' }}
                                @if($att->reason)<div class="text-muted">{{ $att->reason }}</div>@endif
                            </td>
                            <td>
                                @if($att->proof_url)
                                    <a href="{{ $att->proof_url }}" target="_blank" rel="noopener">Lihat</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-attendance.edit' : 'admin.course-attendance.edit'), $att->id) }}" class="btn btn-sm btn-warning">Ubah</a>
                                <form action="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-attendance.destroy' : 'admin.course-attendance.destroy'), $att->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus presensi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada presensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $attendances->links() }}
        </div>
    </div>
</div>
@endsection
