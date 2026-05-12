@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\JobVacancy::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Lowongan Kerja</h3>
    <a href="{{ route('admin.lowongan.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Lowongan</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
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
                    <a href="{{ route('admin.lowongan.index') }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </form>
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Judul</th>
                    <th>Perusahaan</th>
                    <th>Lokasi</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vacancies as $vacancy)
                    <tr>
                        <td>{{ $vacancies->firstItem() + $loop->index }}</td>
                        <td class="fw-semibold">{{ $vacancy->judul }}</td>
                        <td>{{ $vacancy->perusahaan ?? '-' }}</td>
                        <td>{{ $vacancy->lokasi ?? '-' }}</td>
                        <td>{{ $vacancy->deadline ? $vacancy->deadline->format('d M Y') : '-' }}</td>
                        <td class="text-nowrap">
                            @php
                                $status = $vacancy->status ?? 'draft';
                                $badgeClass = [
                                    'draft' => 'bg-secondary',
                                    'pending' => 'bg-warning text-dark',
                                    'published' => 'bg-success',
                                ][$status] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $statusOptions[$status] ?? ucfirst($status) }}</span>
                            <span class="badge {{ $vacancy->is_active ? 'bg-success' : 'bg-dark' }}">{{ $vacancy->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.lowongan.edit', $vacancy->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.lowongan.destroy', $vacancy->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus lowongan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada data lowongan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{ $vacancies->links() }}
    </div>
</div>
@endsection
