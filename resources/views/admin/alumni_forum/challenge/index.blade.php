@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Challenge Mingguan</h4>
            <p class="text-muted small mb-0">Kelola pertanyaan mingguan yang ditampilkan di forum alumni.</p>
        </div>
        <a href="{{ route('admin.alumni-forum.challenge.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Challenge baru
        </a>
    </div>

    <div class="list-group">
        @forelse($challenges as $challenge)
            <div class="list-group-item border-0 shadow-sm mb-3 rounded-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="fw-bold mb-1">{{ $challenge->title }}</h6>
                        <small class="text-muted mb-1 d-block">{{ $challenge->start_date->translatedFormat('d M Y') }} - {{ $challenge->end_date->translatedFormat('d M Y') }}</small>
                        <p class="small text-muted mb-0">{{ Str::limit($challenge->question, 180) }}</p>
                    </div>
                    <div class="text-end">
                        @if($challenge->is_active)
                            <span class="badge bg-success mb-2">Aktif</span>
                        @endif
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.alumni-forum.challenge.edit', $challenge) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                            <form action="{{ route('admin.alumni-forum.challenge.destroy', $challenge) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">Belum ada challenge terdaftar.</div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $challenges->links('pagination::bootstrap-5') }}
    </div>
@endsection
