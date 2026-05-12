@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Highlight PPID</h4>
        <small class="text-muted">Kelola kartu icon PPID yang muncul pada hero halaman.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('ppid') }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat Halaman</a>
        <a href="{{ route('admin.ppid-highlight.create') }}" class="btn btn-primary btn-sm">Tambah Highlight</a>
    </div>
</div>

<div class="bg-white rounded shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Judul</th>
                    <th>Deskripsi</th>
                    <th>Ikon</th>
                    <th>Urutan</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($highlights as $highlight)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $highlight->title }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($highlight->description, 80) }}</td>
                        <td>{{ $highlight->icon ?? '-' }}</td>
                        <td>{{ $highlight->urutan }}</td>
                        <td>
                            <span class="badge {{ $highlight->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $highlight->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.ppid-highlight.edit', $highlight) }}" class="btn btn-sm btn-warning">Ubah</a>
                            <form action="{{ route('admin.ppid-highlight.destroy', $highlight) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus highlight ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada highlight.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
