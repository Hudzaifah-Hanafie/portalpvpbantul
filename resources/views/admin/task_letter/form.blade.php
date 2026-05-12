@extends('layouts.admin')

@section('content')
@php
    $instructorRows = old('instructors', $letter->instructors ?? []);
    $committeeRows = old('committees', $letter->committees ?? []);
    $participantStatuses = old('participant_statuses', $letter->participant_statuses ?? ['approved','active','completed']);
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $letter->exists ? 'Ubah' : 'Buat' }} Surat Tugas</h4>
        <small class="text-muted">Lengkapi data ST/SK otomatis.</small>
    </div>
    <a href="{{ route(request()->routeIs('instructor.*') ? 'instructor.lms.task-letter.index' : 'admin.task-letter.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

@if($selectedClass)
    <div class="alert alert-info d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-semibold">Kelas: {{ $selectedClass->title }}</div>
            <small class="text-muted">Instruktur: {{ $selectedClass->instructor?->name ?? '-' }} • Format: {{ ucfirst($selectedClass->format ?? '-') }}</small>
        </div>
        <a href="{{ route(request()->routeIs('instructor.*') ? 'instructor.lms.course-session.index' : 'admin.course-session.index', ['class_id' => $selectedClass->id]) }}" class="btn btn-outline-primary btn-sm">
            Lihat Sesi
        </a>
    </div>
@endif

