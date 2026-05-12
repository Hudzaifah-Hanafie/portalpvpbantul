@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1">Manajemen Peran</h2>
            <p class="text-muted mb-0">Kelola peran dan hak akses portal.</p>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Tambah Peran</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nama</th>
                    <th>Label</th>
                    <th>Izin Akses</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td><code>{{ $role->name }}</code></td>
                        <td>{{ $role->label ?? '-' }}</td>
                        <td>
                            @forelse($role->permissions as $permission)
                                <span class="badge text-bg-secondary mb-1">{{ $permission->label ?? $permission->name }}</span>
                            @empty
                                <span class="text-muted">Belum ada izin akses</span>
                            @endforelse
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary me-2">
                                <i class="fas fa-edit"></i> Ubah
                            </a>
                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus peran ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" {{ $role->name === 'superadmin' ? 'disabled' : '' }}>
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Belum ada data peran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
