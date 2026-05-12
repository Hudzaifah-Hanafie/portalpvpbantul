@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Surat Keputusan (SK)</h4>
        <small class="text-muted">Generate SK otomatis berdasarkan transaksi pelatihan.</small>
    </div>
    <a href="{{ route('admin.decision-letter.create') }}" class="btn btn-primary btn-sm">Buat SK</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nomor SK</th>
                    <th>Periode</th>
                    <th>Jumlah Program</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($letters as $letter)
                    <tr>
                        <td>{{ $letter->letter_number ?? '-' }}</td>
                        <td>
                            @php
                                $week = $letter->batch_id ? strtoupper($letter->batch_id) : '-';
                                $year = $letter->period_year ?? '-';
                            @endphp
                            {{ $week }} / {{ $year }}
                        </td>
                        <td>{{ is_array($letter->schedule_ids) ? count($letter->schedule_ids) : 0 }}</td>
                        <td>
                            <span class="badge {{ $letter->status === 'published' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $letter->status === 'published' ? 'Terbit' : 'Draft' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.decision-letter.show', $letter->id) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                            <a href="{{ route('admin.decision-letter.edit', $letter->id) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
                            <a href="{{ route('admin.decision-letter.pdf', $letter->id) }}" class="btn btn-sm btn-outline-success">Ekspor PDF</a>
                            <a href="{{ route('admin.decision-letter.print', $letter->id) }}" target="_blank" class="btn btn-sm btn-outline-success">Cetak</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada Surat Keputusan.</td>
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
