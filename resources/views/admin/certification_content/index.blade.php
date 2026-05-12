@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\CertificationContent::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Konten Halaman Sertifikasi</h3>
    <a href="{{ route('admin.certification-content.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Tambah Konten
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
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
                    <a href="{{ route('admin.certification-content.index') }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </form>
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th width="5%">#</th>
                    <th>Section</th>
                    <th>Judul</th>
                    <th>Urutan</th>
                    <th>Status</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><span class="badge bg-secondary">{{ ucfirst($item->section) }}</span></td>
                    <td>{{ $item->title ?? '-' }}</td>
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
                    <td>
                        <a href="{{ route('admin.certification-content.edit', $item->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.certification-content.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus konten ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Belum ada konten.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
