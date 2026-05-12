@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $pageTitle }}</h4>
        <small class="text-muted">Simpan tautan dokumentasi (Google Drive/OneDrive/dll) agar hemat storage.</small>
    </div>
    <a href="{{ route($routePrefix . '.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Tambah Dokumentasi
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
            <div class="col-md-4">
                <label class="form-label">Program Pelatihan</label>
                <select name="schedule_id" class="form-select">
                    <option value="">Semua program</option>
                    @foreach($scheduleOptions as $id => $label)
                        <option value="{{ $id }}" @selected(($scheduleFilter ?? null) === $id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Cari judul</label>
                <input type="text" name="q" class="form-control" value="{{ $search }}" placeholder="Contoh: Dokumentasi Hari 1">
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="mt-4">
    @if($groups->isEmpty())
        <div class="text-muted">Belum ada dokumentasi pelatihan.</div>
    @else
        <div class="accordion" id="documentationKejuruanAccordion">
            @foreach($groups as $kejuruan => $scheduleItems)
                @php
                    $groupId = 'doc-kejuruan-' . $loop->index;
                @endphp
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="heading-{{ $groupId }}">
                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $groupId }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse-{{ $groupId }}">
                            {{ strtoupper($kejuruan) }}
                            <span class="badge bg-light text-muted ms-2">{{ $scheduleItems->count() }}</span>
                        </button>
                    </h2>
                    <div id="collapse-{{ $groupId }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading-{{ $groupId }}" data-bs-parent="#documentationKejuruanAccordion">
                        <div class="accordion-body p-0">
                            <div class="accordion" id="scheduleAccordion-{{ $groupId }}">
                                @foreach($scheduleItems as $schedule)
                                    @php
                                        $scheduleId = $groupId . '-schedule-' . $loop->index;
                                    @endphp
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading-{{ $scheduleId }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $scheduleId }}" aria-expanded="false" aria-controls="collapse-{{ $scheduleId }}">
                                                <div class="w-100">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="fw-semibold">{{ $schedule['title'] }}</span>
                                                        <span class="badge bg-light text-muted">{{ $schedule['docs']->count() }} dok</span>
                                                    </div>
                                                    <div class="small text-muted mt-1">Batch: {{ $schedule['batch'] }} • {{ $schedule['date_range'] }}</div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse-{{ $scheduleId }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $scheduleId }}" data-bs-parent="#scheduleAccordion-{{ $groupId }}">
                                            <div class="accordion-body p-0">
                                                @if($schedule['docs']->isEmpty())
                                                    <div class="p-3 text-muted">Belum ada dokumentasi untuk program ini.</div>
                                                @else
                                                    <div class="table-responsive">
                                                        <table class="table table-sm mb-0 align-middle">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Judul</th>
                                                                    <th>Link</th>
                                                                    <th>Tanggal</th>
                                                                    <th>Diperbarui</th>
                                                                    <th>Status</th>
                                                                    <th class="text-end">Aksi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($schedule['docs'] as $doc)
                                                                    <tr>
                                                                        <td class="fw-semibold">{{ $doc->title }}</td>
                                                                        <td><a href="{{ $doc->link_url }}" target="_blank" rel="noopener">Buka tautan</a></td>
                                                                        <td>{{ $doc->documented_at?->format('d M Y') ?? '-' }}</td>
                                                                        <td>{{ $doc->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                                                                        <td>
                                                                            <span class="badge {{ $doc->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                                                {{ $doc->is_active ? 'Aktif' : 'Nonaktif' }}
                                                                            </span>
                                                                        </td>
                                                                        <td class="text-end">
                                                                            <div class="btn-group btn-group-sm" role="group">
                                                                                <a href="{{ route($routePrefix . '.edit', $doc->id) }}" class="btn btn-outline-primary">
                                                                                    <i class="fas fa-edit"></i>
                                                                                </a>
                                                                                <form action="{{ route($routePrefix . '.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Hapus dokumentasi ini?')" class="d-inline">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                                                                                </form>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
