@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Bank Talenta & Buku CV</h4>
        <small class="text-muted">Profil lulusan/ peserta per kelas, siap dibagikan ke mitra industri/pemerintah.</small>
    </div>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label mb-1">Pilih Kelas / Angkatan</label>
                <select name="class_id" class="form-select">
                    <option value="">Semua kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected($classId == $class->id)>{{ $class->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary">Terapkan</button>
            </div>
            @if($classId)
                <div class="col-auto">
                    <a href="{{ route('admin.talent-pool.index') }}" class="btn btn-link">Atur Ulang</a>
                </div>
            @endif
            @if($classId)
                <div class="col-auto ms-auto">
                    <a href="{{ route('admin.talent-pool.export', $classId) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-file-export"></i> Unduh Buku CV (CSV)
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Kelas</th>
                        <th>Badge / Kompetensi</th>
                        <th>Nilai</th>
                        <th>Pengumpulan</th>
                        <th>Status</th>
                        <th>Terakhir Perbarui</th>
                        <th>Sertifikat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $enrollment)
                        @php($s = $stats[$enrollment->id] ?? [])
                        <tr>
                            <td>{{ $enrollment->user->name ?? '-' }}</td>
                            <td>{{ $enrollment->user->email ?? '-' }}</td>
                            <td>{{ $enrollment->course->title ?? '-' }}</td>
                            <td>
                                @if($enrollment->course?->badge)
                                    <span class="badge text-bg-info text-dark">{{ $enrollment->course->badge }}</span>
                                @endif
                                @if(!empty($enrollment->course?->competencies))
                                    <div class="small text-muted">{{ implode(', ', $enrollment->course->competencies) }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $s['score_total'] ?? 0 }} / {{ $s['score_max'] ?? 0 }}</div>
                                <small class="text-muted">{{ $s['percent'] ?? 0 }}%</small>
                            </td>
                            <td>{{ $s['submission_count'] ?? 0 }}</td>
                            <td>
                                <span class="badge {{ $enrollment->completed_at ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $enrollment->completed_at ? 'Lulus' : 'Aktif' }}
                                </span>
                            </td>
                            <td>{{ $s['last_submission'] ?? '-' }}</td>
                            <td>
                                @if($enrollment->certificate_url)
                                    <a href="{{ $enrollment->certificate_url }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Belum ada data enrollments.</td>
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
