@extends('layouts.participant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $topic->title }}</h4>
        <small class="text-muted">{{ $class->title }}</small>
    </div>
    <a href="{{ route('participant.class.forum.index', $class) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <div class="mb-2 text-muted small">Oleh {{ $topic->user->name ?? '-' }} • {{ $topic->created_at?->format('d M Y H:i') }}</div>
        <p class="mb-0">{{ $topic->body }}</p>
    </div>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        @if(session('success')) <div class="alert alert-success alert-modern">{{ session('success') }}</div> @endif
        @if($errors->has('access')) <div class="alert alert-danger alert-modern">{{ $errors->first('access') }}</div> @endif
        @if(isset($enrollment) && $enrollment->muted_until && $enrollment->muted_until->isFuture())
            <div class="alert alert-warning alert-modern">Anda dibatasi berpartisipasi di forum hingga {{ $enrollment->muted_until->format('d M Y H:i') }}.</div>
        @endif
        <form action="{{ route('participant.class.forum.post', [$class, $topic]) }}" method="POST" class="row g-2">
            @csrf
            <div class="col-12">
                <textarea name="body" rows="3" class="form-control @error('body') is-invalid @enderror" placeholder="Balas topik ini" required></textarea>
                @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-primary">Kirim Balasan</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <h6 class="mb-3">Balasan</h6>
        @forelse($posts as $post)
            <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="fw-bold">{{ $post->user->name ?? '-' }}</div>
                    <div class="small text-muted">{{ $post->created_at?->format('d M Y H:i') }}</div>
                </div>
                <p class="mb-1">{{ $post->body }}</p>
                <form action="{{ route('participant.class.forum.report', $post) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="reason" value="Konten tidak pantas">
                    <button class="btn btn-sm btn-outline-danger">Laporkan</button>
                </form>
            </div>
        @empty
            <div class="text-muted">Belum ada balasan.</div>
        @endforelse
        <div class="mt-3">{{ $posts->links() }}</div>
    </div>
</div>
@endsection
