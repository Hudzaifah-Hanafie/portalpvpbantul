@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $pageTitle }}</h4>
        <small class="text-muted">Kelola modul berbasis kejuruan dengan tautan eksternal (Google Drive/OneDrive).</small>
    </div>
    <a href="{{ route($routePrefix . '.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Tambah Modul
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-warning">{{ session('error') }}</div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" action="{{ route($routePrefix . '.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Kejuruan</label>
                <select name="program_id" class="form-select">
                    <option value="">Semua kejuruan</option>
                    @foreach($programOptions as $id => $label)
                        <option value="{{ $id }}" @selected(($programFilter ?? null) === $id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if($showOwner)
                <div class="col-md-3">
                    <label class="form-label">Instruktur</label>
                    <select name="owner_id" class="form-select">
                        <option value="">Semua instruktur</option>
                        @foreach($ownerOptions as $id => $label)
                            <option value="{{ $id }}" @selected(($ownerFilter ?? null) === $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-md-{{ $showOwner ? '3' : '4' }}">
                <label class="form-label">Cari judul</label>
                <input type="text" name="q" class="form-control" value="{{ $search }}" placeholder="Contoh: Modul AutoCAD">
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="mt-4">
    @if($modulesByKejuruan->isEmpty())
        <div class="text-muted">Belum ada modul kejuruan.</div>
    @else
        <div class="accordion" id="kejuruanModulesAccordion">
            @foreach($modulesByKejuruan as $kejuruan => $modules)
                @php
                    $groupId = 'kejuruan-mod-' . $loop->index;
                @endphp
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="heading-{{ $groupId }}">
                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $groupId }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse-{{ $groupId }}">
                            {{ strtoupper($kejuruan) }}
                            <span class="badge bg-light text-muted ms-2">{{ $modules->count() }}</span>
                        </button>
                    </h2>
                    <div id="collapse-{{ $groupId }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading-{{ $groupId }}" data-bs-parent="#kejuruanModulesAccordion">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Judul Modul</th>
                                            <th>Link</th>
                                            <th>Diperbarui</th>
                                            @if($showOwner)
                                                <th>Instruktur</th>
                                            @endif
                                            <th>Status</th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($modules as $module)
                                            <tr>
                                                <td class="fw-semibold">{{ $module->title }}</td>
                                                <td>
                                                    <a href="{{ $module->link_url }}" target="_blank" rel="noopener">
                                                        Buka tautan
                                                    </a>
                                                </td>
                                                <td>{{ $module->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                                                @if($showOwner)
                                                    <td>{{ $module->owner?->name ?? '-' }}</td>
                                                @endif
                                                <td>
                                                    <span class="badge {{ $module->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $module->is_active ? 'Aktif' : 'Nonaktif' }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route($routePrefix . '.edit', $module->id) }}" class="btn btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route($routePrefix . '.destroy', $module->id) }}" method="POST" onsubmit="return confirm('Hapus modul ini?')" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $showOwner ? 6 : 5 }}" class="text-center text-muted py-3">Belum ada modul.</td>
                                            </tr>
                                        @endforelse
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
@endsection
