@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\CourseAnnouncement::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Pengumuman Kelas</h4>
        <small class="text-muted">Kelola pengumuman yang tampil ke peserta kelas.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-announcement.create' : 'admin.course-announcement.create')) }}" class="btn btn-primary btn-sm">Tambah Pengumuman</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
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
                        <option value="{{ $id }}" @selected(request('class_id', $classFilter ?? null) == $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">Terapkan</button>
            </div>
            @if(request('status') || request('class_id'))
                <div class="col-auto">
                    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-announcement.index' : 'admin.course-announcement.index')) }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
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
                        <th>Status</th>
                        <th>Dipublikasikan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                        <tr>
                            <td>{{ $announcements->firstItem() + $loop->index }}</td>
                            <td>{{ $announcement->title }}</td>
                            <td class="small">{{ $announcement->course->title ?? '-' }}</td>
                            <td>
                                @php $badge = $announcement->status === 'published' ? 'bg-success' : 'bg-secondary'; @endphp
                                <span class="badge {{ $badge }}">{{ $statusOptions[$announcement->status] ?? $announcement->status }}</span>
                            </td>
                            <td class="small">{{ $announcement->published_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-announcement.edit' : 'admin.course-announcement.edit'), $announcement->id) }}" class="btn btn-sm btn-warning">Ubah</a>
                                <form action="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-announcement.destroy' : 'admin.course-announcement.destroy'), $announcement->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus pengumuman ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada pengumuman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $announcements->links() }}</div>
    </div>
</div>
@endsection
