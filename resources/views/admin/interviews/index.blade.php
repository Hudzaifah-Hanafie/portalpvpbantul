@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0">Sesi Wawancara</h3>
        <small class="text-muted">Jadwalkan dan pantau wawancara peserta.</small>
    </div>
    <a href="{{ route('admin.interview-session.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tambah Sesi</a>
</div>
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
<div class="card shadow-sm border-0">
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Batch</th>
                    <th>Pewawancara</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Lokasi</th>
                    <th>Kuota</th>
                    <th>Peserta</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                <tr>
                    <td>{{ $sessions->firstItem() + $loop->index }}</td>
                    <td>{{ $session->trainingSchedule->judul ?? '-' }}</td>
                    <td>{{ $session->interviewer->name ?? '-' }}</td>
                    <td>{{ optional($session->date)->format('d M Y') }}</td>
                    <td>{{ $session->start_time }} - {{ $session->end_time }}</td>
                    <td>{{ $session->location }}</td>
                    <td>{{ $session->quota }}</td>
                    <td>{{ $session->allocations->count() }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.interview-session.show', $session->id) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                        <a href="{{ route('admin.interview-session.edit', $session->id) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">Belum ada jadwal wawancara.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $sessions->links() }}
    </div>
        </div>
@endsection
