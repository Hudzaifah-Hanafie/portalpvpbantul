@extends('layouts.participant')

@section('content')
<div class="container py-4 fade-up">
    <div class="row align-items-center mb-5">
        <div class="col-md-8">
            <h2 class="display-6 fw-bold mb-2">
                <i class="fas fa-trophy text-warning me-2"></i> Papan Peringkat Gamifikasi
            </h2>
            <p class="text-muted fs-5">Kumpulkan poin dengan belajar dan berdiskusi! Tunjukkan semangat vokasimu.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            @if(auth()->check())
            <div class="card shadow-sm border-0 bg-gradient bg-primary text-white text-center p-3 rounded-4">
                <div class="text-uppercase small fw-bold text-white-50">Poin Saya</div>
                <h3 class="display-4 fw-bold my-1">{{ number_format($userTotalPoints ?? 0) }}</h3>
                <div class="small">Peringkat #{{ $userRank ?? '-' }} Keseluruhan</div>
            </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom-0 py-4 px-4 text-center">
            <h4 class="fw-bold mb-0">🏆 Top 20 Peserta Vokasi 🏆</h4>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 text-uppercase text-secondary" style="width: 10%;">Peringkat</th>
                        <th class="py-3 text-uppercase text-secondary text-start">Nama Peserta</th>
                        <th class="py-3 text-uppercase text-secondary" style="width: 15%;">Total Poin</th>
                        <th class="py-3 text-uppercase text-secondary" style="width: 15%;">Badge</th>
                    </tr>
                </thead>
                <tbody class="fs-5">
                    @forelse($leaderboard as $index => $user)
                        <tr class="{{ auth()->id() === $user->id ? 'table-primary fw-bold' : '' }}">
                            <td class="py-3">
                                @if($index === 0)
                                    <i class="fas fa-medal text-warning display-6 pb-2" title="Peringkat 1"></i>
                                @elseif($index === 1)
                                    <i class="fas fa-medal text-secondary display-6 pb-2" title="Peringkat 2"></i>
                                @elseif($index === 2)
                                    <i class="fas fa-medal text-danger display-6 pb-2" title="Peringkat 3" style="color: #cd7f32 !important;"></i>
                                @else
                                    <span class="fs-4 text-muted">#{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="py-3 text-start">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-gradient text-white d-flex justify-content-center align-items-center fw-bold me-3 shadow-sm" style="width: 48px; height: 48px;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $user->name }}</div>
                                        @if(auth()->id() === $user->id)
                                            <span class="badge bg-primary px-2 py-1 mt-1">Itu Anda!</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 text-primary fw-bold fs-4">
                                {{ number_format($user->total_points) }}
                            </td>
                            <td class="py-3">
                                @if($user->total_points >= 100)
                                    <span class="badge bg-warning text-dark py-2 px-3 rounded-pill shadow-sm"><i class="fas fa-star me-1"></i> Master</span>
                                @elseif($user->total_points >= 50)
                                    <span class="badge bg-info text-dark py-2 px-3 rounded-pill shadow-sm"><i class="fas fa-shield-alt me-1"></i> Pro</span>
                                @else
                                    <span class="badge bg-secondary py-2 px-3 rounded-pill shadow-sm"><i class="fas fa-seedling me-1"></i> Pemula</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-5 text-muted">Belum ada data poin gamifikasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
