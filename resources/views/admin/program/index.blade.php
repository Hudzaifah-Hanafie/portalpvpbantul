@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\Program::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Kelola Program Pelatihan</h3>
    <form action="{{ route('admin.skillhub.sync') }}" method="POST" class="mb-0">
        @csrf
        <button class="btn btn-primary"><i class="fas fa-sync-alt me-1"></i> Sinkronisasi dari Pusat</button>
    </form>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
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
                    <a href="{{ route('admin.program.index') }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </form>
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Gambar</th>
                    <th>Nama Kejuruan</th>
                    <th>External ID</th>
                    <th>Deskripsi Singkat</th>
                    <th>Status</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($programs as $index => $p)
                <tr>
                    <td>{{ $loop->iteration }}</td> <td>
                        @if($p->gambar)
                            <img src="{{ asset($p->gambar) }}" width="80" class="rounded">
                        @else
                            <span class="text-muted small">No Image</span>
                        @endif
                    </td>
                    <td class="fw-bold">{{ $p->judul }}</td>
                    <td>{{ $p->external_id ?? '-' }}</td>
                    <td>{{ Str::limit($p->deskripsi, 100) }}</td>
                    <td class="text-nowrap">
                        @php
                            $status = $p->status ?? 'draft';
                            $badgeClass = [
                                'draft' => 'bg-secondary',
                                'pending' => 'bg-warning text-dark',
                                'published' => 'bg-success',
                            ][$status] ?? 'bg-secondary';
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $statusOptions[$status] ?? ucfirst($status) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.program.show', $p->id) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-eye me-1"></i> Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
