@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1">Izin Akses</h2>
            <p class="text-muted mb-0">Daftar izin akses yang tersedia.</p>
        </div>
        <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Tambah Izin Akses</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nama</th>
                    <th>Label</th>
                    <th>Modul</th>
                    <th>Peran</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $permission)
                    <tr>
                        <td><code>{{ $permission->name }}</code></td>
                        <td>{{ $permission->label ?? '-' }}</td>
                        <td>{{ $permission->module ?? '-' }}</td>
                        <td>
                            @forelse($permission->roles as $role)
                                <span class="badge text-bg-secondary">{{ $role->label ?? $role->name }}</span>
                            @empty
                                <span class="text-muted">Belum digunakan</span>
                            @endforelse
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-sm btn-outline-primary me-2">
                                <i class="fas fa-edit"></i> Ubah
                            </a>
                            <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus izin akses ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada izin akses.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
