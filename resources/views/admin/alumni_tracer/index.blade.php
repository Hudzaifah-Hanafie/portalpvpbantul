@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Tracer Alumni</h3>
            <p class="text-muted mb-0">Monitor outcome alumni dan insight kebutuhan industri.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.alumni-tracer.dashboard') }}" class="btn btn-outline-primary btn-sm">Dasbor Tracer</a>
            <a href="{{ route('admin.alumni-tracer.export') }}" class="btn btn-outline-success btn-sm">Ekspor CSV</a>
            <a href="{{ route('alumni.tracer') }}" target="_blank" class="btn btn-outline-primary">Bagikan Form</a>
        </div>
    </div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Total Respon</small>
                <div class="display-6">{{ $metrics['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Bekerja</small>
                <div class="display-6">{{ $metrics['employed'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Wirausaha</small>
                <div class="display-6">{{ $metrics['entrepreneur'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Lanjut Studi</small>
                <div class="display-6">{{ $metrics['studying'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.alumni-tracer.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    @php $statuses = ['employed' => 'Bekerja', 'entrepreneur' => 'Wirausaha', 'studying' => 'Melanjutkan Studi', 'seeking' => 'Mencari Kerja', 'other' => 'Lainnya']; @endphp
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" {{ $status == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tahun Lulus</label>
                <select name="year" class="form-select">
                    <option value="">Semua</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100" type="submit">Terapkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nomor Alumni</th>
                    <th>Nama</th>
                    <th>Program</th>
                    <th>Status</th>
                    <th>Konfirmasi Data</th>
                    <th>Perusahaan / Usaha</th>
                    <th>Terakhir Perbarui</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($responses as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->alumni_number }}</td>
                        <td>{{ $item->full_name }}<br><small class="text-muted">{{ $item->phone }}</small></td>
                        <td>{{ $item->program_name ?? optional($item->program)->judul ?? '-' }}</td>
                        <td><span class="badge bg-primary text-uppercase">{{ $item->status }}</span></td>
                        <td>
                            <span class="badge {{ $item->is_verified ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $item->is_verified ? 'Terverifikasi' : 'Belum diverifikasi' }}
                            </span>
                        </td>
                        <td>{{ $item->company_name ?? $item->business_name ?? '-' }}</td>
                        <td>{{ $item->created_at->translatedFormat('d M Y') }}</td>
                        <td class="d-flex flex-wrap gap-2">
                            @if(! $item->is_verified)
                                <form action="{{ route('admin.alumni-tracer.verify', $item) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-success">Verifikasi</button>
                                </form>
                            @endif
                            <a href="{{ route('admin.alumni-tracer.show', $item) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                            <form action="{{ route('admin.alumni-tracer.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus data ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Belum ada data tracer.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $responses->links() }}
    </div>
</div>
@endsection
