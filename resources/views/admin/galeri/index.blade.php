@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\Galeri::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Kelola Galeri Foto</h3>
    <a href="{{ route('admin.galeri.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Unggah Foto</a>
</div>

<form method="GET" class="row g-2 align-items-end mb-3">
    <div class="col-sm-4 col-md-3">
        <label class="form-label mb-1">Filter Status</label>
        <select name="status" class="form-select form-select-sm">
            <option value="">Semua</option>
            @foreach($statusOptions as $key => $label)
                <option value="{{ $key }}" @selected(request('status', $statusFilter ?? null) === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-sm btn-outline-primary">Terapkan</button>
    </div>
    @if(request('status'))
        <div class="col-auto">
            <a href="{{ route('admin.galeri.index') }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
        </div>
    @endif
</form>

<div class="row">
    @foreach($galeri as $foto)
    <div class="col-md-3 mb-4">
        <div class="card h-100 shadow-sm border-0 position-relative">
            @php
                $status = $foto->status ?? 'draft';
                $badgeClass = [
                    'draft' => 'bg-secondary',
                    'pending' => 'bg-warning text-dark',
                    'published' => 'bg-success',
                ][$status] ?? 'bg-secondary';
            @endphp
            <div class="position-absolute top-0 start-0 m-2">
                <span class="badge {{ $badgeClass }}">{{ $statusOptions[$status] ?? ucfirst($status) }}</span>
            </div>
            <img src="{{ asset($foto->gambar) }}" class="card-img-top" style="height: 150px; object-fit: cover;">
            <div class="card-body p-2 text-center">
                <p class="card-text small fw-bold mb-2">{{ $foto->judul }}</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('admin.galeri.edit', $foto->id) }}" class="btn btn-xs btn-outline-warning btn-sm"><i class="fas fa-edit"></i></a>
                    <form action="{{ route('admin.galeri.destroy', $foto->id) }}" method="POST" onsubmit="return confirm('Hapus foto ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-xs btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="d-flex justify-content-center">
    {{ $galeri->links() }}
</div>
@endsection
