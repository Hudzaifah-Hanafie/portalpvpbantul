@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Seleksi & Ranking Peserta</h4>
        <small class="text-muted">Hitung skor gabungan, batasi kuota, dan terapkan status lulus/cadangan.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-enrollment.index' : 'admin.course-enrollment.index')) }}" class="btn btn-outline-secondary btn-sm">Kembali ke daftar</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label mb-1">Pilih Kelas / Batch</label>
                <select name="class_id" class="form-select" required>
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classes as $id => $title)
                        <option value="{{ $id }}" @selected(request('class_id') == $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Kuota Lulus</label>
                <input type="number" name="quota" min="0" class="form-control" value="{{ old('quota', $quota ?? 0) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Cadangan</label>
                <input type="number" name="reserve" min="0" class="form-control" value="{{ old('reserve', $reserve ?? 0) }}">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary">Lihat Ranking</button>
            </div>
        </form>
    </div>
</div>

@if($selectedClass)
    <div class="alert alert-info mt-3 small mb-0">
        Hanya peserta dengan status verifikasi <strong>Terverifikasi</strong> yang dihitung kuotanya. Peserta belum diverifikasi akan tetap berstatus pending.
    </div>

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="fw-semibold text-uppercase small text-muted">Kelas</div>
                <h5 class="mb-1">{{ $selectedClass->title }}</h5>
                @if($schedule)
                    <div class="small text-muted">Batch ID: {{ $schedule->batch_id ?? '-' }} • Kuota Skillhub: {{ $schedule->kuota ?? '-' }}</div>
                @endif
            </div>
            <div class="d-flex gap-3">
                <div class="text-center">
                    <div class="fw-bold fs-5 text-success">{{ $quota }}</div>
                    <div class="small text-muted">Kuota Lulus</div>
                </div>
                <div class="text-center">
                    <div class="fw-bold fs-5 text-warning">{{ $reserve }}</div>
                    <div class="small text-muted">Cadangan</div>
                </div>
                <div class="text-center">
                    <div class="fw-bold fs-5">{{ $ranked->count() }}</div>
                    <div class="small text-muted">Total Peserta</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-1">Daftar Ranking</h6>
                    <small class="text-muted">Urutan berdasarkan skor akhir (40% tes tulis + 60% wawancara).</small>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-success">Lulus</span>
                    <span class="badge bg-warning text-dark">Cadangan</span>
                    <span class="badge bg-secondary">Menunggu Verifikasi</span>
                    <span class="badge bg-light text-dark border">Tidak Lulus</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Rank</th>
                            <th>Peserta</th>
                            <th>Verifikasi</th>
                            <th>Tertulis</th>
                            <th>Wawancara</th>
                            <th>Skor Akhir</th>
                            <th>Rekomendasi</th>
                            <th>Status Portal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ranked as $enroll)
                            @php
                                $decision = $enroll->proposed_decision ?? 'pending';
                                $rowClass = match($decision) {
                                    'approved' => 'table-success',
                                    'reserve' => 'table-warning',
                                    'rejected' => 'table-light',
                                    default => ''
                                };
                                $decisionLabel = [
                                    'approved' => 'Lulus',
                                    'reserve' => 'Cadangan',
                                    'rejected' => 'Tidak Lulus',
                                    'pending' => 'Menunggu Verifikasi',
                                ][$decision] ?? 'Menunggu Verifikasi';
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td>
                                    <div class="fw-bold">{{ $enroll->verified_rank ?? '—' }}</div>
                                    <div class="small text-muted">#{{ $enroll->rank }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $enroll->user?->name ?? '-' }}</div>
                                    <div class="small text-muted">{{ $enroll->user?->email }}</div>
                                    @if($enroll->user?->nik)
                                        <div class="small text-muted">NIK: {{ $enroll->user->nik }}</div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $verifyBadge = match($enroll->admin_status) {
                                            'verified' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        $adminStatuses = \App\Models\CourseEnrollment::adminStatuses();
                                    @endphp
                                    <span class="badge {{ $verifyBadge }}">{{ $adminStatuses[$enroll->admin_status] ?? ucfirst($enroll->admin_status ?? 'pending') }}</span>
                                    @if($enroll->admin_note)
                                        <div class="small text-muted mt-1">{{ \Illuminate\Support\Str::limit($enroll->admin_note, 60) }}</div>
                                    @endif
                                </td>
                                <td>{{ $enroll->written_score !== null ? number_format($enroll->written_score, 2) : '-' }}</td>
                                <td>{{ $enroll->interview_score !== null ? number_format($enroll->interview_score, 2) : '-' }}</td>
                                <td class="fw-semibold">{{ $enroll->final_score !== null ? number_format($enroll->final_score, 2) : '0.00' }}</td>
                                <td><span class="badge {{ $decision === 'approved' ? 'bg-success' : ($decision === 'reserve' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ $decisionLabel }}</span></td>
                                <td>
                                    @php
                                        $statusOptions = \App\Models\CourseEnrollment::statuses();
                                    @endphp
                                    <span class="badge bg-light text-dark border">{{ $statusOptions[$enroll->status] ?? $enroll->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada peserta pada kelas ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <form action="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-enrollment.ranking.apply' : 'admin.course-enrollment.ranking.apply')) }}" method="POST" class="mt-3">
                @csrf
                <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                <input type="hidden" name="quota" value="{{ $quota }}">
                <input type="hidden" name="reserve" value="{{ $reserve }}">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="1" id="publishAnnouncement" name="publish">
                    <label class="form-check-label" for="publishAnnouncement">
                        Umumkan hasil ke peserta (buat pengumuman kelas)
                    </label>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Sesuaikan kuota/cadangan via formulir di atas lalu tekan tombol ini untuk menerapkan.</small>
                    <button class="btn btn-primary">Terapkan Keputusan</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
