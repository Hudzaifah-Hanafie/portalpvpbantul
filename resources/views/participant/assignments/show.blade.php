@extends('layouts.participant')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 fade-up">
    <div>
        <h4 class="mb-1 section-title">{{ $assignment->title }}</h4>
        <div class="section-subtitle">
            {{ $assignment->course->title ?? '-' }} • {{ strtoupper($assignment->type) }}
            @if($assignment->assessment_type === 'module_quiz')
                • Quiz Akhir Bab
            @elseif($assignment->assessment_type === 'final_exam')
                • Ujian Final
            @elseif($assignment->assessment_type === 'final_project')
                • Proyek Akhir
            @endif
        </div>
    </div>
    <a href="{{ route('participant.assignments') }}" class="btn btn-outline-light border-0 shadow-sm bg-white text-dark btn-sm">Kembali</a>
</div>

<div class="card stat-card card-hover mb-3">
    <div class="card-body">
        <div class="mb-2">
            <strong>Due:</strong> {{ $assignment->due_at ? $assignment->due_at->format('d M Y H:i') : '-' }}
            <span class="ms-2"><strong>Bobot:</strong> {{ $assignment->weight ?? 0 }}%</span>
            <span class="ms-2"><strong>Skor Maks:</strong> {{ $assignment->max_score ?? 100 }}</span>
        </div>
        <p class="mb-0">{!! nl2br(e($assignment->description)) !!}</p>
    </div>
</div>

