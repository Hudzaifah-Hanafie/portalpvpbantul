@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Surat Keputusan</h4>
        <small class="text-muted">Nomor: {{ $letter->letter_number ?? '-' }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.decision-letter.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
        <a href="{{ route('admin.decision-letter.edit', $letter->id) }}" class="btn btn-outline-primary btn-sm">Ubah</a>
        <a href="{{ route('admin.decision-letter.pdf', $letter->id) }}" class="btn btn-outline-success btn-sm">Ekspor PDF</a>
        <a href="{{ route('admin.decision-letter.print', $letter->id) }}" target="_blank" class="btn btn-success btn-sm">Cetak</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small">Periode</div>
                <div class="fw-semibold">{{ $letter->batch_id ?? '-' }} / {{ $letter->period_year ?? '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Jumlah Program</div>
                <div class="fw-semibold">{{ is_array($letter->schedule_ids) ? count($letter->schedule_ids) : 0 }} Program</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Status</div>
                <span class="badge {{ $letter->status === 'published' ? 'text-bg-success' : 'text-bg-secondary' }}">
                    {{ $letter->status === 'published' ? 'Terbit' : 'Draft' }}
                </span>
            </div>
        </div>
        <hr>
        <div>
            <div class="text-muted small">Tentang</div>
            <div>{!! nl2br(e($letter->subject)) !!}</div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <h6 class="fw-semibold">Daftar Program</h6>
        <div class="text-muted small mb-2">{{ $programList }}</div>
        @if($schedules->isEmpty())
            <div class="text-muted">Belum ada jadwal terhubung.</div>
        @else
            <ul class="mb-0">
                @foreach($schedules as $schedule)
                    <li>{{ $schedule->program?->judul ?? '-' }} — {{ $schedule->judul ?? '-' }} ({{ $schedule->mulai }} s/d {{ $schedule->selesai }})</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
