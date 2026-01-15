@extends('layouts.admin')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h3 class="mb-1">Kotak Masuk Pengaduan</h3>
            <p class="text-muted mb-0">Terima pesan dari masyarakat dan alumni. Tandai pesan yang sudah ditindaklanjuti agar tim tetap terkoordinasi.</p>
        </div>
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari nama, email, subjek" aria-label="Cari pesan">
            <select name="status" class="form-select form-select-sm">
                <option value="all" @selected($statusFilter === 'all')>Semua</option>
                <option value="unread" @selected($statusFilter === 'unread')>Belum dibaca</option>
                <option value="read" @selected($statusFilter === 'read')>Sudah dibaca</option>
            </select>
            <button type="submit" class="btn btn-outline-primary btn-sm">Filter</button>
        </form>
    </div>

    @if(session('status'))
        <div class="alert alert-success rounded-4">
            {{ session('status') }}
        </div>
    @endif

    @php
        $statusLabel = match ($statusFilter) {
            'read' => 'Sudah dibaca',
            'unread' => 'Belum dibaca',
            default => 'Semua pesan',
        };
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Total pesan</small>
                            <h4 class="mb-0">{{ $stats['total'] }}</h4>
                        </div>
                        <i class="fas fa-inbox fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Belum dibaca</small>
                            <h4 class="mb-0 text-warning">{{ $stats['unread'] }}</h4>
                        </div>
                        <i class="fas fa-eye-slash fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Sudah dibaca</small>
                            <h4 class="mb-0 text-success">{{ $stats['read'] }}</h4>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Daftar pesan <span class="text-muted fw-normal">({{ $statusLabel }})</span></h5>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="text-muted small text-uppercase">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama</th>
                                    <th>Subjek</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pesan as $item)
                                    <tr @class([
                                        'table-secondary' => ! $item->is_read,
                                    ])>
                                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            {{ $item->nama }}<br>
                                            <small class="text-muted">{{ $item->email }}</small>
                                        </td>
                                        <td>{{ $item->subjek }}</td>
                                        <td>
                                            <span class="badge rounded-pill {{ $item->is_read ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $item->is_read ? 'Sudah dibaca' : 'Belum dibaca' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @php
                                                $focusQuery = array_merge(request()->except(['focus', 'page']), ['focus' => $item->id]);
                                            @endphp
                                            <a href="{{ route('admin.pesan.index', $focusQuery) }}" class="btn btn-sm btn-outline-secondary me-2">Lihat detail</a>
                                            <form class="d-inline" action="{{ route('admin.pesan.status', $item) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_read" value="{{ $item->is_read ? 0 : 1 }}">
                                                <button type="submit" class="btn btn-sm btn-{{ $item->is_read ? 'outline-warning' : 'outline-success' }}">
                                                    {{ $item->is_read ? 'Tandai belum dibaca' : 'Tandai sudah dibaca' }}
                                                </button>
                                            </form>
                                            <form class="d-inline" action="{{ route('admin.pesan.destroy', $item) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger ms-1" title="Hapus pesan">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Belum ada pesan untuk ditampilkan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $pesan->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="fw-bold mb-3">Detail pesan</h5>
                    @if($selectedMessage)
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="mb-0">{{ $selectedMessage->subjek }}</h6>
                                <small class="text-muted">
                                    {{ $selectedMessage->nama }} · {{ $selectedMessage->created_at->translatedFormat('d M Y H:i') }}
                                </small>
                            </div>
                            <span class="badge rounded-pill {{ $selectedMessage->is_read ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $selectedMessage->is_read ? 'Sudah dibaca' : 'Belum dibaca' }}
                            </span>
                        </div>
                        <p class="mb-0" style="white-space: pre-line;">{{ $selectedMessage->pesan }}</p>
                        <div class="mt-4 d-flex flex-wrap gap-2">
                            <form action="{{ route('admin.pesan.status', $selectedMessage) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_read" value="{{ $selectedMessage->is_read ? 0 : 1 }}">
                                <button type="submit" class="btn btn-sm btn-{{ $selectedMessage->is_read ? 'outline-warning' : 'outline-success' }}">
                                    {{ $selectedMessage->is_read ? 'Tandai belum dibaca' : 'Tandai sudah dibaca' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.pesan.destroy', $selectedMessage) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    Hapus
                                </button>
                            </form>
                            <a href="{{ route('admin.pesan.index', request()->except('focus')) }}" class="btn btn-sm btn-outline-secondary">Kembali ke daftar</a>
                        </div>
                    @else
                        <p class="text-muted">Pilih pesan untuk melihat detail dan tindak lanjut.</p>
                    @endif
                    <div class="mt-auto">
                        <small class="text-muted">Pesan yang sudah ditandai dibaca akan membantu tim komunikasi menjawab lebih cepat.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection