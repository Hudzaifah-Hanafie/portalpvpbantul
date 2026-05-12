@extends('layouts.app')

@section('title', $survey->title)

@section('content')
<section class="py-5 survey-hero">
    <div class="container position-relative">
        <div class="survey-blob"></div>
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card shadow-sm border-0 mb-4 survey-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <p class="text-primary fw-semibold mb-1 text-uppercase small">Survey Resmi</p>
                                <h2 class="fw-bold mb-1">{{ $survey->title }}</h2>
                                @if($survey->description)
                                    <p class="text-muted mb-2">{{ $survey->description }}</p>
                                @endif
                                @if($survey->welcome_message)
                                    <div class="alert alert-primary small mb-0">{{ $survey->welcome_message }}</div>
                                @endif
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $isOpen ? 'bg-success' : 'bg-secondary' }}">{{ $isOpen ? 'Menerima respons' : 'Ditutup' }}</span>
                                <div class="text-muted small">Butuh waktu ±3 menit</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if($isOpen)
                    @if($survey->require_login && !auth()->check())
                        <div class="alert alert-warning">
                            Survey ini mengharuskan login. <a href="{{ route('login') }}" class="alert-link">Login sekarang</a>.
                        </div>
                    @endif
                    @php
                        $sections = $survey->sections->count() ? $survey->sections : collect([(object)[
                            'id' => 'default',
                            'title' => 'Form',
                            'description' => null,
                            'questions' => $survey->questions
                        ]]);
                    @endphp
                    <form action="{{ route('surveys.submit', $survey) }}" method="POST" class="card shadow-sm border-0 survey-card" enctype="multipart/form-data" id="survey-form">
                        @csrf
                        <input type="text" name="hp_token" class="d-none" tabindex="-1" autocomplete="off">
                        <div class="card-body">
                            <div class="d-flex flex-column flex-md-row align-items-md-center gap-3 mb-3">
                                <div class="flex-grow-1">
                                    <div class="progress" style="height:10px;">
                                        <div class="progress-bar bg-primary" id="section-progress" style="width:0%;"></div>
                                    </div>
                                </div>
                                <div class="badge bg-light text-dark border">Multi-step form</div>
                            </div>
                            @foreach($sections as $sectionIndex => $section)
                                <div class="survey-section" data-section="{{ $sectionIndex }}" style="{{ $sectionIndex === 0 ? '' : 'display:none;' }}">
                                    <div class="mb-3">
                                        <h5 class="fw-semibold mb-1">{{ $section->title ?? 'Bagian ' . ($sectionIndex+1) }}</h5>
                                        @if(!empty($section->description))<p class="text-muted small mb-2">{{ $section->description }}</p>@endif
                                    </div>
                                    @foreach(($section->questions ?? $survey->questions->where('survey_section_id', $section->id)) as $question)
                                        <div class="mb-4 question-wrapper shadow-sm p-3 rounded-3" data-question="{{ $question->id }}" data-visibility='@json($question->visibility_rules ?? [])'>
                                            <label class="fw-semibold d-block mb-1" for="q-{{ $question->id }}">
                                                <span class="badge bg-primary-subtle text-primary me-2">{{ $loop->parent->iteration }}.{{ $loop->iteration }}</span>
                                                {{ $question->question }}
                                                @if($question->is_required)<span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">Wajib</span>@endif
                                            </label>
                                            @if($question->description)
                                                <small class="text-muted d-block mb-2">{{ $question->description }}</small>
                                            @endif

                                            @php
                                                $fieldName = "responses.{$question->id}";
                                                $oldValue = old("responses.{$question->id}");
                                            @endphp

                                            @if(in_array($question->type, ['short_text', 'long_text']))
                                                @php
                                                    $maxLength = $question->settings['max_length'] ?? ($question->type === 'short_text' ? 255 : 2000);
                                                    $inputType = ($question->validation['format'] ?? '') === 'phone' ? 'tel' : 'text';
                                                @endphp
                                                @if($question->type === 'short_text')
                                                    <input id="q-{{ $question->id }}" type="{{ $inputType }}" name="responses[{{ $question->id }}]" class="form-control form-control-lg rounded-3 @error($fieldName) is-invalid @enderror char-count" placeholder="{{ $question->placeholder }}" value="{{ $oldValue }}" {{ $question->is_required ? 'required' : '' }} maxlength="{{ $maxLength }}" data-max="{{ $maxLength }}" aria-describedby="help-{{ $question->id }}">
                                                @else
                                                    <textarea id="q-{{ $question->id }}" name="responses[{{ $question->id }}]" rows="4" class="form-control rounded-3 @error($fieldName) is-invalid @enderror char-count" placeholder="{{ $question->placeholder }}" {{ $question->is_required ? 'required' : '' }} maxlength="{{ $maxLength }}" data-max="{{ $maxLength }}" aria-describedby="help-{{ $question->id }}">{{ $oldValue }}</textarea>
                                                @endif
                                                <small id="help-{{ $question->id }}" class="text-muted remaining" data-for="responses[{{ $question->id }}]"></small>
                                            @elseif(in_array($question->type, ['choice_single', 'dropdown', 'choice_single_other']))
                                                @if($question->type === 'dropdown')
                                                    <select id="q-{{ $question->id }}" name="responses[{{ $question->id }}]" class="form-select form-select-lg rounded-3 @error($fieldName) is-invalid @enderror" {{ $question->is_required ? 'required' : '' }}>
                                                        <option value="">Pilih jawaban</option>
                                                        @foreach($question->options as $option)
                                                            <option value="{{ $option->id }}" {{ (string) $oldValue === (string) $option->id ? 'selected' : '' }}>
                                                                {{ $option->label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <div class="d-flex flex-column gap-2">
                                                        @foreach($question->options as $option)
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="responses[{{ $question->id }}]" id="q{{ $question->id }}-{{ $option->id }}" value="{{ $option->id }}" {{ (string) $oldValue === (string) $option->id ? 'checked' : '' }} {{ $question->is_required ? 'required' : '' }}>
                                                                <label class="form-check-label" for="q{{ $question->id }}-{{ $option->id }}">{{ $option->label }}</label>
                                                            </div>
                                                        @endforeach
                                                        @if($question->type === 'choice_single_other')
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="responses[{{ $question->id }}]" id="q{{ $question->id }}-other" value="__other">
                                                                <label class="form-check-label" for="q{{ $question->id }}-other">Lainnya</label>
                                                            </div>
                                                            <input type="text" name="responses_other[{{ $question->id }}]" class="form-control form-control-sm mt-2" placeholder="Tulis jawaban lainnya">
                                                        @endif
                                                    </div>
                                                @endif
                                            @elseif($question->type === 'choice_multiple')
                                                <div class="d-flex flex-column gap-2" role="group" aria-labelledby="q-{{ $question->id }}">
                                                    @foreach($question->options as $option)
                                                        @php
                                                            $isChecked = is_array($oldValue) && in_array($option->id, $oldValue);
                                                        @endphp
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="responses[{{ $question->id }}][]" id="q{{ $question->id }}-{{ $option->id }}" value="{{ $option->id }}" {{ $isChecked ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="q{{ $question->id }}-{{ $option->id }}">{{ $option->label }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @elseif($question->type === 'grid_single')
                                                <div class="table-responsive" role="group" aria-labelledby="q-{{ $question->id }}">
                                                    <table class="table table-sm align-middle">
                                                        <thead>
                                                            <tr><th></th>
                                                                @foreach($question->settings['columns'] ?? [] as $col)
                                                                    <th class="text-center">{{ $col }}</th>
                                                                @endforeach
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($question->settings['rows'] ?? [] as $rowIndex => $rowLabel)
                                                                <tr>
                                                                    <td class="fw-semibold">{{ $rowLabel }}</td>
                                                                    @foreach($question->settings['columns'] ?? [] as $colIndex => $col)
                                                                        <td class="text-center">
                                                                            <input type="radio" name="responses[{{ $question->id }}][{{ $rowIndex }}]" value="{{ $col }}" {{ (data_get($oldValue, $rowIndex) === $col) ? 'checked' : '' }}>
                                                                        </td>
                                                                    @endforeach
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @elseif($question->type === 'grid_multiple')
                                                <div class="table-responsive" role="group" aria-labelledby="q-{{ $question->id }}">
                                                    <table class="table table-sm align-middle">
                                                        <thead>
                                                            <tr><th></th>
                                                                @foreach($question->settings['columns'] ?? [] as $col)
                                                                    <th class="text-center">{{ $col }}</th>
                                                                @endforeach
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($question->settings['rows'] ?? [] as $rowIndex => $rowLabel)
                                                                <tr>
                                                                    <td class="fw-semibold">{{ $rowLabel }}</td>
                                                                    @foreach($question->settings['columns'] ?? [] as $colIndex => $col)
                                                                        @php $rowOld = data_get($oldValue, $rowIndex, []); @endphp
                                                                        <td class="text-center">
                                                                            <input type="checkbox" name="responses[{{ $question->id }}][{{ $rowIndex }}][]" value="{{ $col }}" {{ in_array($col, $rowOld ?? []) ? 'checked' : '' }}>
                                                                        </td>
                                                                    @endforeach
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @elseif($question->type === 'linear_scale' || $question->type === 'rating')
                                                @php
                                                    $min = $question->settings['min'] ?? 1;
                                                    $max = $question->settings['max'] ?? 5;
                                                    $current = $oldValue ?? $min;
                                                @endphp
                                                <div class="d-flex align-items-center gap-3 bg-light p-3 rounded-3" aria-label="{{ $question->question }}">
                                                    <small class="text-muted">{{ $question->settings['left_label'] ?? $min }}</small>
                                                    <input type="range" class="form-range flex-fill" name="responses[{{ $question->id }}]" min="{{ $min }}" max="{{ $max }}" value="{{ $current }}" oninput="document.getElementById('scale-value-{{ $question->id }}').innerText=this.value" {{ $question->is_required ? 'required' : '' }}>
                                                    <small class="text-muted">{{ $question->settings['right_label'] ?? $max }}</small>
                                                    <span class="badge bg-primary" id="scale-value-{{ $question->id }}">{{ $current }}</span>
                                                </div>
                                            @elseif($question->type === 'file_upload')
                                                <input type="file" name="responses[{{ $question->id }}]" class="form-control @error($fieldName) is-invalid @enderror" {{ $question->is_required ? 'required' : '' }}>
                                                @if(!empty($question->settings['max_size']))
                                                    <small class="text-muted">Maks {{ $question->settings['max_size'] }} MB</small>
                                                @endif
                                            @elseif($question->type === 'date')
                                                <input type="date" name="responses[{{ $question->id }}]" class="form-control form-control-lg rounded-3 @error($fieldName) is-invalid @enderror" value="{{ $oldValue }}" {{ $question->is_required ? 'required' : '' }}>
                                            @elseif($question->type === 'time')
                                                <input type="time" name="responses[{{ $question->id }}]" class="form-control form-control-lg rounded-3 @error($fieldName) is-invalid @enderror" value="{{ $oldValue }}" {{ $question->is_required ? 'required' : '' }}>
                                            @endif

                                            @error($fieldName)
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                        <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            @if($survey->show_progress)
                                <div class="d-flex align-items-center gap-2 text-muted small" style="min-width:200px;">
                                    <div class="progress flex-fill" style="height: 6px; min-width:160px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 100%;"></div>
                                    </div>
                                    <span>{{ $survey->questions->count() }} pertanyaan</span>
                                </div>
                            @endif
                            <div class="d-flex flex-wrap gap-2">
                                <x-ui.button variant="secondary" id="section-prev">← Kembali</x-ui.button>
                                <x-ui.button variant="primary" class="btn-outline-primary" id="section-next">Lanjut →</x-ui.button>
                                <x-ui.button variant="primary" class="d-none" id="section-submit" type="submit">Kirim Respons</x-ui.button>
                            </div>
                            @php
                                $captchaProvider = config('survey.captcha_provider', 'recaptcha');
                            @endphp
                            @if($captchaProvider === 'hcaptcha' && config('services.hcaptcha.site_key'))
                                <div class="mt-3">
                                    <div class="h-captcha" data-sitekey="{{ config('services.hcaptcha.site_key') }}"></div>
                                </div>
                            @elseif($captchaProvider === 'turnstile' && config('services.turnstile.site_key'))
                                <div class="mt-3">
                                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                                </div>
                            @elseif(config('services.recaptcha.site_key'))
                                <div class="mt-3">
                                    <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                                </div>
                            @endif
                        </div>
                    </form>
                @else
                    <div class="alert alert-secondary">Survey ini sudah tidak menerima respons baru.</div>
                    @if($survey->thank_you_message)
                        <div class="alert alert-info mt-3">{{ $survey->thank_you_message }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</section>
@push('styles')
<style>
    .survey-hero { background: linear-gradient(135deg, #f7f9ff, #e8f3ff); }
    .survey-card { border-radius: 18px; backdrop-filter: blur(2px); }
    .survey-blob { position:absolute; top:-40px; right:20px; width:160px; height:160px; background:radial-gradient(circle at 20% 20%, rgba(37,99,235,.25), rgba(37,99,235,0)); filter: blur(30px); }
    .form-check { padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 12px; }
    .form-check-input { margin-right: 10px; }
    .form-check + .form-check { margin-top: 6px; }
    .question-wrapper { background: #fff; border: 1px solid #eef1f7; transition: all .2s ease; }
    .question-wrapper:hover, .question-wrapper:focus-within { border-color: #d0d9ff; box-shadow: 0 8px 24px rgba(55, 116, 255, 0.08); }
    .btn.btn-outline-primary { border-width: 2px; }
    .btn-primary { background: linear-gradient(120deg, #2563eb, #4f46e5); border: none; box-shadow: 0 10px 25px rgba(79, 70, 229, .25); }
    .btn-primary:hover { filter: brightness(0.98); }
    .survey-hero .badge { padding: 8px 12px; font-weight: 600; }
    .survey-hero h2 { letter-spacing: -0.2px; }
    .survey-hero p { color: #4b5563; }
    @media (max-width: 768px) {
        .survey-card { border-radius: 14px; }
        .question-wrapper { padding: 14px !important; }
        .survey-hero { padding-top: 2rem; }
        .survey-blob { display: none; }
        .card-footer .btn { width: 100%; justify-content: center; }
        .card-footer .d-flex { width: 100%; }
    }
</style>
@endpush

@php
    $skipRulesPayload = $survey->skipRules->map(function ($r) {
        return [
            'question_id' => $r->survey_question_id,
            'target_section_id' => $r->target_section_id,
            'conditions' => $r->conditions,
        ];
    })->values();
@endphp

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sections = Array.from(document.querySelectorAll('.survey-section'));
        if (!sections.length) return;
        let current = 0;
        const nextBtn = document.getElementById('section-next');
        const prevBtn = document.getElementById('section-prev');
        const submitBtn = document.getElementById('section-submit');
        const progressBar = document.getElementById('section-progress');
        const form = document.getElementById('survey-form');
        const storageKey = 'survey-draft-{{ $survey->id }}';
        const serverDraft = @json($draftData ?? []);
        const skipRules = @json($skipRulesPayload);
        const sectionOrder = @json($sections->pluck('id')->values());

        function updateView() {
            sections.forEach((sec, idx) => {
                sec.style.display = idx === current ? '' : 'none';
            });
            prevBtn.disabled = current === 0;
            const last = current === sections.length - 1;
            nextBtn.classList.toggle('d-none', last);
            submitBtn.classList.toggle('d-none', !last);
            const percent = Math.round(((current + 1) / sections.length) * 100);
            progressBar.style.width = percent + '%';
        }

        function evaluateSkipTarget() {
            if (!skipRules.length) return null;
            const fd = new FormData(form);
            for (const rule of skipRules) {
                const key = `responses[${rule.question_id}]`;
                let value = fd.getAll(key);
                if (value.length === 0) value = fd.get(key);
                const cond = rule.conditions || {};
                let matched = false;
                if (cond.selected_option_ids && cond.selected_option_ids.length) {
                    const selected = Array.isArray(value) ? value : [value];
                    matched = selected.some(v => cond.selected_option_ids.includes(v));
                }
                if (cond.equals_text && value) {
                    matched = matched || String(value).trim().toLowerCase() === String(cond.equals_text).trim().toLowerCase();
                }
                if (matched) {
                    const targetIdx = sectionOrder.indexOf(rule.target_section_id);
                    if (targetIdx !== -1) return targetIdx;
                }
            }
            return null;
        }

        function applyVisibility() {
            const fd = new FormData(form);
            document.querySelectorAll('.survey-section').forEach(section => {
                section.querySelectorAll('[data-question]').forEach(wrapper => {
                    const visRules = JSON.parse(wrapper.dataset.visibility || '[]');
                    if (!visRules.length) {
                        wrapper.style.display = '';
                        return;
                    }
                    let show = true;
                    visRules.forEach(rule => {
                        const key = `responses[${rule.question_id}]`;
                        let value = fd.getAll(key);
                        if (value.length === 0) value = fd.get(key);
                        let matched = false;
                        if (rule.equals && value) {
                            matched = String(value).trim().toLowerCase() === String(rule.equals).trim().toLowerCase();
                        }
                        if (rule.in && Array.isArray(value)) {
                            matched = value.some(v => rule.in.includes(v));
                        }
                        if (rule.action === 'hide' && matched) {
                            show = false;
                        }
                        if (rule.action === 'show' && matched) {
                            show = true;
                        }
                    });
                    wrapper.style.display = show ? '' : 'none';
                });
            });
        }

        nextBtn?.addEventListener('click', () => {
            const target = evaluateSkipTarget();
            if (target !== null) {
                current = target;
                updateView();
                return;
            }
            if (current < sections.length - 1) {
                current++;
                updateView();
            }
        });
        prevBtn?.addEventListener('click', () => {
            if (current > 0) {
                current--;
                updateView();
            }
        });

        updateView();

        function saveDraft() {
            const data = new FormData(form);
            const obj = {};
            data.forEach((value, key) => {
                if (key.startsWith('responses')) {
                    if (obj[key]) {
                        if (!Array.isArray(obj[key])) obj[key] = [obj[key]];
                        obj[key].push(value);
                    } else {
                        obj[key] = value;
                    }
                }
            });
            localStorage.setItem(storageKey, JSON.stringify(obj));
            fetch('{{ route('surveys.draft', $survey) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            }).catch(() => {});
        }

        function restoreDraft() {
            const raw = localStorage.getItem(storageKey);
            let saved = {};
            if (raw) {
                try { saved = JSON.parse(raw) || {}; } catch (e) {}
            } else if (serverDraft && Object.keys(serverDraft).length) {
                saved = serverDraft;
            }
            try {
                Object.entries(saved).forEach(([key, value]) => {
                    const field = form.querySelector(`[name="${key}"]`) || form.querySelector(`[name="${key}[]"]`);
                    if (!field) return;
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        const inputs = form.querySelectorAll(`[name="${key}"]`);
                        inputs.forEach(input => {
                            if (Array.isArray(value)) {
                                input.checked = value.includes(input.value);
                            } else {
                                input.checked = input.value === value;
                            }
                        });
                    } else {
                        field.value = value;
                    }
                });
            } catch (e) {}
        }

        form.addEventListener('input', saveDraft);
        form.addEventListener('change', saveDraft);
        form.addEventListener('input', applyVisibility);
        form.addEventListener('change', applyVisibility);
        form.addEventListener('submit', () => localStorage.removeItem(storageKey));
        restoreDraft();
        applyVisibility();

        form.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const target = e.target;
                if (['TEXTAREA', 'BUTTON'].includes(target.tagName)) return;
                e.preventDefault();
                if (!submitBtn.classList.contains('d-none')) {
                    submitBtn.click();
                } else {
                    nextBtn.click();
                }
            }
        });

        // character counter
        document.querySelectorAll('.char-count').forEach((el) => {
            const max = parseInt(el.dataset.max || '0', 10);
            const label = document.querySelector(`.remaining[data-for=\"${el.name}\"]`);
            const update = () => {
                if (!label || !max) return;
                const length = el.value?.length || 0;
                label.textContent = `${length}/${max} karakter`;
            };
            el.addEventListener('input', update);
            update();
        });

        // offline indicator & sync
        const offlineAlert = document.createElement('div');
        offlineAlert.className = 'alert alert-warning d-none mt-3';
        offlineAlert.textContent = 'Anda offline. Jawaban akan dikirim saat online.';
        form.prepend(offlineAlert);
        function updateOnline() {
            offlineAlert.classList.toggle('d-none', navigator.onLine);
            if (navigator.onLine && 'serviceWorker' in navigator && navigator.serviceWorker.controller) {
                navigator.serviceWorker.ready.then(reg => {
                    if (reg.sync) reg.sync.register('sync-survey');
                }).catch(()=>{});
            }
        }
        window.addEventListener('online', updateOnline);
        window.addEventListener('offline', updateOnline);
        updateOnline();
    });
</script>
@php $captchaProvider = config('survey.captcha_provider', 'recaptcha'); @endphp
@if($captchaProvider === 'hcaptcha' && config('services.hcaptcha.site_key'))
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
@elseif($captchaProvider === 'turnstile' && config('services.turnstile.site_key'))
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@elseif(config('services.recaptcha.site_key'))
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
@endpush
@endsection
@php $isOfflineReady = true; @endphp
