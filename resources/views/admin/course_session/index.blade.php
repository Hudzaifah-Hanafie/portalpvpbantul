@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\CourseSession::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Jadwal & Sesi</h4>
        <small class="text-muted">Kelola jadwal, link live, dan rekaman dengan kontrol publikasi.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-session.create' : 'admin.course-session.create')) }}" class="btn btn-primary btn-sm">Tambah Sesi</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-sm-3">
                <label class="form-label mb-1">Filter Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('status', $statusFilter ?? null) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label mb-1">Filter Kelas</label>
                <select name="class_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($classes as $id => $title)
                        <option value="{{ $id }}" @selected(request('class_id', $classFilter ?? null) === $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">Terapkan</button>
            </div>
            @if(request('status') || request('class_id'))
                <div class="col-auto">
                    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-session.index' : 'admin.course-session.index')) }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Judul</th>
                        <th>Kelas</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th>Presensi</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        <tr>
                            <td>{{ $sessions->firstItem() + $loop->index }}</td>
                            <td>{{ $session->title }}</td>
                            <td>{{ $session->course->title ?? '-' }}</td>
                            <td>
                                <div class="small">
                                    {{ $session->start_at ? $session->start_at->format('d M Y H:i') : '-' }}<br>
                                    s/d {{ $session->end_at ? $session->end_at->format('d M Y H:i') : '-' }}
                                </div>
                            </td>
                            <td class="text-nowrap">
                                @php
                                    $status = $session->status ?? 'draft';
                                    $badgeClass = [
                                        'draft' => 'bg-secondary',
                                        'pending' => 'bg-warning text-dark',
                                        'published' => 'bg-success',
                                    ][$status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $statusOptions[$status] ?? ucfirst($status) }}</span>
                                <span class="badge {{ $session->is_active ? 'bg-success' : 'bg-dark' }}">{{ $session->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="small">
                                @if($session->attendance_code)
                                    <div>Kode: <strong>{{ $session->attendance_code }}</strong></div>
                                    @if($session->attendance_code_expires_at)
                                        <div class="text-muted">s/d {{ $session->attendance_code_expires_at->format('d M Y H:i') }}</div>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-session.show' : 'admin.course-session.show'), $session->id) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-session.edit' : 'admin.course-session.edit'), $session->id) }}" class="btn btn-sm btn-warning">Ubah</a>
                    @if($session->attendance_code)
                        <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-session.qr' : 'admin.course-session.qr'), $session->id) }}" class="btn btn-sm btn-outline-primary">QR Presensi</a>
                    @endif
                    <form action="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-session.destroy' : 'admin.course-session.destroy'), $session->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus sesi ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada sesi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $sessions->links() }}
        </div>
    </div>
</div>
@endsection
