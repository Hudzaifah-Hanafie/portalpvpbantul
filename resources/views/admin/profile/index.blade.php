@extends('layouts.admin')

@section('content')
<h3>Kelola Halaman Profil Instansi</h3>
<div class="card shadow-sm mt-3 border-0">
    <div class="card-body">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Nama Halaman</th>
                    <th>Terakhir Diupdate</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($profiles as $p)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="fw-bold">{{ $p->judul }}</td>
                    <td>{{ $p->updated_at->format('d M Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.profile.edit', $p->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i> Ubah Konten
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection