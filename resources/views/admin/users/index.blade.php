@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1">Manajemen Pengguna</h2>
            <p class="text-muted mb-0">Kelola akun dan peran pengguna portal.</p>
        </div>
        @can('create', App\Models\User::class)
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="fas fa-user-plus me-2"></i>Tambah Pengguna</a>
        @endcan
    </div>

    <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Peran</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->roles->isEmpty())
                                <span class="badge text-bg-secondary">Belum Ada Peran</span>
                            @else
                                @foreach($user->roles as $role)
                                    <span class="badge text-bg-primary">{{ $role->label ?? ucfirst($role->name) }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td class="text-end">
                            @can('impersonate', $user)
                                <form action="{{ route('admin.impersonate.start', $user) }}" method="POST" class="d-inline me-2" onsubmit="return confirm('Masuk sebagai {{ $user->name }}?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-user-secret"></i> Impersonasi
                                    </button>
                                </form>
                            @endcan
                            @can('update', $user)
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary me-2">
                                    <i class="fas fa-edit"></i> Ubah
                                </a>
                            @endcan
                            @can('delete', $user)
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus pengguna ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Belum ada data pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
