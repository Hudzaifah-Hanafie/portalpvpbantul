@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Surat Tugas</h4>
        <small class="text-muted">Generate ST/SK otomatis berbasis data kelas.</small>
    </div>
    <a href="{{ route(request()->routeIs('instructor.*') ? 'instructor.lms.task-letter.create' : 'admin.task-letter.create', $classFilter ? ['class_id' => $classFilter] : []) }}" class="btn btn-primary btn-sm">Buat Surat Tugas</a>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Filter Kelas</label>
                <select name="class_id" class="form-select">
                    <option value="">Semua kelas</option>
                    @foreach($classes as $id => $label)
                        <option value="{{ $id }}" @selected($classFilter === $id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100">Terapkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nomor</th>
                    <th>Kelas</th>
                    <th>Jadwal</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($letters as $letter)
                    <tr>
                        <td>{{ $letter->letter_number ?? '-' }}</td>
                        <td>{{ $letter->course?->title ?? '-' }}</td>
                        <td>
                            @if($letter->start_date || $letter->end_date)
                                {{ optional($letter->start_date)->format('d M Y') ?? '-' }}
                                @if($letter->end_date) - {{ $letter->end_date->format('d M Y') }} @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $letter->status === 'published' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $letter->status === 'published' ? 'Terbit' : 'Draft' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route(request()->routeIs('instructor.*') ? 'instructor.lms.task-letter.show' : 'admin.task-letter.show', $letter->id) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                            <a href="{{ route(request()->routeIs('instructor.*') ? 'instructor.lms.task-letter.edit' : 'admin.task-letter.edit', $letter->id) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
                            <a href="{{ route(request()->routeIs('instructor.*') ? 'instructor.lms.task-letter.print' : 'admin.task-letter.print', $letter->id) }}" target="_blank" class="btn btn-sm btn-outline-success">Cetak</a>
                            <form action="{{ route(request()->routeIs('instructor.*') ? 'instructor.lms.task-letter.destroy' : 'admin.task-letter.destroy', $letter->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus surat tugas ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada surat tugas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $letters->links() }}
</div>
@endsection
