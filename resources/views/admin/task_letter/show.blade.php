@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Surat Tugas</h4>
        <small class="text-muted">Pratinjau dan lampiran nominatif.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route(request()->routeIs('instructor.*') ? 'instructor.lms.task-letter.edit' : 'admin.task-letter.edit', $letter->id) }}" class="btn btn-outline-secondary btn-sm">Ubah</a>
        <a href="{{ route(request()->routeIs('instructor.*') ? 'instructor.lms.task-letter.print' : 'admin.task-letter.print', $letter->id) }}" target="_blank" class="btn btn-primary btn-sm">Cetak</a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted small">Nomor Surat</div>
                <div class="fw-semibold">{{ $letter->letter_number ?? '-' }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Kelas</div>
                <div class="fw-semibold">{{ $letter->course?->title ?? '-' }}</div>
            </div>
            <div class="col-md-12">
                <div class="text-muted small">Dasar Hukum</div>
                <div>{{ $letter->legal_basis ?: '-' }}</div>
            </div>
        </div>
        <hr>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted small">Jadwal Pelaksanaan</div>
                <div>
                    {{ optional($letter->start_date)->format('d M Y') ?? '-' }}
                    @if($letter->end_date) - {{ $letter->end_date->format('d M Y') }} @endif
                    @if($letter->start_time) • {{ $letter->start_time }} @endif
                    @if($letter->end_time) - {{ $letter->end_time }} @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Lokasi</div>
                <div>{{ $letter->location_name ?? '-' }}</div>
                @if($letter->location_link)
                    <a href="{{ $letter->location_link }}" target="_blank">{{ $letter->location_link }}</a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-semibold">Instruktur/Tenaga Pelatih</h6>
                <ol class="mb-0">
                    @forelse(($letter->instructors ?? []) as $row)
                        <li>{{ $row['name'] ?? '-' }} @if(!empty($row['nip'])) ({{ $row['nip'] }}) @endif — {{ $row['position'] ?? '-' }}</li>
                    @empty
                        <li class="text-muted">Belum diisi.</li>
                    @endforelse
                </ol>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-semibold">Panitia Penyelenggara</h6>
                <ol class="mb-0">
                    @forelse(($letter->committees ?? []) as $row)
                        <li>{{ $row['name'] ?? '-' }} — {{ $row['role'] ?? '-' }}</li>
                    @empty
                        <li class="text-muted">Belum diisi.</li>
                    @endforelse
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mt-4">
    <div class="card-body">
        <h6 class="fw-semibold">Lampiran I — Daftar Peserta (Nominatif)</h6>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participants as $idx => $row)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $row->user?->name ?? '-' }}</td>
                            <td>{{ $row->user?->email ?? '-' }}</td>
                            <td>{{ $row->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada peserta pada status terpilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
