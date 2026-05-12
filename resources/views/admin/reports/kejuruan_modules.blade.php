@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Laporan Modul Kejuruan</h4>
        <small class="text-muted">Ringkasan kelengkapan modul per kejuruan.</small>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.kejuruan-modules') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Kejuruan</label>
                <select name="program_id" class="form-select">
                    <option value="">Semua kejuruan</option>
                    @foreach($programOptions as $id => $label)
                        <option value="{{ $id }}" @selected($programFilter === $id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Hanya kosong</label>
                <select name="missing_only" class="form-select">
                    <option value="0" @selected(!$missingOnly)>Semua</option>
                    <option value="1" @selected($missingOnly)>Belum unggah</option>
                </select>
            </div>
            <div class="col-md-4 d-grid">
                <button class="btn btn-primary">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body table-responsive">
        <table class="table table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>Kejuruan</th>
                    <th>Total Modul</th>
                    <th>Terakhir Perbarui</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['program_name'] }}</td>
                        <td>{{ $row['total'] }}</td>
                        <td>{{ $row['last_updated'] ? \Carbon\Carbon::parse($row['last_updated'])->format('d M Y H:i') : '-' }}</td>
                        <td>
                            @if($row['total'] === 0)
                                <span class="badge bg-danger">Belum Unggah</span>
                            @else
                                <span class="badge bg-success">Sudah Unggah</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Belum ada data untuk ditampilkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(! $missingOnly)
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body table-responsive">
            <h6 class="fw-semibold mb-3">Daftar Modul (Detail)</h6>
            @if($modulesByKejuruan->isEmpty())
                <div class="text-muted">Belum ada modul.</div>
            @else
                <div class="accordion" id="kejuruanModulesAccordion">
                    @foreach($modulesByKejuruan as $kejuruan => $items)
                        @php
                            $groupId = 'kejuruan-modul-' . $loop->index;
                        @endphp
                        <div class="accordion-item mb-2">
                            <h2 class="accordion-header" id="heading-{{ $groupId }}">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $groupId }}" aria-expanded="true" aria-controls="collapse-{{ $groupId }}">
                                    {{ strtoupper($kejuruan) }}
                                    <span class="badge bg-light text-muted ms-2">{{ $items->count() }}</span>
                                </button>
                            </h2>
                            <div id="collapse-{{ $groupId }}" class="accordion-collapse collapse show" aria-labelledby="heading-{{ $groupId }}" data-bs-parent="#kejuruanModulesAccordion">
                                <div class="accordion-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0 align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Judul Modul</th>
                                                    <th>Instruktur</th>
                                                    <th>Tautan</th>
                                                    <th>Diperbarui</th>
                                                    <th>Status</th>
                                                    <th class="text-end">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($items as $module)
                                                    <tr>
                                                        <td class="fw-semibold">{{ $module->title }}</td>
                                                        <td>{{ $module->owner?->name ?? '-' }}</td>
                                                        <td>
                                                            <a href="{{ $module->link_url }}" target="_blank" rel="noopener">Buka tautan</a>
                                                        </td>
                                                        <td>{{ $module->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                                                        <td>
                                                            <span class="badge {{ $module->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                                {{ $module->is_active ? 'Aktif' : 'Nonaktif' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('admin.kejuruan-modules.edit', $module->id) }}" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@else
    <div class="text-muted mt-3">Filter “Belum unggah” aktif — daftar modul disembunyikan.</div>
@endif
@endsection