<form action="{{ $action }}" method="POST" class="bg-white rounded shadow-sm p-4" novalidate>
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Kelas</label>
            <select name="course_class_id" class="form-select @error('course_class_id') is-invalid @enderror" required>
                <option value="">Pilih kelas</option>
                @foreach($classes as $id => $label)
                    <option value="{{ $id }}" @selected(old('course_class_id', $letter->course_class_id) === $id)>{{ $label }}</option>
                @endforeach
            </select>
            @error('course_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Nomor Surat (ST/SK)</label>
            <input type="text" name="letter_number" class="form-control @error('letter_number') is-invalid @enderror" value="{{ old('letter_number', $letter->letter_number) }}">
            @error('letter_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mt-3">
        <label class="form-label">Dasar Hukum</label>
        <textarea name="legal_basis" rows="3" class="form-control @error('legal_basis') is-invalid @enderror">{{ old('legal_basis', $letter->legal_basis) }}</textarea>
        @error('legal_basis') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="row g-3 mt-2">
        <div class="col-md-3">
            <label class="form-label">Tanggal Mulai</label>
            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', optional($letter->start_date)->format('Y-m-d')) }}">
            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">Tanggal Selesai</label>
            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', optional($letter->end_date)->format('Y-m-d')) }}">
            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">Jam Mulai</label>
            <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time', $letter->start_time) }}">
            @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">Jam Selesai</label>
            <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time', $letter->end_time) }}">
            @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row g-3 mt-2">
        <div class="col-md-6">
            <label class="form-label">Lokasi</label>
            <input type="text" name="location_name" class="form-control @error('location_name') is-invalid @enderror" value="{{ old('location_name', $letter->location_name) }}">
            @error('location_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Tautan Lokasi (Zoom/Meet)</label>
            <input type="text" name="location_link" class="form-control @error('location_link') is-invalid @enderror" value="{{ old('location_link', $letter->location_link) }}">
            @error('location_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <hr class="my-4">
    <h6 class="fw-semibold">Daftar Instruktur/Tenaga Pelatih</h6>
    <div id="instructorRows">
        @foreach($instructorRows as $i => $row)
            <div class="row g-2 mb-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="instructors[name][]" class="form-control" placeholder="Nama" value="{{ $row['name'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <input type="text" name="instructors[nip][]" class="form-control" placeholder="NIP" value="{{ $row['nip'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="instructors[position][]" class="form-control" placeholder="Jabatan" value="{{ $row['position'] ?? '' }}">
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-row">&times;</button>
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm" id="addInstructorRow">Tambah Instruktur</button>

    <hr class="my-4">
    <h6 class="fw-semibold">Daftar Panitia Penyelenggara</h6>
    <div id="committeeRows">
        @foreach($committeeRows as $i => $row)
            <div class="row g-2 mb-2 align-items-end">
                <div class="col-md-5">
                    <input type="text" name="committees[name][]" class="form-control" placeholder="Nama" value="{{ $row['name'] ?? '' }}">
                </div>
                <div class="col-md-5">
                    <input type="text" name="committees[role][]" class="form-control" placeholder="Peran (Ketua/Sekretaris/Anggota)" value="{{ $row['role'] ?? '' }}">
                </div>
                <div class="col-md-2 text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-row">&times;</button>
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm" id="addCommitteeRow">Tambah Panitia</button>

    <hr class="my-4">
    <h6 class="fw-semibold">Daftar Peserta (Nominatif)</h6>
    <div class="row g-2">
        @foreach(['approved' => 'Disetujui', 'active' => 'Aktif', 'completed' => 'Selesai', 'pending' => 'Pending'] as $key => $label)
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="participant_statuses[]" value="{{ $key }}" {{ in_array($key, $participantStatuses, true) ? 'checked' : '' }}>
                    <label class="form-check-label">{{ $label }}</label>
                </div>
            </div>
        @endforeach
    </div>

    <hr class="my-4">
    <h6 class="fw-semibold">Penandatangan</h6>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Kota</label>
            <input type="text" name="signed_city" class="form-control" value="{{ old('signed_city', $letter->signed_city) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal</label>
            <input type="date" name="signed_at" class="form-control" value="{{ old('signed_at', optional($letter->signed_at)->format('Y-m-d')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Status Surat</label>
            <select name="status" class="form-select">
                <option value="draft" @selected(old('status', $letter->status) === 'draft')>Draft</option>
                <option value="published" @selected(old('status', $letter->status) === 'published')>Terbit</option>
            </select>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <label class="form-label">Nama Penandatangan</label>
            <input type="text" name="signatory_name" class="form-control" value="{{ old('signatory_name', $letter->signatory_name) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Jabatan Penandatangan</label>
            <input type="text" name="signatory_position" class="form-control" value="{{ old('signatory_position', $letter->signatory_position) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">NIP Penandatangan</label>
            <input type="text" name="signatory_nip" class="form-control" value="{{ old('signatory_nip', $letter->signatory_nip) }}">
        </div>
    </div>

    <div class="text-end mt-4">
        <button class="btn btn-primary px-4">{{ $letter->exists ? 'Perbarui' : 'Simpan' }}</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    function addRow(containerId, templateHtml) {
        const container = document.getElementById(containerId);
        if (!container) return;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = templateHtml.trim();
        container.appendChild(wrapper.firstChild);
    }

    document.getElementById('addInstructorRow')?.addEventListener('click', () => {
        addRow('instructorRows', `
            <div class="row g-2 mb-2 align-items-end">
                <div class="col-md-4"><input type="text" name="instructors[name][]" class="form-control" placeholder="Nama"></div>
                <div class="col-md-4"><input type="text" name="instructors[nip][]" class="form-control" placeholder="NIP"></div>
                <div class="col-md-3"><input type="text" name="instructors[position][]" class="form-control" placeholder="Jabatan"></div>
                <div class="col-md-1 text-end"><button type="button" class="btn btn-outline-danger btn-sm remove-row">&times;</button></div>
            </div>
        `);
    });

    document.getElementById('addCommitteeRow')?.addEventListener('click', () => {
        addRow('committeeRows', `
            <div class="row g-2 mb-2 align-items-end">
                <div class="col-md-5"><input type="text" name="committees[name][]" class="form-control" placeholder="Nama"></div>
                <div class="col-md-5"><input type="text" name="committees[role][]" class="form-control" placeholder="Peran (Ketua/Sekretaris/Anggota)"></div>
                <div class="col-md-2 text-end"><button type="button" class="btn btn-outline-danger btn-sm remove-row">&times;</button></div>
            </div>
        `);
    });

    document.addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-row')) {
            event.target.closest('.row')?.remove();
        }
    });
</script>
@endpush
