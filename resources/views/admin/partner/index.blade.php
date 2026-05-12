@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\Partner::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Partner Kami</h3>
    <a href="{{ route('admin.partner.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah Partner</a>
</div>

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
                    <a href="{{ route('admin.partner.index') }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </form>
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Logo</th>
                    <th>Nama</th>
                    <th>Tautan</th>
                    <th>Urutan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($partners as $partner)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        @if($partner->logo)
                            <img src="{{ asset($partner->logo) }}" alt="{{ $partner->nama }}" width="60">
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $partner->nama }}</td>
                    <td>
                        @if($partner->tautan)
                            <a href="{{ $partner->tautan }}" target="_blank">{{ $partner->tautan }}</a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $partner->urutan }}</td>
                    <td>
                        @php
                            $status = $partner->status ?? 'draft';
                            $badgeClass = [
                                'draft' => 'bg-secondary',
                                'pending' => 'bg-warning text-dark',
                                'published' => 'bg-success',
                            ][$status] ?? 'bg-secondary';
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $statusOptions[$status] ?? ucfirst($status) }}</span>
                        <span class="badge {{ $partner->is_active ? 'bg-success' : 'bg-dark' }}">{{ $partner->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td class="d-flex gap-2">
                        <a href="{{ route('admin.partner.edit', $partner->id) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
                        <form action="{{ route('admin.partner.destroy', $partner->id) }}" method="POST" onsubmit="return confirm('Hapus partner ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data partner.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
