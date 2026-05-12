@extends('layouts.admin')

@php
    use Illuminate\Support\Str;
    $statusOptions = $statusOptions ?? \App\Models\PublicServiceFlow::statuses();
    $statusLabels = $statusOptions;
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Alur Pelayanan Publik</h4>
        <small class="text-muted">Kelola setiap blok alur agar tampilan halaman mengikuti UI lampiran.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('resource.pelayanan') }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat Halaman</a>
        <a href="{{ route('admin.public-service-flow.create') }}" class="btn btn-primary btn-sm">Tambah Alur</a>
    </div>
</div>

<div class="bg-white rounded shadow-sm">
    <form method="GET" class="p-3 border-bottom">
        <div class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
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
                    <a href="{{ route('admin.public-service-flow.index') }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Kategori</th>
                    <th>Judul</th>
                    <th>Urutan</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($flows as $flow)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ ucfirst($flow->category) }}</td>
                        <td>
                            <strong>{{ $flow->title }}</strong>
                            <div class="small text-muted">{{ \Illuminate\Support\Str::limit($flow->subtitle, 80) }}</div>
                        </td>
                        <td>{{ $flow->urutan }}</td>
                        <td class="text-nowrap">
                            @php
                                $status = $flow->status ?? 'draft';
                                $badgeClass = [
                                    'draft' => 'bg-secondary',
                                    'pending' => 'bg-warning text-dark',
                                    'published' => 'bg-success',
                                ][$status] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $statusLabels[$status] ?? ucfirst($status) }}</span>
                            <span class="badge {{ $flow->is_active ? 'bg-success' : 'bg-dark' }}">{{ $flow->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.public-service-flow.edit', $flow) }}" class="btn btn-sm btn-warning">Ubah</a>
                            <form action="{{ route('admin.public-service-flow.destroy', $flow) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus alur ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada data alur.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
