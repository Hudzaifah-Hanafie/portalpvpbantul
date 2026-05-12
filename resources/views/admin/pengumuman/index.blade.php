@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\Pengumuman::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Kelola Pengumuman</h3>
    <a href="{{ route('admin.pengumuman.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Pengumuman</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @php
            $statusLabels = \App\Models\Pengumuman::statuses();
            $statusColors = [
                \App\Models\Pengumuman::STATUS_DRAFT => 'secondary',
                \App\Models\Pengumuman::STATUS_PENDING => 'warning',
                \App\Models\Pengumuman::STATUS_PUBLISHED => 'success',
            ];
        @endphp
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
                    <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </form>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th width="5%">No</th>
                    <th>Judul Pengumuman</th>
                    <th>Tanggal</th>
                    <th>Lampiran</th>
                    <th>Status</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pengumuman as $info)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $info->judul }}</td>
                    <td>{{ $info->created_at->format('d M Y') }}</td>
                    <td>
                        @if($info->file_download)
                            <a href="{{ asset($info->file_download) }}" target="_blank" class="badge bg-info text-decoration-none">
                                <i class="fas fa-download"></i> Unduh File
                            </a>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge text-bg-{{ $statusColors[$info->status] ?? 'secondary' }}">{{ $statusLabels[$info->status] ?? ucfirst($info->status) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.pengumuman.edit', $info->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        @if($info->status === \App\Models\Pengumuman::STATUS_DRAFT)
                            <form action="{{ route('admin.pengumuman.submit', $info) }}" method="POST" class="d-inline" onsubmit="return confirm('Ajukan pengumuman ini?')">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-paper-plane"></i></button>
                            </form>
                        @endif
                        @if($info->status === \App\Models\Pengumuman::STATUS_PENDING && auth()->user()->hasPermission('approve-content'))
                            <form action="{{ route('admin.pengumuman.approve', $info) }}" method="POST" class="d-inline" onsubmit="return confirm('Setujui pengumuman ini?')">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                            </form>
                        @endif
                        <form action="{{ route('admin.pengumuman.destroy', $info->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus pengumuman ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
