@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\CourseForumReport::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Laporan Forum Kelas</h4>
        <small class="text-muted">Pantau topik/post yang dilaporkan peserta dan lakukan tindakan.</small>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
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
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">Terapkan</button>
            </div>
            @if(request('status'))
                <div class="col-auto">
                    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-forum-reports.index' : 'admin.course-forum-reports.index')) }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Kelas & Topik</th>
                        <th>Postingan</th>
                        <th>Pelapor</th>
                        <th>Status</th>
                        <th class="text-end">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>{{ $reports->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="fw-bold">{{ data_get($report, 'post.topic.title', '-') }}</div>
                                <div class="small text-muted">{{ data_get($report, 'post.topic.course.title', '-') }}</div>
                            </td>
                            <td>
                                <div class="small text-muted mb-1">Oleh {{ data_get($report, 'post.user.name', '-') }}</div>
                                <div>{{ \Illuminate\Support\Str::limit($report->post->body ?? '-', 140) }}</div>
                                @if($report->reason)
                                    <div class="small text-muted mt-1">Alasan: {{ $report->reason }}</div>
                                @endif
                            </td>
                            <td class="small">{{ $report->reporter->name ?? '-' }}</td>
                            <td>
                                @php
                                    $badge = $report->status === 'resolved' ? 'bg-success' : 'bg-warning text-dark';
                                @endphp
                                <span class="badge {{ $badge }}">{{ $statusOptions[$report->status] ?? $report->status }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex flex-column gap-1 align-items-end">
                                    <form action="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-forum-reports.resolve' : 'admin.course-forum-reports.resolve'), $report->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success" @disabled($report->status === 'resolved')>Resolve</button>
                                    </form>
                                    <form action="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-forum-reports.delete-post' : 'admin.course-forum-reports.delete-post'), $report->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus post ini? Tindakan ini juga menutup laporan.');">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger">Hapus Post</button>
                                    </form>
                                    <form action="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-forum-reports.mute' : 'admin.course-forum-reports.mute'), $report->id) }}" method="POST" class="d-flex align-items-center gap-1">
                                        @csrf
                                        <input type="number" name="duration_days" min="1" max="365" value="7" class="form-control form-control-sm" style="width:80px" title="Durasi (hari)">
                                        <button class="btn btn-sm btn-outline-secondary">Mute</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada laporan forum.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $reports->links() }}</div>
    </div>
</div>
@endsection
