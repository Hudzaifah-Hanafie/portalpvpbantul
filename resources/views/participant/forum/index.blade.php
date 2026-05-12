@extends('layouts.participant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Forum Kelas: {{ $class->title }}</h4>
        <small class="text-muted">Diskusi dan tanya jawab untuk kelas ini.</small>
    </div>
    <a href="{{ route('participant.classes') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        @if(session('success')) <div class="alert alert-success alert-modern">{{ session('success') }}</div> @endif
        @if($errors->has('access')) <div class="alert alert-danger alert-modern">{{ $errors->first('access') }}</div> @endif
        @if(isset($enrollment) && $enrollment->muted_until && $enrollment->muted_until->isFuture())
            <div class="alert alert-warning alert-modern">Anda dibatasi berpartisipasi di forum hingga {{ $enrollment->muted_until->format('d M Y H:i') }}.</div>
        @endif
        <form action="{{ route('participant.class.forum.store', $class) }}" method="POST" class="row g-2">
            @csrf
            <div class="col-md-4">
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" placeholder="Judul topik" required>
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <input type="text" name="body" class="form-control @error('body') is-invalid @enderror" placeholder="Pertanyaan atau topik singkat">
                @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-2 text-end">
                <button class="btn btn-primary w-100">Tambah Topik</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Judul</th>
                        <th>Dibuat oleh</th>
                        <th>Balasan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topics as $topic)
                        <tr>
                            <td>
                                <a href="{{ route('participant.class.forum.show', [$class, $topic]) }}">{{ $topic->title }}</a>
                                @if($topic->is_pinned) <span class="badge bg-warning text-dark">Pin</span> @endif
                                @if($topic->is_closed) <span class="badge bg-secondary">Tutup</span> @endif
                            </td>
                            <td class="small">{{ $topic->user->name ?? '-' }}</td>
                            <td class="small">{{ $topic->posts_count ?? $topic->posts()->count() }}</td>
                            <td class="text-end">
                                <a href="{{ route('participant.class.forum.show', [$class, $topic]) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada topik.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $topics->links() }}</div>
    </div>
</div>
@endsection
