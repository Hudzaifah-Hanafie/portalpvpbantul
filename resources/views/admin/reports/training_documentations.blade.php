@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Laporan Dokumentasi Pelatihan</h4>
        <small class="text-muted">Dokumentasi berbasis tautan eksternal yang diinput instruktur.</small>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.training-documentations') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Kejuruan</label>
                <select name="program_id" class="form-select">
                    <option value="">Semua kejuruan</option>
                    @foreach($programOptions as $id => $label)
                        <option value="{{ $id }}" @selected($programFilter === $id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Program Pelatihan</label>
                <select name="schedule_id" class="form-select">
                    <option value="">Semua program</option>
                    @foreach($scheduleOptions as $id => $label)
                        <option value="{{ $id }}" @selected($scheduleFilter === $id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button class="btn btn-primary">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        @if($missingItems->isNotEmpty())
            <div class="alert alert-warning">
                <div class="fw-semibold mb-1">Dokumentasi masih kosong untuk {{ $missingItems->count() }} program pelatihan.</div>
                <div class="small text-muted mb-2">Silakan ingatkan instruktur untuk mengunggah tautan dokumentasi.</div>
                <div class="small">
                    <ul class="mb-0">
                        @foreach($missingItems as $schedule)
                            <li>
                                {{ $schedule->program?->judul ?? 'LAINNYA' }} • {{ $schedule->judul }}
                                @if($schedule->batch_id)
                                    ({{ $schedule->batch_id }})
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        @if($groups->isEmpty())
            <div class="text-muted">Belum ada dokumentasi pelatihan.</div>
        @else
            <div class="accordion" id="documentationReportAccordion">
                @foreach($groups as $kejuruan => $scheduleItems)
                    @php
                        $groupId = 'report-doc-' . $loop->index;
                    @endphp
                    <div class="accordion-item mb-2">
                        <h2 class="accordion-header" id="heading-{{ $groupId }}">
                            <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $groupId }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse-{{ $groupId }}">
                                {{ strtoupper($kejuruan) }}
                                <span class="badge bg-light text-muted ms-2">{{ $scheduleItems->count() }}</span>
                            </button>
                        </h2>
                        <div id="collapse-{{ $groupId }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading-{{ $groupId }}" data-bs-parent="#documentationReportAccordion">
                            <div class="accordion-body p-0">
                                <div class="accordion" id="scheduleReportAccordion-{{ $groupId }}">
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
                                            <div id="collapse-{{ $scheduleId }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $scheduleId }}" data-bs-parent="#scheduleReportAccordion-{{ $groupId }}">
                                                <div class="accordion-body p-0">
                                                    @if($schedule['docs']->isEmpty())
                                                        <div class="p-3 text-muted">Belum ada dokumentasi untuk program ini.</div>
                                                    @else
                                                        <div class="table-responsive">
                                                            <table class="table table-sm mb-0 align-middle">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>Judul</th>
                                                                        <th>Tautan</th>
                                                                        <th>Tanggal</th>
                                                                        <th>Instruktur</th>
                                                                        <th>Diperbarui</th>
                                                                        <th>Status</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($schedule['docs'] as $doc)
                                                                        <tr>
                                                                            <td class="fw-semibold">{{ $doc->title }}</td>
                                                                            <td><a href="{{ $doc->link_url }}" target="_blank" rel="noopener">Buka tautan</a></td>
                                                                            <td>{{ $doc->documented_at?->format('d M Y') ?? '-' }}</td>
                                                                            <td>{{ $doc->owner?->name ?? '-' }}</td>
                                                                            <td>{{ $doc->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                                                                            <td>
                                                                                <span class="badge {{ $doc->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                                                    {{ $doc->is_active ? 'Aktif' : 'Nonaktif' }}
                                                                                </span>
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
</div>
@endsection
