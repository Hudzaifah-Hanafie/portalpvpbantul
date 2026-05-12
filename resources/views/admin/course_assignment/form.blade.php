@extends('layouts.admin')

@php
    $statusOptions = \App\Models\CourseAssignment::statuses();
    $assessmentOptions = $assessmentOptions ?? [
        'regular' => 'Tugas/Quiz Reguler',
        'module_quiz' => 'Quiz Akhir Bab',
        'final_exam' => 'Ujian Final',
        'final_project' => 'Proyek Akhir',
    ];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $assignment->exists ? 'Ubah' : 'Tambah' }} Tugas</h4>
        <small class="text-muted">Setel detail tugas/quiz, due date, dan status publikasi.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-assignment.index' : 'admin.course-assignment.index')) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<form action="{{ $action }}" method="POST" class="bg-white rounded shadow-sm p-4" novalidate>
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Kelas</label>
            <select name="course_class_id" class="form-select @error('course_class_id') is-invalid @enderror" required>
                <option value="">Pilih Kelas</option>
                @foreach($classes as $id => $title)
                    <option value="{{ $id }}" @selected(old('course_class_id', $assignment->course_class_id) === $id)>{{ $title }}</option>
                @endforeach
            </select>
            @error('course_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Judul</label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $assignment->title) }}" required maxlength="255">
            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mb-3 mt-3">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $assignment->description) }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Kategori Penilaian</label>
            <select name="assessment_type" class="form-select @error('assessment_type') is-invalid @enderror" id="assessmentTypeSelect">
                @foreach($assessmentOptions as $key => $label)
                    <option value="{{ $key }}" @selected(old('assessment_type', $assignment->assessment_type ?? 'regular') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('assessment_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4" id="moduleSelectWrap" style="display: none;">
            <label class="form-label">Bab (Modul)</label>
            <select name="course_module_id" class="form-select @error('course_module_id') is-invalid @enderror">
                <option value="">Pilih bab</option>
                @foreach($modules as $id => $title)
                    <option value="{{ $id }}" @selected(old('course_module_id', $assignment->course_module_id) === $id)>{{ $title }}</option>
                @endforeach
            </select>
            @error('course_module_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Tipe</label>
            <select name="type" class="form-select @error('type') is-invalid @enderror">
                <option value="essay" @selected(old('type', $assignment->type) === 'essay')>Essay</option>
                <option value="file" @selected(old('type', $assignment->type) === 'file')>File</option>
                <option value="quiz" @selected(old('type', $assignment->type) === 'quiz')>Quiz</option>
            </select>
            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4" id="quizScopeWrap" style="display: none;">
            <label class="form-label">Jenis CBT</label>
            <select name="quiz_scope" class="form-select @error('quiz_scope') is-invalid @enderror">
                <option value="class" @selected(old('quiz_scope', $assignment->quiz_scope ?? 'class') === 'class')>CBT Kelas</option>
                <option value="selection" @selected(old('quiz_scope', $assignment->quiz_scope) === 'selection')>CBT Seleksi</option>
            </select>
            <small class="text-muted">CBT seleksi akan mengisi nilai pendaftaran.</small>
            @error('quiz_scope') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Due Date</label>
            <input type="datetime-local" name="due_at" class="form-control @error('due_at') is-invalid @enderror" value="{{ old('due_at', optional($assignment->due_at)->format('Y-m-d\TH:i')) }}">
            @error('due_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror">
                @foreach($statusOptions as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $assignment->status ?? 'draft') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <small class="text-muted">Draft/Pending untuk menunggu review; Reviewer/Admin dapat langsung publikasi.</small>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-4">
            <label class="form-label">Bobot (%)</label>
            <input type="number" name="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ old('weight', $assignment->weight ?? 0) }}" min="0" max="100">
            @error('weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Skor Maks</label>
            <input type="number" name="max_score" class="form-control @error('max_score') is-invalid @enderror" value="{{ old('max_score', $assignment->max_score ?? 100) }}" min="1" max="1000">
            @error('max_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Token Ujian (opsional)</label>
            <input type="text" name="exam_token" class="form-control @error('exam_token') is-invalid @enderror" value="{{ old('exam_token', $assignment->exam_token) }}" placeholder="Isi jika butuh token harian">
            @error('exam_token') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="require_token" value="1" id="requireToken" @checked(old('require_token', $assignment->require_token))>
                <label class="form-check-label" for="requireToken">Wajibkan token untuk mulai ujian</label>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-4">
            <label class="form-label">Late Policy</label>
            <select name="late_policy" class="form-select @error('late_policy') is-invalid @enderror">
                <option value="no-accept" @selected(old('late_policy', $assignment->late_policy) === 'no-accept')>Tidak terima setelah due</option>
                <option value="penalty" @selected(old('late_policy', $assignment->late_policy) === 'penalty')>Penalti</option>
                <option value="allow" @selected(old('late_policy', $assignment->late_policy) === 'allow')>Izinkan tanpa penalti</option>
            </select>
            @error('late_policy') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Penalti (%)</label>
            <input type="number" name="penalty_percent" class="form-control @error('penalty_percent') is-invalid @enderror" value="{{ old('penalty_percent', $assignment->penalty_percent) }}" min="0" max="100">
            @error('penalty_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <small class="text-muted">Hanya dipakai jika kebijakan penalti.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label">Ujian dibuka</label>
            <input type="datetime-local" name="exam_start_at" class="form-control @error('exam_start_at') is-invalid @enderror" value="{{ old('exam_start_at', optional($assignment->exam_start_at)->format('Y-m-d\\TH:i')) }}">
            @error('exam_start_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-4">
            <label class="form-label">Ujian ditutup</label>
            <input type="datetime-local" name="exam_end_at" class="form-control @error('exam_end_at') is-invalid @enderror" value="{{ old('exam_end_at', optional($assignment->exam_end_at)->format('Y-m-d\\TH:i')) }}">
            @error('exam_end_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4 d-flex align-items-center">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" name="auto_submit" value="1" id="autoSubmit" @checked(old('auto_submit', $assignment->auto_submit))>
                <label class="form-check-label" for="autoSubmit">Auto-submit saat waktu habis</label>
            </div>
        </div>
        <div class="col-md-4 d-flex align-items-center">
            <div class="form-check mt-4">
                <input type="checkbox" class="form-check-input" name="is_active" value="1" {{ old('is_active', $assignment->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label">Aktif</label>
            </div>
        </div>
    </div>

    <div id="quizPanel" class="mt-4 border rounded p-3 bg-light" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-0">Pengaturan Quiz</h5>
                <small class="text-muted">Tambah pertanyaan dan jawaban tanpa perlu JSON.</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addQuestionBtn">
                <i class="fas fa-plus"></i> Pertanyaan
            </button>
        </div>

        <div id="quizQuestions" class="d-flex flex-column gap-3"></div>
        <input type="hidden" name="quiz_schema" id="quizSchemaInput" value="{{ old('quiz_schema', $assignment->quiz_schema ? json_encode($assignment->quiz_schema) : '') }}">
        @error('quiz_schema') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

        <hr class="my-3">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label mb-1">Batas waktu (menit) <small class="text-muted">(opsional)</small></label>
                <input type="number" min="1" name="quiz_settings_time_limit" id="quizTimeLimit" class="form-control" placeholder="contoh: 30" value="{{ old('quiz_settings', $assignment->quiz_settings ? ($assignment->quiz_settings['time_limit_minutes'] ?? '') : '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label mb-1">Maks percobaan <small class="text-muted">(opsional)</small></label>
                <input type="number" min="1" name="quiz_settings_max_attempts" id="quizMaxAttempts" class="form-control" placeholder="contoh: 1" value="{{ old('quiz_settings', $assignment->quiz_settings ? ($assignment->quiz_settings['max_attempts'] ?? '') : '') }}">
            </div>
        </div>
        <input type="hidden" name="quiz_settings" id="quizSettingsInput" value="{{ old('quiz_settings', $assignment->quiz_settings ? json_encode($assignment->quiz_settings) : '') }}">
        @error('quiz_settings') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
    </div>

    <div class="mt-4 border rounded p-3 bg-light">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h5 class="mb-1">Rubrik Penilaian</h5>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addRubricBtn">
                <i class="fas fa-plus"></i> Kriteria
            </button>
        </div>
        <div id="rubricList" class="d-flex flex-column gap-2"></div>
        <input type="hidden" name="rubric" id="rubricInput" value="{{ old('rubric', $assignment->rubric ? json_encode($assignment->rubric) : '') }}">
        @error('rubric') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        <div class="form-text mt-1">Weight & Max Score opsional; jika kosong akan dinilai dengan skor total tugas.</div>
    </div>

    <div class="text-end mt-4">
        <button class="btn btn-primary px-4">{{ $assignment->exists ? 'Perbarui' : 'Simpan' }}</button>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const typeSelect = document.querySelector('select[name="type"]');
    const assessmentTypeSelect = document.getElementById('assessmentTypeSelect');
    const moduleSelectWrap = document.getElementById('moduleSelectWrap');
    const quizPanel = document.getElementById('quizPanel');
    const quizScopeWrap = document.getElementById('quizScopeWrap');
    const quizQuestionsEl = document.getElementById('quizQuestions');
    const addQuestionBtn = document.getElementById('addQuestionBtn');
    const quizSchemaInput = document.getElementById('quizSchemaInput');
    const quizSettingsInput = document.getElementById('quizSettingsInput');
    const quizTimeLimit = document.getElementById('quizTimeLimit');
    const quizMaxAttempts = document.getElementById('quizMaxAttempts');
    const rubricInput = document.getElementById('rubricInput');
    const rubricList = document.getElementById('rubricList');
    const addRubricBtn = document.getElementById('addRubricBtn');

    if (!typeSelect || !assessmentTypeSelect) return;

    let questions = [];
    let rubrics = [];

    function renderQuestions() {
        quizQuestionsEl.innerHTML = '';
        questions.forEach((q, qIndex) => {
            const card = document.createElement('div');
            card.className = 'card border-0 shadow-sm';
            const body = document.createElement('div');
            body.className = 'card-body';
            body.innerHTML = `
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="fw-semibold">Pertanyaan ${qIndex + 1}</div>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-action="remove-question" data-index="${qIndex}"><i class="fas fa-trash"></i></button>
                </div>
                <div class="mb-3">
                    <label class="form-label">Teks Pertanyaan</label>
                    <input type="text" class="form-control" data-field="question-text" data-index="${qIndex}" value="${q.text ?? ''}">
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Skor Soal</label>
                        <input type="number" min="0" class="form-control" data-field="question-score" data-index="${qIndex}" value="${q.score ?? 0}">
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">Opsi Jawaban</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-action="add-option" data-index="${qIndex}"><i class="fas fa-plus"></i> Opsi</button>
                    </div>
                    <div class="d-flex flex-column gap-2" data-options="${qIndex}">
                        ${renderOptions(q.options || [], qIndex)}
                    </div>
                </div>
            `;
            card.appendChild(body);
            quizQuestionsEl.appendChild(card);
        });

        syncHidden();
    }

    function renderOptions(options, qIndex) {
        return options.map((opt, oIndex) => `
            <div class="input-group">
                <span class="input-group-text">
                    <input type="radio" name="correct-${qIndex}" ${opt.is_correct ? 'checked' : ''} data-action="mark-correct" data-q="${qIndex}" data-o="${oIndex}">
                </span>
                <input type="text" class="form-control" placeholder="Teks opsi" data-field="option-text" data-q="${qIndex}" data-o="${oIndex}" value="${opt.text ?? ''}">
                <input type="number" class="form-control" placeholder="Skor" min="0" data-field="option-score" data-q="${qIndex}" data-o="${oIndex}" value="${opt.score ?? ''}">
                <button class="btn btn-outline-danger" type="button" data-action="remove-option" data-q="${qIndex}" data-o="${oIndex}"><i class="fas fa-trash"></i></button>
            </div>
        `).join('') || `<div class="text-muted small">Belum ada opsi, tambahkan minimal satu.</div>`;
    }

    function addQuestion() {
        const newIndex = questions.length + 1;
        questions.push({
            id: 'q' + newIndex,
            text: '',
            type: 'single_choice',
            score: 0,
            options: [
                { id: 'o1', text: '', is_correct: true, score: 0 },
                { id: 'o2', text: '', is_correct: false, score: 0 },
            ],
        });
        renderQuestions();
    }

    function addOption(qIndex) {
        const opts = questions[qIndex].options || [];
        const newId = 'o' + (opts.length + 1);
        opts.push({ id: newId, text: '', is_correct: opts.length === 0, score: 0 });
        questions[qIndex].options = opts;
        renderQuestions();
    }

    function removeQuestion(qIndex) {
        questions.splice(qIndex, 1);
        renderQuestions();
    }

    function removeOption(qIndex, oIndex) {
        questions[qIndex].options.splice(oIndex, 1);
        if (!questions[qIndex].options.some(o => o.is_correct) && questions[qIndex].options[0]) {
            questions[qIndex].options[0].is_correct = true;
        }
        renderQuestions();
    }

    function markCorrect(qIndex, oIndex) {
        questions[qIndex].options.forEach((o, idx) => o.is_correct = idx === oIndex);
        syncHidden();
    }

    function syncHidden() {
        quizSchemaInput.value = JSON.stringify(questions);
        const settings = {};
        if (quizTimeLimit.value) settings.time_limit_minutes = parseInt(quizTimeLimit.value, 10);
        if (quizMaxAttempts.value) settings.max_attempts = parseInt(quizMaxAttempts.value, 10);
        quizSettingsInput.value = Object.keys(settings).length ? JSON.stringify(settings) : '';
        rubricInput.value = JSON.stringify(rubrics);
    }

    function loadInitial() {
        try {
            questions = quizSchemaInput.value ? JSON.parse(quizSchemaInput.value) : [];
        } catch (e) {
            questions = [];
        }
        try {
            rubrics = rubricInput.value ? JSON.parse(rubricInput.value) : [];
        } catch (e) {
            rubrics = [];
        }
        if (!questions.length && typeSelect.value === 'quiz') {
            addQuestion();
        } else {
            renderQuestions();
        }
        renderRubrics();
    }

    quizQuestionsEl.addEventListener('input', function (e) {
        const target = e.target;
        if (target.dataset.field === 'question-text') {
            questions[target.dataset.index].text = target.value;
        }
        if (target.dataset.field === 'question-score') {
            questions[target.dataset.index].score = parseFloat(target.value || 0);
        }
        if (target.dataset.field === 'option-text') {
            questions[target.dataset.q].options[target.dataset.o].text = target.value;
        }
        if (target.dataset.field === 'option-score') {
            questions[target.dataset.q].options[target.dataset.o].score = parseFloat(target.value || 0);
        }
        syncHidden();
    });

    quizQuestionsEl.addEventListener('click', function (e) {
        const action = e.target.closest('[data-action]')?.dataset.action;
        if (!action) return;
        const q = parseInt(e.target.closest('[data-action]').dataset.q ?? e.target.closest('[data-action]').dataset.index, 10);
        const o = parseInt(e.target.closest('[data-action]').dataset.o ?? -1, 10);

        if (action === 'add-option') addOption(q);
        if (action === 'remove-option') removeOption(q, o);
        if (action === 'remove-question') removeQuestion(q);
        if (action === 'mark-correct') markCorrect(q, o);
    });

    [quizTimeLimit, quizMaxAttempts].forEach(el => el?.addEventListener('input', syncHidden));

    addQuestionBtn?.addEventListener('click', function () {
        addQuestion();
    });

    function renderRubrics() {
        rubricList.innerHTML = '';
        if (!rubrics.length) {
            rubricList.innerHTML = '<div class="text-muted small">Belum ada kriteria. Tambahkan minimal satu jika diperlukan.</div>';
            syncHidden();
            return;
        }
        rubrics.forEach((r, idx) => {
            const row = document.createElement('div');
            row.className = 'card border-0 shadow-sm';
            row.innerHTML = `
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="fw-semibold">Kriteria ${idx + 1}</div>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-action="remove-rubric" data-index="${idx}"><i class="fas fa-trash"></i></button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label mb-1">Nama Kriteria</label>
                            <input type="text" class="form-control" data-field="rubric-criterion" data-index="${idx}" value="${r.criterion ?? ''}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Bobot (%)</label>
                            <input type="number" min="0" max="100" class="form-control" data-field="rubric-weight" data-index="${idx}" value="${r.weight ?? ''}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Skor Maks</label>
                            <input type="number" min="0" class="form-control" data-field="rubric-max" data-index="${idx}" value="${r.max_score ?? ''}">
                        </div>
                        <div class="col-md-12 mt-2">
                            <label class="form-label mb-1">Deskripsi</label>
                            <input type="text" class="form-control" data-field="rubric-desc" data-index="${idx}" value="${r.description ?? ''}">
                        </div>
                    </div>
                </div>
            `;
            rubricList.appendChild(row);
        });
        syncHidden();
    }

    function addRubric() {
        rubrics.push({
            criterion: '',
            weight: '',
            max_score: '',
            description: '',
        });
        renderRubrics();
    }

    rubricList?.addEventListener('input', function (e) {
        const field = e.target.dataset.field;
        const idx = parseInt(e.target.dataset.index ?? -1, 10);
        if (isNaN(idx) || idx < 0) return;
        if (field === 'rubric-criterion') rubrics[idx].criterion = e.target.value;
        if (field === 'rubric-weight') rubrics[idx].weight = e.target.value ? parseFloat(e.target.value) : '';
        if (field === 'rubric-max') rubrics[idx].max_score = e.target.value ? parseFloat(e.target.value) : '';
        if (field === 'rubric-desc') rubrics[idx].description = e.target.value;
        syncHidden();
    });

    rubricList?.addEventListener('click', function (e) {
        const action = e.target.closest('[data-action]')?.dataset.action;
        if (action === 'remove-rubric') {
            const idx = parseInt(e.target.closest('[data-action]').dataset.index, 10);
            rubrics.splice(idx, 1);
            renderRubrics();
        }
    });

    addRubricBtn?.addEventListener('click', function () {
        addRubric();
    });

    typeSelect.addEventListener('change', function () {
        const isQuiz = this.value === 'quiz';
        quizPanel.style.display = isQuiz ? 'block' : 'none';
        if (quizScopeWrap) quizScopeWrap.style.display = isQuiz ? 'block' : 'none';
        if (isQuiz && !questions.length) {
            addQuestion();
        }
        if (!isQuiz) {
            quizSchemaInput.value = '';
            quizSettingsInput.value = '';
        } else {
            syncHidden();
        }
    });

    assessmentTypeSelect.addEventListener('change', function () {
        const assessment = this.value;
        const isModuleQuiz = assessment === 'module_quiz';
        const isFinalExam = assessment === 'final_exam';
        const isFinalProject = assessment === 'final_project';

        if (moduleSelectWrap) moduleSelectWrap.style.display = isModuleQuiz ? 'block' : 'none';

        if (isModuleQuiz || isFinalExam) {
            typeSelect.value = 'quiz';
            typeSelect.dispatchEvent(new Event('change'));
        } else if (isFinalProject) {
            typeSelect.value = 'file';
            typeSelect.dispatchEvent(new Event('change'));
        }
    });

    // initial state
    quizPanel.style.display = typeSelect.value === 'quiz' ? 'block' : 'none';
    if (quizScopeWrap) quizScopeWrap.style.display = typeSelect.value === 'quiz' ? 'block' : 'none';
    if (moduleSelectWrap) {
        moduleSelectWrap.style.display = assessmentTypeSelect.value === 'module_quiz' ? 'block' : 'none';
    }
    loadInitial();

    // ensure sync on submit
    const formEl = document.querySelector('form');
    formEl?.addEventListener('submit', function (e) {
        syncHidden();
        if (rubrics.length && rubrics.some(r => !r.criterion)) {
            e.preventDefault();
            alert('Lengkapi nama kriteria pada rubrik.');
            return;
        }
        if (typeSelect.value !== 'quiz') return;
        if (!questions.length) {
            e.preventDefault();
            alert('Tambahkan minimal satu pertanyaan quiz.');
            return;
        }
        const invalid = questions.some(q => !q.text || !(q.options || []).some(o => o.is_correct && o.text));
        if (invalid) {
            e.preventDefault();
            alert('Lengkapi teks pertanyaan dan tandai minimal satu jawaban benar.');
            return;
        }
    });
})();
</script>
@endpush
@endsection
