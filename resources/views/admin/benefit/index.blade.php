@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\Benefit::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Kenapa Harus Ikut (Benefit)</h3>
    <a href="{{ route('admin.benefit.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah</a>
</div>
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
<div class="card shadow-sm border-0">
    <div class="card-body table-responsive">
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
                    <a href="{{ route('admin.benefit.index') }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </form>
        <table class="table align-middle">
            <thead><tr><th>#</th><th>Judul</th><th>Deskripsi</th><th>Urutan</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($benefits as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->judul }}</td>
                    <td class="small text-muted">{{ Str::limit($item->deskripsi, 80) }}</td>
                    <td>{{ $item->urutan }}</td>
                    <td class="text-nowrap">
                        @php
                            $status = $item->status ?? 'draft';
                            $badgeClass = [
                                'draft' => 'bg-secondary',
                                'pending' => 'bg-warning text-dark',
                                'published' => 'bg-success',
                            ][$status] ?? 'bg-secondary';
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $statusOptions[$status] ?? ucfirst($status) }}</span>
                        <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-dark' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td class="d-flex gap-2">
                        <a href="{{ route('admin.benefit.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
                        <form action="{{ route('admin.benefit.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus data?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
