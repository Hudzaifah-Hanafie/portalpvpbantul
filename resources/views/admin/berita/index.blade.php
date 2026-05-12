@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\Berita::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Kelola Berita</h3>
    <a href="{{ route('admin.berita.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Berita</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        @php
            $statusLabels = \App\Models\Berita::statuses();
            $statusColors = [
                \App\Models\Berita::STATUS_DRAFT => 'secondary',
                \App\Models\Berita::STATUS_PENDING => 'warning',
                \App\Models\Berita::STATUS_PUBLISHED => 'success',
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
                    <a href="{{ route('admin.berita.index') }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </form>
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Judul Berita</th>
                    <th>Kategori</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($berita as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <img src="{{ $item->gambar_utama }}" width="80" class="rounded">
                    </td>
                    <td>{{ $item->judul }}</td>
                    <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ \App\Models\Berita::categories()[$item->kategori] ?? $item->kategori }}</span></td>
                    <td>{{ optional($item->published_at)->format('d M Y') }}</td>
                    <td>
                        <span class="badge text-bg-{{ $statusColors[$item->status] ?? 'secondary' }}">{{ $statusLabels[$item->status] ?? ucfirst($item->status) }}</span>
                        @if($item->status === \App\Models\Berita::STATUS_PENDING && $item->approver)
                            <small class="text-muted d-block">Menunggu persetujuan</small>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.berita.edit', $item->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        @if($item->status === \App\Models\Berita::STATUS_DRAFT)
                            <form action="{{ route('admin.berita.submit', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Ajukan berita ini?')">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-paper-plane"></i></button>
                            </form>
                        @endif
                        @if($item->status === \App\Models\Berita::STATUS_PENDING && auth()->user()->hasPermission('approve-content'))
                            <form action="{{ route('admin.berita.approve', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Setujui berita ini?')">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                            </form>
                        @endif
                        <form action="{{ route('admin.berita.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
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
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-3">
            <div class="text-muted small">
                Menampilkan {{ $berita->firstItem() ?? 0 }} - {{ $berita->lastItem() ?? 0 }} dari {{ $berita->total() }} berita
            </div>
            <div>
                {{ $berita->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
