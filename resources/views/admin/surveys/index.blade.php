@extends('layouts.admin')

@php use Illuminate\Support\Str; @endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Survei Dinamis</h4>
        <small class="text-muted">Rancang survei dasar (pertanyaan, tema, logika) yang dipakai ulang oleh Survei Instans.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.survey-instance.index') }}" class="btn btn-outline-primary btn-sm">Kelola Survei Instans</a>
        <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Buat Survei Dinamis</a>
    </div>
</div>

<div class="card shadow-sm border-0" id="analitik">
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Status</th>
                    <th>Respons</th>
                    <th>Jadwal</th>
                    <th>Tautan</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($surveys as $survey)
                @php $isOpen = $survey->isOpen(); @endphp
                <tr>
                    <td style="min-width:220px;">
                        <div class="fw-semibold">{{ $survey->title }}</div>
                        <small class="text-muted">{{ Str::limit($survey->description, 80) }}</small>
                    </td>
                    <td>
                        <span class="badge rounded-pill {{ $isOpen ? 'bg-success' : 'bg-secondary' }}">{{ $isOpen ? 'Terbuka' : 'Tutup' }}</span>
                        @if($survey->require_login)
                            <span class="badge rounded-pill bg-info text-dark">Masuk</span>
                        @endif
                        @if(! $survey->allow_multiple_responses)
                            <span class="badge rounded-pill bg-warning text-dark">1x submit</span>
                        @endif
                    </td>
                    <td>
                        <div class="fw-bold">{{ $survey->responses_count }} respons</div>
                        @if($survey->max_responses)
                            <small class="text-muted">Batas {{ $survey->max_responses }}</small>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted d-block">
                            @if($survey->opens_at) Mulai: {{ $survey->opens_at->format('d M Y H:i') }} @else Tidak dijadwalkan @endif
                        </small>
                        <small class="text-muted d-block">
                            @if($survey->closes_at) Tutup: {{ $survey->closes_at->format('d M Y H:i') }} @else - @endif
                        </small>
                    </td>
                    <td style="min-width:220px;">
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" class="form-control form-control-sm link-copy" value="{{ url('/survei/'.$survey->slug) }}" readonly>
                            <button class="btn btn-sm btn-outline-primary copy-btn" data-url="{{ url('/survei/'.$survey->slug) }}"><i class="fas fa-copy"></i></button>
                        </div>
                        @if($survey->embed_token && $survey->allow_embed)
                            <small class="text-muted d-block mt-1">Sematan: <code>&lt;iframe src="{{ url('/survei/embed/'.$survey->embed_token) }}"&gt;&lt;/iframe&gt;</code></small>
                        @endif
                    </td>
                    <td class="text-end" style="min-width:260px;">
                        <div class="d-flex flex-wrap justify-content-end gap-2">
                            <a href="{{ route('admin.surveys.edit', $survey) }}" class="btn btn-sm btn-light border">✏️ Ubah</a>
                            <a href="{{ route('admin.surveys.analytics', $survey) }}" class="btn btn-sm btn-primary text-white">📊 Analitik</a>
                            <a href="{{ route('surveys.show', $survey) }}" target="_blank" class="btn btn-sm btn-dark text-white">🌐 Lihat Portal</a>
                            <form action="{{ route('admin.surveys.duplicate', $survey) }}" method="POST" onsubmit="return confirm('Duplikasi survei ini?')" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success text-white">📄 Duplikat</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada survei. Mulai dengan tombol "Buat Survei".</td></tr>
            @endforelse
            </tbody>
        </table>

        {{ $surveys->links() }}
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.copy-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const url = btn.dataset.url;
                navigator.clipboard.writeText(url).then(() => {
                    btn.innerHTML = '<i class="fas fa-check"></i>';
                    setTimeout(() => btn.innerHTML = '<i class="fas fa-copy"></i>', 1200);
                });
            });
        });
        if (window.location.hash === '#analitik') {
            const target = document.getElementById('analitik');
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });
</script>
@endpush
@endsection
