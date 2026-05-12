@extends('layouts.admin')

@section('content')
@php
    $considerationsText = old('considerations_text', implode("\n", $letter->considerations ?? []));
    $legalBasisText = old('legal_basis_text', implode("\n", $letter->legal_basis ?? []));
    $decisions = old('decisions', $letter->decisions ?? []);
    $instructorRows = old('instructor_team', $letter->instructor_team ?? []);
    $recruitmentRows = old('recruitment_team', $letter->recruitment_team ?? []);
    $managementRows = old('management_team', $letter->management_team ?? []);
    $filterBatch = old('batch_id', $filters['batch_id'] ?? $letter->batch_id);
    $filterYear = old('period_year', $filters['period_year'] ?? $letter->period_year);
    $filterProgram = old('program_id', $filters['program_id'] ?? null);
    $filterDateFrom = old('date_from', $filters['date_from'] ?? null);
    $filterDateTo = old('date_to', $filters['date_to'] ?? null);
    $filterAction = $letter->exists ? route('admin.decision-letter.edit', $letter->id) : route('admin.decision-letter.create');
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $letter->exists ? 'Ubah' : 'Buat' }} Surat Keputusan</h4>
        <small class="text-muted">Gunakan filter jadwal agar SK terisi otomatis.</small>
    </div>
    <a href="{{ route('admin.decision-letter.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form method="GET" action="{{ $filterAction }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Batch/Week</label>
                <select name="batch_id" class="form-select">
                    <option value="">Semua batch</option>
                    @foreach($batchOptions as $batch)
                        <option value="{{ $batch }}" @selected($filterBatch === $batch)>{{ $batch }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tahun</label>
                <select name="period_year" class="form-select">
                    <option value="">Semua</option>
                    @foreach($yearOptions as $year)
                        <option value="{{ $year }}" @selected((string) $filterYear === (string) $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kejuruan</label>
                <select name="program_id" class="form-select">
                    <option value="">Semua Kejuruan</option>
                    @foreach($programOptions as $id => $label)
                        <option value="{{ $id }}" @selected((string) $filterProgram === (string) $id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Mulai</label>
                <input type="date" name="date_from" class="form-control" value="{{ $filterDateFrom }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Selesai</label>
                <input type="date" name="date_to" class="form-control" value="{{ $filterDateTo }}">
            </div>
            <div class="col-md-12 text-end">
                <button class="btn btn-outline-secondary btn-sm">Terapkan Filter</button>
            </div>
        </form>
        <div class="mt-3" id="schedulePreview">
            @if($schedules->isEmpty())
                <div class="text-muted">Belum ada jadwal untuk filter yang dipilih.</div>
            @else
                <div class="fw-semibold">Jadwal terpilih ({{ $schedules->count() }} program)</div>
                <ul class="small text-muted mb-0">
                    @foreach($schedules as $schedule)
                        <li>{{ $schedule->program?->judul ?? '-' }} — {{ $schedule->judul ?? '-' }} ({{ $schedule->mulai }} s/d {{ $schedule->selesai }})</li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div id="curriculumWarning" class="{{ empty($missingCurricula) ? 'd-none' : '' }}" data-preview-url="{{ route('admin.decision-letter.preview') }}">
            @if(!empty($missingCurricula))
                <div class="alert alert-warning mt-3 mb-0">
                    <div class="fw-semibold">Kurikulum belum lengkap</div>
                    <div class="small">Program berikut belum memiliki kurikulum/unit kompetensi, sehingga SK tidak bisa diterbitkan:</div>
                    <ul class="mb-0 small">
                        @foreach($missingCurricula as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>

<form action="{{ $action }}" method="POST" class="bg-white rounded shadow-sm p-4" novalidate>
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <input type="hidden" name="batch_id" value="{{ $filterBatch }}">
    <input type="hidden" name="period_year" value="{{ $filterYear }}">
    <input type="hidden" name="program_id" value="{{ $filterProgram }}">
    <input type="hidden" name="date_from" value="{{ $filterDateFrom }}">
    <input type="hidden" name="date_to" value="{{ $filterDateTo }}">

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nomor SK</label>
            <input type="text" name="letter_number" class="form-control" value="{{ old('letter_number', $letter->letter_number) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Status SK</label>
            <select name="status" class="form-select">
                <option value="draft" @selected(old('status', $letter->status) === 'draft')>Draft</option>
                <option value="published" @selected(old('status', $letter->status) === 'published')>Terbit</option>
            </select>
        </div>
        <div class="col-md-12">
            <label class="form-label">Tentang</label>
            <textarea name="subject" rows="4" class="form-control">{{ old('subject', $letter->subject) }}</textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">Menimbang (satu baris per poin)</label>
            <textarea name="considerations_text" rows="3" class="form-control">{{ $considerationsText }}</textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">Mengingat (satu baris per poin)</label>
            <textarea name="legal_basis_text" rows="4" class="form-control">{{ $legalBasisText }}</textarea>
        </div>
    </div>

    <hr class="my-4">
    <h6 class="fw-semibold">Memutuskan</h6>
    <div class="row g-3">
        <div class="col-md-12">
            <label class="form-label">KESATU</label>
            <textarea name="decisions[kesatu]" rows="3" class="form-control">{{ $decisions['kesatu'] ?? '' }}</textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">KEDUA</label>
            <textarea name="decisions[kedua]" rows="2" class="form-control">{{ $decisions['kedua'] ?? '' }}</textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">KETIGA</label>
            <textarea name="decisions[ketiga]" rows="2" class="form-control">{{ $decisions['ketiga'] ?? '' }}</textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">KEEMPAT</label>
            <textarea name="decisions[keempat]" rows="2" class="form-control">{{ $decisions['keempat'] ?? '' }}</textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">KELIMA</label>
            <textarea name="decisions[kelima]" rows="2" class="form-control">{{ $decisions['kelima'] ?? '' }}</textarea>
        </div>
    </div>

    <hr class="my-4">
    <h6 class="fw-semibold">Lokasi Pelatihan</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Lokasi</label>
            <input type="text" name="location_name" class="form-control" value="{{ old('location_name', $letter->location_name) }}">
        </div>
    </div>

    <hr class="my-4">
    <h6 class="fw-semibold">Penandatangan SK</h6>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Kota</label>
            <input type="text" name="signed_city" class="form-control" value="{{ old('signed_city', $letter->signed_city) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal</label>
            <input type="date" name="signed_at" class="form-control" value="{{ old('signed_at', optional($letter->signed_at)->format('Y-m-d')) }}">
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <label class="form-label">Nama</label>
            <input type="text" name="signatory_name" class="form-control" value="{{ old('signatory_name', $letter->signatory_name) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Jabatan</label>
            <input type="text" name="signatory_position" class="form-control" value="{{ old('signatory_position', $letter->signatory_position) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">NIP</label>
            <input type="text" name="signatory_nip" class="form-control" value="{{ old('signatory_nip', $letter->signatory_nip) }}">
        </div>
    </div>

    <hr class="my-4">
    <h6 class="fw-semibold">Penandatangan Lampiran Kurikulum</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nama (Kiri)</label>
            <input type="text" name="approval_left_name" class="form-control" value="{{ old('approval_left_name', $letter->approval_left_name) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Nama (Kanan)</label>
            <input type="text" name="approval_right_name" class="form-control" value="{{ old('approval_right_name', $letter->approval_right_name) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Jabatan (Kiri)</label>
            <input type="text" name="approval_left_position" class="form-control" value="{{ old('approval_left_position', $letter->approval_left_position) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Jabatan (Kanan)</label>
            <input type="text" name="approval_right_position" class="form-control" value="{{ old('approval_right_position', $letter->approval_right_position) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">NIP (Kiri)</label>
            <input type="text" name="approval_left_nip" class="form-control" value="{{ old('approval_left_nip', $letter->approval_left_nip) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">NIP (Kanan)</label>
            <input type="text" name="approval_right_nip" class="form-control" value="{{ old('approval_right_nip', $letter->approval_right_nip) }}">
        </div>
    </div>

    <hr class="my-4">
    <h6 class="fw-semibold">Susunan Tim Instruktur</h6>
    <div id="instructorRows">
        @foreach($instructorRows as $row)
            <div class="row g-2 mb-2 align-items-end">
                <div class="col-md-4"><input type="text" name="instructor_team[name][]" class="form-control" placeholder="Nama" value="{{ $row['name'] ?? '' }}"></div>
                <div class="col-md-3"><input type="text" name="instructor_team[nip][]" class="form-control" placeholder="NIP" value="{{ $row['nip'] ?? '' }}"></div>
                <div class="col-md-3"><input type="text" name="instructor_team[position][]" class="form-control" placeholder="Jabatan" value="{{ $row['position'] ?? '' }}"></div>
                <div class="col-md-1"><input type="text" name="instructor_team[role][]" class="form-control" placeholder="Peran" value="{{ $row['role'] ?? '' }}"></div>
                <div class="col-md-1 text-end"><button type="button" class="btn btn-outline-danger btn-sm remove-row">&times;</button></div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm" id="addInstructorRow">Tambah Baris</button>

    <hr class="my-4">
    <h6 class="fw-semibold">Susunan Tim Rekrutmen</h6>
    <div id="recruitmentRows">
        @foreach($recruitmentRows as $row)
            <div class="row g-2 mb-2 align-items-end">
                <div class="col-md-4"><input type="text" name="recruitment_team[name][]" class="form-control" placeholder="Nama" value="{{ $row['name'] ?? '' }}"></div>
                <div class="col-md-3"><input type="text" name="recruitment_team[nip][]" class="form-control" placeholder="NIP" value="{{ $row['nip'] ?? '' }}"></div>
                <div class="col-md-3"><input type="text" name="recruitment_team[position][]" class="form-control" placeholder="Jabatan" value="{{ $row['position'] ?? '' }}"></div>
                <div class="col-md-1"><input type="text" name="recruitment_team[role][]" class="form-control" placeholder="Peran" value="{{ $row['role'] ?? '' }}"></div>
                <div class="col-md-1 text-end"><button type="button" class="btn btn-outline-danger btn-sm remove-row">&times;</button></div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm" id="addRecruitmentRow">Tambah Baris</button>

    <hr class="my-4">
    <h6 class="fw-semibold">Susunan Tim Pengelola</h6>
    <div id="managementRows">
        @foreach($managementRows as $row)
            <div class="row g-2 mb-2 align-items-end">
                <div class="col-md-4"><input type="text" name="management_team[name][]" class="form-control" placeholder="Nama" value="{{ $row['name'] ?? '' }}"></div>
                <div class="col-md-3"><input type="text" name="management_team[nip][]" class="form-control" placeholder="NIP" value="{{ $row['nip'] ?? '' }}"></div>
                <div class="col-md-3"><input type="text" name="management_team[position][]" class="form-control" placeholder="Jabatan" value="{{ $row['position'] ?? '' }}"></div>
                <div class="col-md-1"><input type="text" name="management_team[role][]" class="form-control" placeholder="Peran" value="{{ $row['role'] ?? '' }}"></div>
                <div class="col-md-1 text-end"><button type="button" class="btn btn-outline-danger btn-sm remove-row">&times;</button></div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm" id="addManagementRow">Tambah Baris</button>

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
                <div class="col-md-4"><input type="text" name="instructor_team[name][]" class="form-control" placeholder="Nama"></div>
                <div class="col-md-3"><input type="text" name="instructor_team[nip][]" class="form-control" placeholder="NIP"></div>
                <div class="col-md-3"><input type="text" name="instructor_team[position][]" class="form-control" placeholder="Jabatan"></div>
                <div class="col-md-1"><input type="text" name="instructor_team[role][]" class="form-control" placeholder="Peran"></div>
                <div class="col-md-1 text-end"><button type="button" class="btn btn-outline-danger btn-sm remove-row">&times;</button></div>
            </div>
        `);
    });

    document.getElementById('addRecruitmentRow')?.addEventListener('click', () => {
        addRow('recruitmentRows', `
            <div class="row g-2 mb-2 align-items-end">
                <div class="col-md-4"><input type="text" name="recruitment_team[name][]" class="form-control" placeholder="Nama"></div>
                <div class="col-md-3"><input type="text" name="recruitment_team[nip][]" class="form-control" placeholder="NIP"></div>
                <div class="col-md-3"><input type="text" name="recruitment_team[position][]" class="form-control" placeholder="Jabatan"></div>
                <div class="col-md-1"><input type="text" name="recruitment_team[role][]" class="form-control" placeholder="Peran"></div>
                <div class="col-md-1 text-end"><button type="button" class="btn btn-outline-danger btn-sm remove-row">&times;</button></div>
            </div>
        `);
    });

    document.getElementById('addManagementRow')?.addEventListener('click', () => {
        addRow('managementRows', `
            <div class="row g-2 mb-2 align-items-end">
                <div class="col-md-4"><input type="text" name="management_team[name][]" class="form-control" placeholder="Nama"></div>
                <div class="col-md-3"><input type="text" name="management_team[nip][]" class="form-control" placeholder="NIP"></div>
                <div class="col-md-3"><input type="text" name="management_team[position][]" class="form-control" placeholder="Jabatan"></div>
                <div class="col-md-1"><input type="text" name="management_team[role][]" class="form-control" placeholder="Peran"></div>
                <div class="col-md-1 text-end"><button type="button" class="btn btn-outline-danger btn-sm remove-row">&times;</button></div>
            </div>
        `);
    });

    document.addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-row')) {
            event.target.closest('.row')?.remove();
        }
    });

    const filterIds = ['batch_id', 'period_year', 'program_id', 'date_from', 'date_to'];
    const warningContainer = document.getElementById('curriculumWarning');
    const previewUrl = warningContainer?.dataset.previewUrl;
    let previewTimer = null;
    let previewController = null;

    function renderWarning(items, count) {
        if (!warningContainer) return;
        if (!items || items.length === 0) {
            warningContainer.innerHTML = '';
            warningContainer.classList.add('d-none');
            return;
        }
        const listItems = items.map((item) => `<li>${item}</li>`).join('');
        warningContainer.innerHTML = `
            <div class="alert alert-warning mt-3 mb-0">
                <div class="fw-semibold">Kurikulum belum lengkap</div>
                <div class="small">Program berikut belum memiliki kurikulum/unit kompetensi, sehingga SK tidak bisa diterbitkan:</div>
                <ul class="mb-0 small">${listItems}</ul>
                <div class="small text-muted mt-2">Total jadwal terdeteksi: ${count}</div>
            </div>
        `;
        warningContainer.classList.remove('d-none');
    }

    function fetchPreview() {
        if (!previewUrl) return;
        const params = new URLSearchParams();
        filterIds.forEach((id) => {
            const el = document.querySelector(`[name=\"${id}\"]`);
            if (el && el.value) {
                params.append(id, el.value);
            }
        });

        if (previewController) {
            previewController.abort();
        }
        previewController = new AbortController();

        fetch(`${previewUrl}?${params.toString()}`, { signal: previewController.signal })
            .then((res) => res.ok ? res.json() : Promise.reject(res))
            .then((data) => {
                renderWarning(data.missing || [], data.count || 0);
            })
            .catch((err) => {
                if (err?.name === 'AbortError') return;
            });
    }

    function schedulePreview() {
        if (previewTimer) clearTimeout(previewTimer);
        previewTimer = setTimeout(fetchPreview, 300);
    }

    filterIds.forEach((id) => {
        const el = document.querySelector(`[name=\"${id}\"]`);
        if (el) {
            el.addEventListener('change', schedulePreview);
            el.addEventListener('input', schedulePreview);
        }
    });
</script>
@endpush
