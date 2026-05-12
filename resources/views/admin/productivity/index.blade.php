@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Peningkatan Produktivitas</h3>
    <a href="{{ route('admin.productivity.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah</a>
</div>
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
<div class="card shadow-sm border-0">
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Judul</th>
                    <th>Urutan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->judul }}</td>
                    <td>{{ $item->urutan }}</td>
                    <td><span class="badge {{ $item->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td class="d-flex gap-2">
                        <a href="{{ route('admin.productivity.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
                        <form action="{{ route('admin.productivity.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
