@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Saluran Hubungi Kami</h4>
        <small class="text-muted">Kelola kartu informasi kontak seperti alamat, telepon, dan sosial media.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('resource.hubungi') }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat Halaman</a>
        <a href="{{ route('admin.contact-channel.create') }}" class="btn btn-primary btn-sm">Tambah Saluran</a>
    </div>
</div>

<div class="bg-white rounded shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Judul</th>
                    <th>Label</th>
                    <th>Ikon</th>
                    <th>Urutan</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($channels as $channel)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $channel->title }}</strong>
                            <div class="small text-muted">{{ $channel->subtitle }}</div>
                        </td>
                        <td>{{ $channel->label ?? '-' }}</td>
                        <td>{{ $channel->icon ?? '-' }}</td>
                        <td>{{ $channel->urutan }}</td>
                        <td>
                            <span class="badge {{ $channel->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $channel->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.contact-channel.edit', $channel) }}" class="btn btn-sm btn-warning">Ubah</a>
                            <form action="{{ route('admin.contact-channel.destroy', $channel) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus channel ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada channel.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
