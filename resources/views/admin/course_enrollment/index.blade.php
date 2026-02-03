@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\CourseEnrollment::statuses();
    $adminStatuses = $adminStatuses ?? \App\Models\CourseEnrollment::adminStatuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Enrollment Peserta</h4>
        <small class="text-muted">Data peserta berasal dari portal (akun SIAP Kerja) dan diproses untuk seleksi lokal.</small>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.skillhub.sync') }}" method="POST" class="mb-0">
            @csrf
            <button class="btn btn-outline-secondary btn-sm">Sinkronisasi Skillhub</button>
        </form>
        <a href="{{ route('admin.interview-session.index') }}" class="btn btn-outline-secondary btn-sm">Jadwal Wawancara</a>
        <a href="{{ route('admin.course-enrollment.ranking') }}" class="btn btn-outline-primary btn-sm">Seleksi & Ranking</a>
        <a href="{{ route('admin.course-enrollment.create') }}" class="btn btn-primary btn-sm">Tambah Enrollment</a>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@php
    $filterBase = request()->query();
    $filterLink = fn (array $overrides = []) => route('admin.course-enrollment.index', array_merge($filterBase, $overrides));
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-muted">Total Pendaftar</div>
                <div class="h4 mb-1">{{ $stats['total'] ?? 0 }}</div>
                <a href="{{ $filterLink(['status' => null, 'admin_status' => null, 'user_id' => null]) }}" class="small text-decoration-none">Lihat semua</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-muted">Perlu Verifikasi</div>
                <div class="h4 mb-1 text-warning">{{ $stats['pending_admin'] ?? 0 }}</div>
                <a href="{{ $filterLink(['admin_status' => 'pending']) }}" class="small text-decoration-none">Tinjau</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-muted">Terverifikasi</div>
                <div class="h4 mb-1 text-success">{{ $stats['verified'] ?? 0 }}</div>
                <a href="{{ $filterLink(['admin_status' => 'verified']) }}" class="small text-decoration-none">Lihat</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-muted">Perlu CBT</div>
                <div class="h4 mb-1 text-info">{{ $stats['needs_cbt'] ?? 0 }}</div>
                <div class="small text-muted">Belum mengisi tes tertulis</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-muted">Perlu Wawancara</div>
                <div class="h4 mb-1 text-info">{{ $stats['needs_interview'] ?? 0 }}</div>
                <div class="small text-muted">Menunggu penjadwalan/nilai</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-muted">Lulus</div>
                <div class="h4 mb-1 text-success">{{ $stats['approved'] ?? 0 }}</div>
                <a href="{{ $filterLink(['status' => 'approved']) }}" class="small text-decoration-none">Lihat</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small text-muted">Ditolak</div>
                <div class="h4 mb-1 text-danger">{{ $stats['rejected'] ?? 0 }}</div>
                <a href="{{ $filterLink(['status' => 'rejected']) }}" class="small text-decoration-none">Lihat</a>
            </div>
        </div>
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
                <label class="form-label mb-1">Verifikasi Admin</label>
                <select name="admin_status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($adminStatuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('admin_status', $adminFilter ?? null) === $key)>{{ $label }}</option>
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
            <div class="col-sm-3">
                <label class="form-label mb-1">Peserta</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($users as $id => $name)
                        <option value="{{ $id }}" @selected(request('user_id', $userFilter ?? null) == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">Terapkan</button>
            </div>
            @if(request('status') || request('class_id') || request('user_id') || request('admin_status'))
                <div class="col-auto">
                    <a href="{{ route('admin.course-enrollment.index') }}" class="btn btn-sm btn-link text-decoration-none">Reset</a>
                </div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Peserta</th>
                        <th>Kelas</th>
                        <th>Verifikasi</th>
                        <th>CBT</th>
                        <th>Wawancara</th>
                        <th>Status</th>
                        <th>Skor Akhir</th>
                        <th>Batas Forum</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $enroll)
                        <tr>
                            <td>{{ $enrollments->firstItem() + $loop->index }}</td>
                            <td>{{ $enroll->user->name ?? $enroll->user_id }}</td>
                            <td>{{ $enroll->course->title ?? '-' }}</td>
                            <td>
                                @php
                                    $verifyBadge = match($enroll->admin_status) {
                                        'verified' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $verifyBadge }}">{{ $adminStatuses[$enroll->admin_status] ?? ucfirst($enroll->admin_status ?? 'pending') }}</span>
                                @if($enroll->admin_note)
                                    <div class="small text-muted mt-1">{{ \Illuminate\Support\Str::limit($enroll->admin_note, 70) }}</div>
                                @endif
                            </td>
                            <td>
                                @if($enroll->written_score !== null)
                                    <span class="badge bg-success">Selesai</span>
                                    <div class="small text-muted">Skor: {{ number_format($enroll->written_score, 2) }}</div>
                                @elseif($enroll->admin_status === 'verified')
                                    <span class="badge bg-warning text-dark">Belum</span>
                                    <div class="small text-muted">Menunggu CBT</div>
                                @elseif($enroll->admin_status === 'rejected')
                                    <span class="badge bg-secondary">Ditolak</span>
                                @else
                                    <span class="badge bg-secondary">Menunggu</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $allocation = $enroll->interviewAllocations->first();
                                    $session = $allocation?->session;
                                @endphp
                                @if($enroll->interview_score !== null)
                                    <span class="badge bg-success">Selesai</span>
                                    <div class="small text-muted">Skor: {{ number_format($enroll->interview_score, 2) }}</div>
                                @elseif($session)
                                    <div class="small">{{ $session->date?->format('d M Y') }} • {{ $session->start_time }}-{{ $session->end_time }}</div>
                                    <div class="small text-muted">{{ $session->location }}</div>
                                    <span class="badge bg-info text-dark">{{ $allocation->status }}</span>
                                @else
                                    <span class="badge bg-secondary">Belum Dijadwalkan</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($enroll->status) {
                                        'active', 'approved' => 'bg-success',
                                        'completed' => 'bg-info',
                                        'pending' => 'bg-warning text-dark',
                                        'blocked', 'rejected' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $statusOptions[$enroll->status] ?? $enroll->status }}</span>
                            </td>
                            <td class="fw-semibold">{{ $enroll->final_score !== null ? number_format($enroll->final_score, 2) : '-' }}</td>
                            <td class="small">
                                @if($enroll->muted_until && $enroll->muted_until->isFuture())
                                    <span class="badge bg-warning text-dark">Muted s/d {{ $enroll->muted_until->format('d M Y H:i') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    @if($enroll->admin_status !== 'verified')
                                        <form action="{{ route('admin.course-enrollment.verify', $enroll->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="admin_status" value="verified">
                                            <button class="btn btn-sm btn-success" title="Tandai sudah verifikasi"><i class="fas fa-check"></i></button>
                                        </form>
                                    @endif
                                    @if($enroll->admin_status !== 'rejected')
                                        <form action="{{ route('admin.course-enrollment.verify', $enroll->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tandai peserta ini ditolak administrasi?')">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="admin_status" value="rejected">
                                            <button class="btn btn-sm btn-outline-danger" title="Tandai ditolak"><i class="fas fa-ban"></i></button>
                                        </form>
                                    @endif
                                </div>
                                <a href="{{ route('admin.course-enrollment.edit', $enroll->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.course-enrollment.destroy', $enroll->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus enrollment ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Belum ada enrollment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $enrollments->links() }}
        </div>
    </div>
</div>
@endsection
