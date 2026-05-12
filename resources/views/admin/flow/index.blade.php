@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Alur Pelatihan</h3>
    <a href="{{ route('admin.flow.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah Langkah</a>
</div>
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
<div class="card shadow-sm border-0">
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead><tr><th>#</th><th>Isi Langkah</th><th>Urutan</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($flows as $flow)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td class="small text-muted">{{ Str::limit($flow->deskripsi ?: $flow->judul, 120) }}</td>
                <td>{{ $flow->urutan }}</td>
                <td><span class="badge {{ $flow->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $flow->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td class="d-flex gap-2">
                    <a href="{{ route('admin.flow.edit', $flow->id) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
                    <form action="{{ route('admin.flow.destroy', $flow->id) }}" method="POST" onsubmit="return confirm('Hapus data?')">
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
