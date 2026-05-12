@extends('layouts.participant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Jadwal Wawancara</h4>
        <small class="text-muted">Lihat jadwal, lokasi, dan status wawancara Anda.</small>
    </div>
</div>

<div class="row g-3">
    @forelse($allocations as $allocation)
        @php
            $session = $allocation->session;
        @endphp
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="badge bg-primary-subtle text-primary">Wawancara</div>
                            <h6 class="fw-bold mb-0">{{ $session->trainingSchedule->judul ?? 'Pelatihan' }}</h6>
                        </div>
                        <span class="badge {{ $allocation->status === 'ATTENDED' ? 'bg-success' : ($allocation->status === 'ABSENT' ? 'bg-danger' : 'bg-secondary') }}">
                            {{ $allocation->status }}
                        </span>
                    </div>
                    <div class="text-muted small">
                        <div><i class="fas fa-calendar me-1"></i> {{ optional($session->date)->format('d M Y') }} • {{ $session->start_time }} - {{ $session->end_time }}</div>
                        <div><i class="fas fa-map-marker-alt me-1"></i> {{ $session->location }}</div>
                        <div><i class="fas fa-user-tie me-1"></i> Pewawancara: {{ $session->interviewer->name ?? '-' }}</div>
                    </div>
                    @if($allocation->score)
                        <div class="mt-3 p-3 bg-light rounded-3">
                            <div class="fw-semibold small text-uppercase text-muted mb-1">Nilai Wawancara</div>
                            <div class="fs-4 fw-bold text-success">{{ $allocation->score->final_score }}</div>
                            <div class="text-muted small">Catatan: {{ $allocation->score->interviewer_notes ?: 'Tidak ada catatan' }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info alert-modern">Belum ada jadwal wawancara untuk Anda.</div>
        </div>
    @endforelse
</div>
@endsection
