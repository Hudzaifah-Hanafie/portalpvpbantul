@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0">Detail Sesi Wawancara</h3>
        <small class="text-muted">Kelola peserta, kehadiran, dan penilaian.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.interview-session.edit', $session->id) }}" class="btn btn-outline-primary btn-sm">Ubah</a>
        <a href="{{ route('admin.interview-session.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold">{{ $session->trainingSchedule->judul ?? '-' }}</h5>
                <div class="text-muted small mb-2">Batch: {{ $session->training_schedule_id }}</div>
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Tanggal</dt>
                    <dd class="col-7">{{ optional($session->date)->format('d M Y') }}</dd>
                    <dt class="col-5 text-muted">Jam</dt>
                    <dd class="col-7">{{ $session->start_time }} - {{ $session->end_time }}</dd>
                    <dt class="col-5 text-muted">Lokasi</dt>
                    <dd class="col-7">{{ $session->location }}</dd>
                    <dt class="col-5 text-muted">Pewawancara</dt>
                    <dd class="col-7">{{ $session->interviewer->name ?? '-' }}</dd>
                    <dt class="col-5 text-muted">Kuota</dt>
                    <dd class="col-7">{{ $session->quota }}</dd>
                </dl>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6 class="fw-bold mb-2">Tambah Peserta</h6>
                <form action="{{ route('admin.interview-session.allocations.store', $session->id) }}" method="POST" class="vstack gap-2">
                    @csrf
                    <select name="course_enrollment_id" class="form-select form-select-sm" required>
                        <option value="">Pilih peserta</option>
                        @foreach($eligibleEnrollments as $enrollment)
                            <option value="{{ $enrollment->id }}">
                                {{ $enrollment->user->name ?? 'Peserta' }} ({{ $enrollment->user->nik ?? $enrollment->user->email }})
                            </option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary btn-sm">Tambahkan</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Peserta</h6>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Nilai Wawancara</th>
                                <th>Catatan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($session->allocations as $allocation)
                            <tr>
                                <td class="fw-semibold">{{ $allocation->enrollment->user->name ?? '-' }}</td>
                                <td>
                                    <form action="{{ route('admin.interview-allocations.status', $allocation->id) }}" method="POST" class="d-flex align-items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="form-select form-select-sm">
                                            @foreach(['SCHEDULED' => 'Dijadwalkan', 'ATTENDED' => 'Hadir', 'ABSENT' => 'Tidak Hadir'] as $key => $label)
                                                <option value="{{ $key }}" @selected($allocation->status === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-outline-secondary btn-sm">Simpan</button>
                                    </form>
                                </td>
                                <td>
                                    @if($allocation->score)
                                        <div class="fw-bold">{{ $allocation->score->final_score }}</div>
                                        <div class="text-muted small">({{ $allocation->score->score_communication }}/{{ $allocation->score->score_motivation }}/{{ $allocation->score->score_technical }})</div>
                                    @else
                                        <span class="text-muted small">Belum dinilai</span>
                                    @endif
                                </td>
                                <td class="small">{{ $allocation->score?->interviewer_notes }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#score-{{ $allocation->id }}">Input Nilai</button>
                                </td>
                            </tr>
                            <tr class="collapse" id="score-{{ $allocation->id }}">
                                <td colspan="5">
                                    <form action="{{ route('admin.interview-allocations.score', $allocation->id) }}" method="POST" class="row g-2 align-items-end">
                                        @csrf
                                        <div class="col-md-3">
                                            <label class="form-label small mb-1">Komunikasi (1-10)</label>
                                            <input type="number" name="score_communication" class="form-control form-control-sm" value="{{ $allocation->score->score_communication ?? 8 }}" min="0" max="10" required>
                                        </div>
        <div class="col-md-3">
                                            <label class="form-label small mb-1">Motivasi (1-10)</label>
                                            <input type="number" name="score_motivation" class="form-control form-control-sm" value="{{ $allocation->score->score_motivation ?? 8 }}" min="0" max="10" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small mb-1">Teknis (1-10)</label>
                                            <input type="number" name="score_technical" class="form-control form-control-sm" value="{{ $allocation->score->score_technical ?? 8 }}" min="0" max="10" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small mb-1">Catatan</label>
                                            <input type="text" name="interviewer_notes" class="form-control form-control-sm" value="{{ $allocation->score->interviewer_notes ?? '' }}" placeholder="Catatan singkat">
                                        </div>
                                        <div class="col-12">
                                            <button class="btn btn-success btn-sm">Simpan Nilai</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada peserta di sesi ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
