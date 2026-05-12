@extends('layouts.admin')

@php
    $statusOptions = $statusOptions ?? \App\Models\FaqItem::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Daftar FAQ</h4>
        <small class="text-muted">Pertanyaan dan jawaban yang ditampilkan pada halaman publik.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('resource.faq') }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat Halaman</a>
        <a href="{{ route('admin.faq-item.create') }}" class="btn btn-primary btn-sm">Tambah FAQ</a>
    </div>
</div>

<div class="bg-white rounded shadow-sm">
    <form method="GET" class="p-3 border-bottom">
        <div class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
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
                    <a href="{{ route('admin.faq-item.index') }}" class="btn btn-sm btn-link text-decoration-none">Atur Ulang</a>
                </div>
            @endif
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Pertanyaan</th>
                    <th>Kategori</th>
                    <th>Urutan</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $item->question }}</strong>
                            <div class="text-muted small">{{ \Illuminate\Support\Str::limit(strip_tags($item->answer), 100) }}</div>
                        </td>
                        <td>{{ $item->category->title ?? '-' }}</td>
                        <td>{{ $item->urutan }}</td>
                        <td class="text-nowrap">
                            @php
                                $status = $item->status ?? 'draft';
                                $badgeClass = [
                                    'draft' => 'bg-secondary',
                                    'pending' => 'bg-warning text-dark',
                                    'published' => 'bg-success',
                                ][$status] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $statusOptions[$status] ?? ucfirst($status) }}</span>
                            <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-dark' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.faq-item.edit', $item) }}" class="btn btn-sm btn-warning">Ubah</a>
                            <form action="{{ route('admin.faq-item.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus FAQ ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada FAQ.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
