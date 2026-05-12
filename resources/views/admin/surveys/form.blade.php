@extends('layouts.admin')

@php
    $isEdit = $survey->exists;
    $initialQuestions = ($questions ?? collect())->map(function ($q) {
        return [
            'id' => $q->id,
            'question' => $q->question,
            'description' => $q->description,
            'type' => $q->type,
            'is_required' => $q->is_required,
            'placeholder' => $q->placeholder,
            'position' => $q->position,
            'settings' => $q->settings ?? [],
            'options' => $q->options->map(function ($o) {
                return [
                    'id' => $o->id,
                    'label' => $o->label,
                    'value' => $o->value,
                    'position' => $o->position,
                    'is_other' => $o->is_other,
                ];
            })->values(),
            'section_key' => optional($q->section)->id,
            'validation' => $q->validation ?? [],
            'visibility_rules' => $q->visibility_rules ?? [],
        ];
    })->values();

    $oldPayload = old('questions_payload');
    if ($oldPayload) {
        $decoded = json_decode($oldPayload, true);
        if (is_array($decoded)) {
            $initialQuestions = collect($decoded);
        }
    }

    $initialSections = ($sections ?? collect())->map(function ($s) {
        return [
            'id' => $s->id,
            'key' => $s->id,
            'title' => $s->title,
            'description' => $s->description,
            'position' => $s->position,
        ];
    })->values();
    $oldSections = old('sections_payload');
    if ($oldSections && is_array(json_decode($oldSections, true))) {
        $initialSections = collect(json_decode($oldSections, true));
    }

        $initialSkipRules = collect(json_decode(old('skip_rules_payload', '[]'), true));
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $isEdit ? 'Ubah Survei' : 'Buat Survei Baru' }}</h4>
        <small class="text-muted">Susun pertanyaan fleksibel mirip Google Form dan atur jadwal publikasinya.</small>
    </div>
    <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Validasi gagal.</strong> Pastikan semua pertanyaan terisi dan opsi pilihan tidak kosong.
    </div>
@endif