<div class="card stat-card card-hover">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-modern">{{ session('success') }}</div>
        @endif
        @if($assignment->type === 'quiz')
            <h5 class="mb-3">Kerjakan Quiz</h5>
            <div class="d-flex flex-wrap gap-3 mb-3">
                @if($quizMaxAttempts)
                    <span class="badge text-bg-secondary">Sisa percobaan: {{ max(0, $quizMaxAttempts - ($quizAttemptsDone ?? 0)) }} / {{ $quizMaxAttempts }}</span>
                @endif
                @if($quizExpiresAt)
                    <span class="badge text-bg-warning text-dark" id="quizTimer" data-expire="{{ $quizExpiresAt }}" data-auto-submit="{{ $assignment->auto_submit ? 1 : 0 }}">
                        Waktu tersisa: <span id="quizTimerValue">-</span>
                    </span>
                @elseif(($assignment->quiz_settings['time_limit_minutes'] ?? null))
                    <span class="badge text-bg-warning text-dark">Batas waktu: {{ $assignment->quiz_settings['time_limit_minutes'] }} menit</span>
                @endif
            </div>
            @if($quizMaxAttempts && ($quizAttemptsLeft ?? 0) <= 0)
                <div class="alert alert-secondary alert-modern">Batas percobaan telah habis.</div>
            @elseif($submission)
                <div class="alert alert-secondary alert-modern">Anda sudah mengerjakan quiz ini.</div>
            @else
                <form action="{{ route('participant.assignments.submit', $assignment) }}" method="POST" class="row g-4" id="quizForm">
                    @csrf
                    @if($assignment->require_token)
                        <div class="col-12">
                            <label class="form-label">Token Ujian</label>
                            <input type="text" name="exam_token" class="form-control @error('exam_token') is-invalid @enderror" placeholder="Masukkan token dari panitia" required>
                            @error('exam_token') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @endif
                    @if($assignment->exam_start_at || $assignment->exam_end_at)
                        <div class="col-12">
                            <div class="alert alert-info alert-modern py-2 px-3 small mb-0">
                                Waktu ujian: {{ $assignment->exam_start_at?->format('d M Y H:i') ?? '-' }} s/d {{ $assignment->exam_end_at?->format('d M Y H:i') ?? '-' }}
                            </div>
                        </div>
                    @endif
                    @foreach($quizQuestions ?? [] as $question)
                        <div class="col-12">
                            <div class="fw-semibold">{{ $loop->iteration }}. {{ $question['text'] ?? '' }}</div>
                            @foreach(($question['options'] ?? []) as $option)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answers[{{ $question['id'] }}]" id="q{{ $loop->parent->iteration }}o{{ $loop->iteration }}" value="{{ $option['id'] }}" required>
                                    <label class="form-check-label" for="q{{ $loop->parent->iteration }}o{{ $loop->iteration }}">{{ $option['text'] ?? '' }}</label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                    <div class="text-end">
                        <button class="btn btn-primary lms-cta">Kirim Jawaban</button>
                    </div>
                </form>
            @endif
        @else
            <h5 class="mb-3">Kumpulkan Tugas</h5>
            <form action="{{ route('participant.assignments.submit', $assignment) }}" method="POST" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-12">
                    <label class="form-label">Jawaban Teks (opsional)</label>
                    <textarea name="content_text" rows="4" class="form-control @error('content_text') is-invalid @enderror">{{ old('content_text') }}</textarea>
                    @error('content_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tautan (opsional)</label>
                    <input type="url" name="link_url" class="form-control @error('link_url') is-invalid @enderror" value="{{ old('link_url') }}" placeholder="https://...">
                    @error('link_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Unggah File (opsional)</label>
                    <input type="file" name="file_upload" class="form-control @error('file_upload') is-invalid @enderror" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.rar,.jpg,.jpeg,.png">
                    <small class="text-muted">Maks 10MB; PDF/DOC/PPT/ZIP/JPG/PNG.</small>
                    @error('file_upload') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="text-end">
                    <button class="btn btn-primary lms-cta">Kirim</button>
                </div>
            </form>
        @endif

        @if($submission)
            <hr>
            <h6>Submission Terakhir</h6>
            <div class="small text-muted">Versi {{ $submission->version }} • {{ $submission->submitted_at?->format('d M Y H:i') }}</div>
            <div class="mb-2">
                @if($submission->late)
                    <span class="badge bg-warning text-dark">Terlambat ({{ $submission->late_minutes }} menit)</span>
                @endif
                <span class="badge bg-secondary">{{ $submission->status }}</span>
                @if($submission->total_score !== null)
                    <span class="badge bg-success">Nilai: {{ $submission->total_score }}</span>
                @endif
            </div>
            @if($assignment->type === 'quiz' && $submission->quiz_answers)
                <div class="mt-3">
                    <h6>Jawaban Quiz</h6>
                    <ul class="list-group">
                        @foreach($submission->quiz_answers as $qa)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold">{{ $qa['question'] ?? $qa['question_id'] }}</div>
                                    <small class="text-muted">Jawaban: {{ $qa['selected'] ?? '-' }} • Benar: {{ $qa['correct'] ?? '-' }}</small>
                                </div>
                                <span class="badge bg-{{ ($qa['is_correct'] ?? false) ? 'success' : 'danger' }}">{{ $qa['score'] ?? 0 }} pts</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if($submission->content_text)
                <div class="mb-2"><strong>Teks:</strong> {{ Str::limit($submission->content_text, 200) }}</div>
            @endif
            @if($submission->link_url)
                <div class="mb-2"><strong>Link:</strong> <a href="{{ $submission->link_url }}" target="_blank">{{ $submission->link_url }}</a></div>
            @endif
            @if($submission->file_url)
                <div class="mb-2"><strong>File:</strong> <a href="{{ route('participant.submissions.file', $submission) }}">Unduh Aman</a></div>
            @endif
            @if($submission->feedback)
                <div class="mb-2"><strong>Feedback:</strong> {{ $submission->feedback }}</div>
            @endif
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if($assignment->type === 'quiz' && $quizExpiresAt)
<script>
    (function () {
        const timerEl = document.getElementById('quizTimer');
        const valueEl = document.getElementById('quizTimerValue');
        if (!timerEl || !valueEl) return;
        const expire = new Date(timerEl.dataset.expire);
        const autoSubmit = timerEl.dataset.autoSubmit === '1';
        const form = document.getElementById('quizForm');
        function tick() {
            const now = new Date();
            const diff = expire - now;
            if (diff <= 0) {
                valueEl.textContent = 'habis';
                if (autoSubmit && form) {
                    form.submit();
                } else if (form) {
                    form.querySelectorAll('button, input, textarea, select').forEach(el => el.disabled = true);
                }
                return;
            }
            const minutes = Math.floor(diff / 60000);
            const seconds = Math.floor((diff % 60000) / 1000);
            valueEl.textContent = `${minutes}m ${seconds}s`;
            requestAnimationFrame(tick);
        }
        tick();
    })();
</script>
@endif
@endpush
