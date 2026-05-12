@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Nominatif Peserta</h4>
        <small class="text-muted">Daftar nominatif otomatis dari peserta terdaftar.</small>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="GET" action="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-nominative.index' : 'admin.course-nominative.index')) }}" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Kejuruan</label>
                <select name="kejuruan_id" class="form-select">
                    <option value="">Semua kejuruan</option>
                    @foreach($programOptions as $id => $label)
                        <option value="{{ $id }}" @selected($kejuruanFilter === $id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Batch</label>
                <select name="batch_id" class="form-select">
                    <option value="">Semua batch</option>
                    @foreach($batchOptions as $id => $label)
                        <option value="{{ $id }}" @selected($batchFilter === $id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Instruktur</label>
                <select name="instructor_id" class="form-select">
                    <option value="">Semua instruktur</option>
                    @foreach($instructors as $id => $label)
                        <option value="{{ $id }}" @selected($instructorFilter === $id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status Peserta</label>
                <select name="status" class="form-select">
                    <option value="" @selected(!$statusFilter)>Semua</option>
                    <option value="approved" @selected($statusFilter === 'approved')>Disetujui</option>
                    <option value="active" @selected($statusFilter === 'active')>Aktif</option>
                    <option value="completed" @selected($statusFilter === 'completed')>Selesai</option>
                    <option value="pending" @selected($statusFilter === 'pending')>Pending</option>
                    <option value="rejected" @selected($statusFilter === 'rejected')>Ditolak</option>
                    <option value="blocked" @selected($statusFilter === 'blocked')>Diblokir</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status Admin</label>
                <select name="admin_status" class="form-select">
                    <option value="">Semua</option>
                    @foreach($adminStatuses as $key => $label)
                        <option value="{{ $key }}" @selected($adminStatusFilter === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button class="btn btn-primary">Tampilkan</button>
            </div>
        </form>

        <div class="mt-4">
            <div class="text-muted mb-3">Gunakan tombol cetak untuk melihat format nominatif resmi.</div>
            @if($kejuruanGroups->isEmpty())
                <div class="text-muted">Belum ada data kejuruan atau pelatihan yang tersedia.</div>
            @else
                <div class="accordion" id="nominatifKejuruan">
                    @foreach($kejuruanGroups as $kejuruan => $items)
                        @php
                            $groupId = 'kejuruan-' . $loop->index;
                            $isEmpty = $items->isEmpty();
                        @endphp
                        <div class="accordion-item mb-2">
                            <h2 class="accordion-header" id="heading-{{ $groupId }}">
                                <button class="accordion-button {{ $isEmpty ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $groupId }}" aria-expanded="{{ $isEmpty ? 'false' : 'true' }}" aria-controls="collapse-{{ $groupId }}">
                                    {{ strtoupper($kejuruan) }}
                                    <span class="badge bg-light text-muted ms-2">{{ $items->count() }}</span>
                                </button>
                            </h2>
                            <div id="collapse-{{ $groupId }}" class="accordion-collapse collapse {{ $isEmpty ? '' : 'show' }}" aria-labelledby="heading-{{ $groupId }}" data-bs-parent="#nominatifKejuruan">
                                <div class="accordion-body p-0">
                                    @if($isEmpty)
                                        <div class="p-3 text-muted">Belum ada nominatif di kejuruan ini.</div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0 align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Pelatihan/Kelas</th>
                                                        <th>Batch</th>
                                                        <th>Tanggal</th>
                                                        <th>Instruktur</th>
                                                        <th class="text-end">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($items as $item)
                                                        <tr>
                                                            <td class="fw-semibold">{{ $item['title'] }}</td>
                                                            <td>{{ $item['batch'] }}</td>
                                                            <td>{{ $item['tanggal'] }}</td>
                                                            <td>{{ $item['instructor'] }}</td>
                                                            <td class="text-end">
                                                                <div class="btn-group btn-group-sm" role="group">
                                                                    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-nominative.print' : 'admin.course-nominative.print'), $item['query']) }}" target="_blank" class="btn btn-outline-success">
                                                                        <i class="fas fa-print"></i>
                                                                    </a>
                                                                    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-nominative.pdf' : 'admin.course-nominative.pdf'), $item['query']) }}" class="btn btn-outline-danger">
                                                                        <i class="fas fa-file-pdf"></i>
                                                                    </a>
                                                                    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-nominative.export' : 'admin.course-nominative.export'), $item['query']) }}" class="btn btn-outline-primary">
                                                                        <i class="fas fa-file-excel"></i>
                                                                    </a>
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
            @endif
        </div>
    </div>
</div>
@endsection