<form id="survey-form" action="{{ $isEdit ? route('admin.surveys.update', $survey) : route('admin.surveys.store') }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul Survei</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $survey->title) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi singkat</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $survey->description) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kalimat pembuka</label>
                        <textarea name="welcome_message" class="form-control" rows="2" placeholder="Contoh: Isi survey ini untuk membantu kami meningkatkan layanan.">{{ old('welcome_message', $survey->welcome_message) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pesan terima kasih</label>
                        <textarea name="thank_you_message" class="form-control" rows="2" placeholder="Contoh: Terima kasih, respon Anda terekam.">{{ old('thank_you_message', $survey->thank_you_message) }}</textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Buka pada</label>
                            <input type="datetime-local" name="opens_at" class="form-control" value="{{ old('opens_at', optional($survey->opens_at)->format('Y-m-d\TH:i')) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Tutup pada</label>
                            <input type="datetime-local" name="closes_at" class="form-control" value="{{ old('closes_at', optional($survey->closes_at)->format('Y-m-d\TH:i')) }}">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-6">
                            <label class="form-label">Limit respons</label>
                            <input type="number" name="max_responses" class="form-control" min="1" value="{{ old('max_responses', $survey->max_responses) }}" placeholder="Opsional">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Progress bar</label>
                            <select name="show_progress" class="form-select">
                                <option value="1" {{ old('show_progress', $survey->show_progress ?? true) ? 'selected' : '' }}>Tampilkan</option>
                                <option value="0" {{ old('show_progress', $survey->show_progress ?? true) ? '' : 'selected' }}>Sembunyikan</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $survey->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Aktifkan survey</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="require_login" name="require_login" value="1" {{ old('require_login', $survey->require_login) ? 'checked' : '' }}>
                            <label class="form-check-label" for="require_login">Wajib login</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="allow_multiple_responses" name="allow_multiple_responses" value="1" {{ old('allow_multiple_responses', $survey->allow_multiple_responses ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_multiple_responses">Izinkan submit berulang</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="shuffle_questions" name="shuffle_questions" value="1" {{ old('shuffle_questions', data_get($survey->settings, 'shuffle_questions')) ? 'checked' : '' }}>
                            <label class="form-check-label" for="shuffle_questions">Acak urutan pertanyaan</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="restrict_to_logged_in" name="restrict_to_logged_in" value="1" {{ old('restrict_to_logged_in', $survey->restrict_to_logged_in) ? 'checked' : '' }}>
                            <label class="form-check-label" for="restrict_to_logged_in">Hanya user login</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="allow_embed" name="allow_embed" value="1" {{ old('allow_embed', $survey->allow_embed ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_embed">Ijinkan embed</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <h6 class="mb-2">Tema & Branding</h6>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Primary color</label>
                            <input type="color" name="theme_primary" class="form-control form-control-color" value="{{ old('theme_primary', data_get($survey->theme, 'primary', '#2563eb')) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Font</label>
                            <input type="text" name="theme_font" class="form-control" value="{{ old('theme_font', data_get($survey->theme, 'font', 'Inter, sans-serif')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Cover Image URL</label>
                            <input type="url" name="theme_cover" class="form-control" placeholder="https://..." value="{{ old('theme_cover', data_get($survey->theme, 'cover')) }}">
                        </div>
                    </div>
                    <div class="mt-3 p-3 border rounded" id="theme-preview" style="background: linear-gradient(135deg, #f7f9ff, #e8f3ff);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle" style="width:48px;height:48px;background: var(--preview-primary, #2563eb);"></div>
                            <div>
                                <div class="fw-semibold" id="preview-title">{{ old('title', $survey->title ?? 'Judul Survei') }}</div>
                                <small class="text-muted">Pratinjau tema</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if($isEdit && $survey->versions->count())
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <h6 class="mb-2">Versi Sebelumnya</h6>
                    <ul class="list-unstyled mb-0">
                        @foreach($survey->versions as $version)
                            <li class="d-flex justify-content-between align-items-center py-1">
                                <div>
                                    <strong>{{ $version->created_at->diffForHumans() }}</strong>
                                    <div class="text-muted small">{{ $version->note ?? 'Snapshot' }}</div>
                                </div>
                                <button type="submit" form="restore-version-{{ $version->id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Pulihkan ke versi ini?')">Undo</button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
            @if($isEdit)
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <h6 class="mb-2">Kolaborator</h6>
                    <div class="row g-2 mb-2">
                        <div class="col-7">
                            <input type="email" name="email" class="form-control form-control-sm" placeholder="Email user" required form="collaborator-add-form">
                        </div>
                        <div class="col-3">
                            <select name="role" class="form-select form-select-sm" form="collaborator-add-form">
                                <option value="editor">Editor</option>
                                <option value="viewer">Viewer</option>
                                <option value="owner">Owner</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <button type="submit" form="collaborator-add-form" class="btn btn-sm btn-outline-primary w-100">Tambah</button>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between align-items-center py-1">
                            <div>
                                <strong>{{ $survey->creator->email ?? 'Pembuat' }}</strong>
                                <div class="text-muted small">Owner</div>
                            </div>
                        </li>
                        @foreach($survey->collaborators as $collab)
                            <li class="d-flex justify-content-between align-items-center py-1">
                                <div>
                                    <strong>{{ $collab->user->email ?? 'Pengguna' }}</strong>
                                    <div class="text-muted small">{{ ucfirst($collab->role) }}</div>
                                </div>
                                <button type="submit" form="collaborator-remove-{{ $collab->id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kolaborator?')">Hapus</button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="mb-0">Section</h6>
                            <small class="text-muted">Bagi form menjadi beberapa halaman.</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-section">Tambah Section</button>
                    </div>
                    <div id="section-list" class="d-flex flex-column gap-2"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h5 class="mb-0">Pertanyaan</h5>
                            <small class="text-muted">Gunakan berbagai tipe: singkat, paragraf, pilihan, skala, tanggal.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">Templat</button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item template-btn" data-template="kepuasan" href="#">Kepuasan Layanan</a></li>
                                    <li><a class="dropdown-item template-btn" data-template="tracer" href="#">Tracer Alumni</a></li>
                                    <li><a class="dropdown-item template-btn" data-template="rsvp" href="#">RSVP/Registrasi</a></li>
                                </ul>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-question">
                                <i class="fas fa-plus me-1"></i> Pertanyaan
                            </button>
                        </div>
                    </div>
                    <div id="question-list" class="d-flex flex-column gap-3"></div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="sections_payload" id="sections-payload">
    <input type="hidden" name="questions_payload" id="questions-payload">
    <input type="hidden" name="skip_rules_payload" id="skip-rules-payload">
    <div class="text-end mt-4">
        <button class="btn btn-primary px-4">{{ $isEdit ? 'Simpan Perubahan' : 'Publikasikan Survei' }}</button>
    </div>
</form>

@if($isEdit)
    @if($survey->versions->count())
        @foreach($survey->versions as $version)
            <form id="restore-version-{{ $version->id }}" action="{{ route('admin.surveys.restore', [$survey, $version]) }}" method="POST" class="d-none">
                @csrf
            </form>
        @endforeach
    @endif
    <form id="collaborator-add-form" action="{{ route('admin.surveys.collaborators.add', $survey) }}" method="POST" class="d-none">
        @csrf
    </form>
    @foreach($survey->collaborators as $collab)
        <form id="collaborator-remove-{{ $collab->id }}" action="{{ route('admin.surveys.collaborators.remove', [$survey, $collab]) }}" method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif
@endsection

@push('styles')
<style>
    .question-card { border:1px solid #e5e7eb; border-radius:14px; padding:16px; background:#fff; box-shadow:0 4px 14px rgba(0,0,0,0.03); }
    .option-row { display:flex; gap:8px; align-items:center; margin-bottom:8px; }
    .option-row input { flex:1; }
    .badge-type { font-size:11px; text-transform:uppercase; letter-spacing:0.5px; }
    .survey-toolbar { background:linear-gradient(135deg,#eef2ff,#e0f2fe); border-radius:14px; padding:14px; }
    .question-card:hover { border-color:#cbd5e1; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const questionTypes = {
            short_text: 'Jawaban singkat',
            long_text: 'Paragraf',
            choice_single: 'Pilihan ganda',
            choice_multiple: 'Checkbox',
            dropdown: 'Dropdown',
            linear_scale: 'Skala (1-5)',
            date: 'Tanggal',
            time: 'Waktu',
            file_upload: 'Unggah file',
            grid_single: 'Grid (pilihan tunggal)',
            grid_multiple: 'Grid (checkbox)',
            rating: 'Rating',
            choice_single_other: 'Pilihan ganda + Lainnya',
        };

        const form = document.getElementById('survey-form');
        const questionList = document.getElementById('question-list');
        const payloadInput = document.getElementById('questions-payload');
        const sectionList = document.getElementById('section-list');
        const payloadSections = document.getElementById('sections-payload');
        const skipRulesPayload = document.getElementById('skip-rules-payload');
        const initialQuestions = @json($initialQuestions);
        const initialSections = @json($initialSections);
        const initialSkipRules = @json($initialSkipRules);
        let visibilityRules = {};
        const templates = {
            kepuasan: {
                sections: [{ key: 'section-1', title: 'Profil', description: '' }, { key: 'section-2', title: 'Penilaian', description: '' }],
                questions: [
                    { question: 'Nama lengkap Anda', type: 'short_text', section_key: 'section-1', is_required: true, placeholder: 'Tuliskan nama sesuai identitas' },
                    { question: 'Bagaimana Anda mengenal BPVP?', type: 'choice_single', section_key: 'section-1', options: [
                        { label: 'Media sosial' }, { label: 'Website' }, { label: 'Teman/keluarga' }, { label: 'Kampus/instansi' }
                    ], is_required: true },
                    { question: 'Layanan yang pernah Anda gunakan', type: 'choice_multiple', section_key: 'section-1', options: [
                        { label: 'Pelatihan vokasi' }, { label: 'Sertifikasi' }, { label: 'Konsultasi karier' }, { label: 'Forum alumni' }
                    ]},
                    { question: 'Seberapa puas dengan pengalaman Anda?', type: 'linear_scale', section_key: 'section-2', settings: { min:1, max:5, left_label:'Kurang', right_label:'Sangat puas' }, is_required: true },
                    { question: 'Saran perbaikan terpenting menurut Anda', type: 'long_text', section_key: 'section-2', placeholder: 'Tuliskan singkat' },
                ]
            },
            tracer: {
                sections: [{ key: 'section-1', title: 'Data Diri', description: '' }, { key: 'section-2', title: 'Karier', description: '' }],
                questions: [
                    { question: 'Nama Alumni', type: 'short_text', section_key: 'section-1', is_required: true },
                    { question: 'Tahun Kelulusan', type: 'short_text', section_key: 'section-1', placeholder: '2023' },
                    { question: 'Status saat ini', type: 'choice_single', section_key: 'section-2', is_required: true, options: [
                        { label: 'Bekerja' }, { label: 'Wirausaha' }, { label: 'Melanjutkan studi' }, { label: 'Mencari kerja' }
                    ]},
                    { question: 'Nama perusahaan/usaha', type: 'short_text', section_key: 'section-2', placeholder: 'Jika bekerja/berusaha' },
                    { question: 'Pendapatan per bulan', type: 'dropdown', section_key: 'section-2', options: [
                        { label: '< 3 juta' }, { label: '3-5 juta' }, { label: '5-8 juta' }, { label: '> 8 juta' }
                    ]},
                ]
            },
            rsvp: {
                sections: [{ key: 'section-1', title: 'Konfirmasi Kehadiran', description: '' }],
                questions: [
                    { question: 'Nama', type: 'short_text', section_key: 'section-1', is_required: true },
                    { question: 'Email', type: 'short_text', section_key: 'section-1', validation: { format: 'email' }, is_required: true },
                    { question: 'Nomor telepon', type: 'short_text', section_key: 'section-1', validation: { format: 'phone' }, is_required: true },
                    { question: 'Apakah akan hadir?', type: 'choice_single', section_key: 'section-1', is_required: true, options: [
                        { label: 'Ya' }, { label: 'Tidak' }
                    ]},
                    { question: 'Jumlah pendamping', type: 'dropdown', section_key: 'section-1', options: [
                        { label: '0' }, { label: '1' }, { label: '2' }, { label: '3+' }
                    ]},
                ]
            }
        };

        let sections = Array.isArray(initialSections) && initialSections.length ? initialSections : [{ id: null, key: 'section-1', title: 'Bagian 1', description: '', position: 0 }];
        let questions = Array.isArray(initialQuestions) && initialQuestions.length ? initialQuestions : [newQuestion()];
        let skipRules = Array.isArray(initialSkipRules) ? initialSkipRules : [];

        function newQuestion() {
            return {
                id: null,
                question: '',
                description: '',
                type: 'short_text',
                is_required: false,
                placeholder: '',
                settings: {},
                options: [],
                section_key: sections[0]?.key ?? 'section-1',
                validation: {},
                visibility_rules: [],
            };
        }

        function renderSections() {
            sectionList.innerHTML = '';
            sections.forEach((section, index) => {
                section.position = index;
                const row = document.createElement('div');
                row.className = 'border rounded-3 p-2';
                row.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <input type="text" class="form-control form-control-sm section-title" placeholder="Judul section" value="${section.title ?? ''}">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-section" ${sections.length === 1 ? 'disabled' : ''}><i class="fas fa-times"></i></button>
                    </div>
                    <textarea class="form-control form-control-sm mt-2 section-desc" rows="2" placeholder="Deskripsi section (opsional)">${section.description ?? ''}</textarea>
                `;
                row.querySelector('.section-title').addEventListener('input', e => section.title = e.target.value);
                row.querySelector('.section-desc').addEventListener('input', e => section.description = e.target.value);
                row.querySelector('.remove-section').addEventListener('click', () => {
                    if (sections.length === 1) return;
                    sections.splice(index, 1);
                    questions = questions.map(q => ({ ...q, section_key: sections[0]?.key }));
                    renderSections();
                    renderQuestions();
                });
                sectionList.appendChild(row);
            });
        }

        function renderQuestions() {
            questionList.innerHTML = '';
            questions.forEach((question, index) => {
                question.position = index;
                const card = document.createElement('div');
                card.className = 'question-card';

                card.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-light text-dark badge-type">#${index + 1}</span>
                            <select class="form-select form-select-sm w-auto question-type">
                                ${Object.entries(questionTypes).map(([key,label]) => `<option value="${key}" ${question.type === key ? 'selected' : ''}>${label}</option>`).join('')}
                            </select>
                            <select class="form-select form-select-sm w-auto question-section">
                                ${sections.map(sec => `<option value="${sec.key}" ${question.section_key === sec.key ? 'selected' : ''}>${sec.title ?? sec.key}</option>`).join('')}
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary move-up" title="Naik"><i class="fas fa-arrow-up"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-secondary move-down" title="Turun"><i class="fas fa-arrow-down"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-question" title="Hapus"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                    <div class="mb-2">
                        <input type="text" class="form-control question-text" placeholder="Tulis pertanyaan" value="${question.question ?? ''}" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-8">
                            <input type="text" class="form-control question-desc" placeholder="Deskripsi (opsional)" value="${question.description ?? ''}">
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control question-placeholder" placeholder="Placeholder" value="${question.placeholder ?? ''}">
                        </div>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input question-required" type="checkbox" ${question.is_required ? 'checked' : ''}>
                        <label class="form-check-label">Wajib diisi</label>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <input type="text" class="form-control form-control-sm validation-regex" placeholder="Regex/format khusus" value="${question.validation?.regex ?? ''}">
                        </div>
                        <div class="col-md-6">
                            <select class="form-select form-select-sm validation-format">
                                <option value="">Format opsional</option>
                                <option value="email" ${question.validation?.format === 'email' ? 'selected' : ''}>Email</option>
                                <option value="phone" ${question.validation?.format === 'phone' ? 'selected' : ''}>Nomor telepon</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary manage-visibility">Rule tampil/sembunyi</button>
                        <div class="small text-muted mt-1">Atur logika tampil berdasarkan jawaban lain.</div>
                    </div>
                    <div class="option-area"></div>
                `;

                attachQuestionEvents(card, question, index);
                questionList.appendChild(card);
            });
        }

        function attachQuestionEvents(card, question, index) {
            card.querySelector('.question-type').addEventListener('change', (e) => {
                question.type = e.target.value;
                if (!['choice_single','choice_multiple','dropdown','choice_single_other'].includes(question.type)) {
                    question.options = [];
                }
                renderQuestions();
            });

            card.querySelector('.question-text').addEventListener('input', (e) => question.question = e.target.value);
            card.querySelector('.question-desc').addEventListener('input', (e) => question.description = e.target.value);
            card.querySelector('.question-placeholder').addEventListener('input', (e) => question.placeholder = e.target.value);
            card.querySelector('.question-required').addEventListener('change', (e) => question.is_required = e.target.checked);
            const sectionSelect = card.querySelector('.question-section');
            if (sectionSelect) {
                sectionSelect.addEventListener('change', (e) => question.section_key = e.target.value);
            }
            const valRegex = card.querySelector('.validation-regex');
            const valFormat = card.querySelector('.validation-format');
            if (valRegex) {
                valRegex.addEventListener('input', (e) => {
                    question.validation = question.validation || {};
                    question.validation.regex = e.target.value;
                });
            }
            if (valFormat) {
                valFormat.addEventListener('change', (e) => {
                    question.validation = question.validation || {};
                    question.validation.format = e.target.value;
                });
            }
            const manageVisibility = card.querySelector('.manage-visibility');
            if (manageVisibility) {
                manageVisibility.addEventListener('click', () => {
                    const otherQuestions = questions.filter(q => q !== question);
                    const choices = otherQuestions.map(q => `<option value="${q.id || q.question}">${q.question || 'Pertanyaan'}</option>`).join('');
                    const currentRules = question.visibility_rules || [];
                    const modal = document.createElement('div');
                    modal.className = 'p-3 border rounded bg-light';
                    modal.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Rule tampil/sembunyi</strong>
                            <button type="button" class="btn btn-sm btn-outline-danger close-vis">Tutup</button>
                        </div>
                        <div class="vis-rules"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary add-vis mt-2">Tambah Rule</button>
                    `;
                    const visContainer = modal.querySelector('.vis-rules');
                    function renderVis() {
                        visContainer.innerHTML = '';
                        (question.visibility_rules || []).forEach((r, idx) => {
                            const row = document.createElement('div');
                            row.className = 'border rounded p-2 mb-2 bg-white';
                            row.innerHTML = `
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">Pertanyaan acuan</label>
                                        <select class="form-select form-select-sm vis-question">
                                            ${choices}
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Aksi</label>
                                        <select class="form-select form-select-sm vis-action">
                                            <option value="show" ${r.action === 'show' ? 'selected' : ''}>Tampilkan jika cocok</option>
                                            <option value="hide" ${r.action === 'hide' ? 'selected' : ''}>Sembunyikan jika cocok</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1">Nilai</label>
                                        <input type="text" class="form-control form-control-sm vis-equals" value="${r.equals ?? ''}" placeholder="Cocok dengan teks/option">
                                    </div>
                                    <div class="col-md-1 text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-vis">&times;</button>
                                    </div>
                                </div>
                            `;
                            row.querySelector('.vis-question').value = r.question_id || '';
                            row.querySelector('.vis-question').addEventListener('change', e => {
                                question.visibility_rules[idx].question_id = e.target.value;
                            });
                            row.querySelector('.vis-action').addEventListener('change', e => {
                                question.visibility_rules[idx].action = e.target.value;
                            });
                            row.querySelector('.vis-equals').addEventListener('input', e => {
                                question.visibility_rules[idx].equals = e.target.value;
                            });
                            row.querySelector('.remove-vis').addEventListener('click', () => {
                                question.visibility_rules.splice(idx,1);
                                renderVis();
                            });
                            visContainer.appendChild(row);
                        });
                    }
                    renderVis();
                    modal.querySelector('.add-vis').addEventListener('click', () => {
                        question.visibility_rules = question.visibility_rules || [];
                        question.visibility_rules.push({ question_id: otherQuestions[0]?.id || otherQuestions[0]?.question || '', action:'show', equals:'' });
                        renderVis();
                    });
                    modal.querySelector('.close-vis').addEventListener('click', () => modal.remove());
                    card.appendChild(modal);
                });
            }

            card.querySelector('.move-up').addEventListener('click', () => {
                if (index === 0) return;
                [questions[index - 1], questions[index]] = [questions[index], questions[index - 1]];
                renderQuestions();
            });
            card.querySelector('.move-down').addEventListener('click', () => {
                if (index === questions.length - 1) return;
                [questions[index + 1], questions[index]] = [questions[index], questions[index + 1]];
                renderQuestions();
            });
            card.querySelector('.remove-question').addEventListener('click', () => {
                questions.splice(index, 1);
                if (!questions.length) questions.push(newQuestion());
                renderQuestions();
            });

            const optionArea = card.querySelector('.option-area');
            renderOptionArea(optionArea, question);
        }

        function renderOptionArea(container, question) {
            const isChoice = ['choice_single','choice_multiple','dropdown','choice_single_other'].includes(question.type);
            const isScale = question.type === 'linear_scale';
            const isRating = question.type === 'rating';
            const isFile = question.type === 'file_upload';
            const isGrid = ['grid_single','grid_multiple'].includes(question.type);

            if (isChoice) {
                container.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="small text-muted">Opsi jawaban</div>
                        <button type="button" class="btn btn-sm btn-outline-primary add-option"><i class="fas fa-plus me-1"></i> Opsi</button>
                    </div>
                    <div class="option-list"></div>
                `;

                const optionList = container.querySelector('.option-list');
                const renderOptions = () => {
                    optionList.innerHTML = '';
                    (question.options || []).forEach((opt, idx) => {
                        const row = document.createElement('div');
                        row.className = 'option-row';
                        row.innerHTML = `
                            <input type="text" class="form-control option-label" placeholder="Opsi ${idx + 1}" value="${opt.label ?? ''}">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-option"><i class="fas fa-times"></i></button>
                        `;
                        row.querySelector('.option-label').addEventListener('input', (e) => {
                            opt.label = e.target.value;
                        });
                        row.querySelector('.remove-option').addEventListener('click', () => {
                            question.options.splice(idx, 1);
                            renderOptions();
                        });
                        optionList.appendChild(row);
                    });
                };

                container.querySelector('.add-option').addEventListener('click', () => {
                    question.options = question.options || [];
                    question.options.push({ label: '', value: '', is_other: false, position: question.options.length });
                    renderOptions();
                });

                renderOptions();
            } else if (isScale) {
                const min = question.settings?.min ?? 1;
                const max = question.settings?.max ?? 5;
                container.innerHTML = `
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small mb-1">Skala Minimum</label>
                            <input type="number" class="form-control scale-min" value="${min}" min="1" max="10">
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">Skala Maksimum</label>
                            <input type="number" class="form-control scale-max" value="${max}" min="2" max="10">
                        </div>
                        <div class="col-6">
                            <input type="text" class="form-control scale-left" placeholder="Label kiri (opsional)" value="${question.settings?.left_label ?? ''}">
                        </div>
                        <div class="col-6">
                            <input type="text" class="form-control scale-right" placeholder="Label kanan (opsional)" value="${question.settings?.right_label ?? ''}">
                        </div>
                    </div>
                `;
                container.querySelector('.scale-min').addEventListener('input', (e) => {
                    question.settings = question.settings || {};
                    question.settings.min = parseInt(e.target.value || 1, 10);
                });
                container.querySelector('.scale-max').addEventListener('input', (e) => {
                    question.settings = question.settings || {};
                    question.settings.max = parseInt(e.target.value || 5, 10);
                });
                container.querySelector('.scale-left').addEventListener('input', (e) => {
                    question.settings = question.settings || {};
                    question.settings.left_label = e.target.value;
                });
                container.querySelector('.scale-right').addEventListener('input', (e) => {
                    question.settings = question.settings || {};
                    question.settings.right_label = e.target.value;
                });
            } else if (isRating) {
                question.settings = question.settings || {};
                question.settings.min = question.settings.min ?? 1;
                question.settings.max = question.settings.max ?? 5;
                container.innerHTML = `<small class="text-muted">Rating menggunakan slider (set min/max di skala).</small>`;
            } else if (isFile) {
                container.innerHTML = `
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small mb-1">Max Size (MB)</label>
                            <input type="number" class="form-control file-max" min="1" value="${question.settings?.max_size ?? 5}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">Tipe file (mime, koma)</label>
                            <input type="text" class="form-control file-mime" value="${question.settings?.mime ?? 'jpeg,png,pdf'}">
                        </div>
                    </div>
                `;
                container.querySelector('.file-max').addEventListener('input', e => {
                    question.settings = question.settings || {};
                    question.settings.max_size = parseInt(e.target.value || 5, 10);
                });
                container.querySelector('.file-mime').addEventListener('input', e => {
                    question.settings = question.settings || {};
                    question.settings.mime = e.target.value;
                });
            } else if (isGrid) {
                container.innerHTML = `
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small">Baris (pisahkan dengan baris baru)</label>
                            <textarea class="form-control grid-rows" rows="3">${(question.settings?.rows || []).join('\\n')}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Kolom (pisahkan dengan baris baru)</label>
                            <textarea class="form-control grid-cols" rows="3">${(question.settings?.columns || []).join('\\n')}</textarea>
                        </div>
                    </div>
                `;
                container.querySelector('.grid-rows').addEventListener('input', e => {
                    question.settings = question.settings || {};
                    question.settings.rows = e.target.value.split('\\n').filter(Boolean);
                });
                container.querySelector('.grid-cols').addEventListener('input', e => {
                    question.settings = question.settings || {};
                    question.settings.columns = e.target.value.split('\\n').filter(Boolean);
                });
            } else {
                container.innerHTML = `<small class="text-muted">Tidak perlu opsi untuk tipe ini.</small>`;
            }
        }

        document.getElementById('add-question').addEventListener('click', () => {
            questions.push(newQuestion());
            renderQuestions();
            // Scroll to the newly added question to keep UX aligned with the button intent
            requestAnimationFrame(() => {
                const cards = document.querySelectorAll('#question-list .question-card');
                const newest = cards[cards.length - 1];
                newest?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                newest?.querySelector('.question-text')?.focus();
            });
        });

        document.querySelectorAll('.template-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const key = btn.dataset.template;
                if (!templates[key]) return;
                const tmpl = templates[key];
                sections = tmpl.sections.map((s, idx) => ({ id: null, ...s, position: idx }));
                questions = tmpl.questions.map((q, idx) => ({
                    id: null,
                    question: q.question,
                    description: q.description ?? '',
                    type: q.type,
                    is_required: q.is_required ?? false,
                    placeholder: q.placeholder ?? '',
                    settings: q.settings ?? {},
                    options: (q.options ?? []).map((o, oidx) => ({ ...o, position: oidx })),
                    section_key: q.section_key ?? sections[0]?.key,
                    validation: q.validation ?? {},
                    position: idx,
                }));
                renderSections();
                renderQuestions();
            });
        });

        document.getElementById('add-section').addEventListener('click', () => {
            const key = 'section-' + (sections.length + 1);
            sections.push({ id: null, key, title: 'Bagian ' + (sections.length + 1), description: '', position: sections.length });
            renderSections();
            renderQuestions();
        });

        function syncPayload() {
            const payload = questions.map((q, idx) => {
                q.position = idx;
                if (q.options && q.options.length) {
                    q.options = q.options
                        .filter(opt => (opt.label || '').trim() !== '')
                        .map((opt, optIdx) => ({ ...opt, position: optIdx }));
                }
                return q;
            });
            payloadInput.value = JSON.stringify(payload);
            payloadSections.value = JSON.stringify(sections);
            skipRulesPayload.value = JSON.stringify(skipRules);
        }

        form.addEventListener('submit', function () {
            syncPayload();
        });

        const themePrimary = document.querySelector('input[name="theme_primary"]');
        const themePreview = document.getElementById('theme-preview');
        function updateThemePreview() {
            if (themePreview && themePrimary) {
                themePreview.style.setProperty('--preview-primary', themePrimary.value || '#2563eb');
            }
        }
        themePrimary?.addEventListener('input', updateThemePreview);
        updateThemePreview();

        renderSections();
        renderQuestions();
    });
</script>
@endpush
