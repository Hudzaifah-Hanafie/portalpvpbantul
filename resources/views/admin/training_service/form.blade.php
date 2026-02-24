@extends('layouts.admin')

@section('content')
<div class="card shadow-sm border-0 col-lg-10 mx-auto">
    <div class="card-header bg-white">
        <h5 class="mb-0">{{ $service->exists ? 'Edit' : 'Tambah' }} Layanan Pelatihan</h5>
    </div>
    <div class="card-body">
        <form id="training-service-form" action="{{ $action }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($method === 'PUT') @method('PUT') @endif
            <div class="mb-3">
                <label class="form-label fw-bold">Judul</label>
                <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $service->judul) }}" required maxlength="100">
                <small class="text-muted">Maksimal 100 karakter.</small>
                @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Deskripsi</label>
                <div class="wys-toolbar mb-2">
                    <button type="button" class="btn btn-sm btn-light" data-editor-action="bold"><i class="fas fa-bold"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-editor-action="italic"><i class="fas fa-italic"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-editor-action="underline"><i class="fas fa-underline"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-editor-action="insertUnorderedList"><i class="fas fa-list-ul"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-editor-action="insertOrderedList"><i class="fas fa-list-ol"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-editor-action="createLink"><i class="fas fa-link"></i></button>
                </div>
                <div class="wys-editor form-control @error('deskripsi') is-invalid @enderror" contenteditable="true" data-editor-target="#deskripsi-input">{!! old('deskripsi', $service->deskripsi) !!}</div>
                <textarea id="deskripsi-input" name="deskripsi" class="d-none">{{ old('deskripsi', $service->deskripsi) }}</textarea>
                @error('deskripsi') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Fasilitas (boleh HTML / list)</label>
                <div class="wys-toolbar mb-2">
                    <button type="button" class="btn btn-sm btn-light" data-editor-action="bold"><i class="fas fa-bold"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-editor-action="italic"><i class="fas fa-italic"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-editor-action="underline"><i class="fas fa-underline"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-editor-action="insertUnorderedList"><i class="fas fa-list-ul"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-editor-action="insertOrderedList"><i class="fas fa-list-ol"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-editor-action="createLink"><i class="fas fa-link"></i></button>
                </div>
                <div class="wys-editor form-control @error('fasilitas') is-invalid @enderror" contenteditable="true" data-editor-target="#fasilitas-input">{!! old('fasilitas', $service->fasilitas) !!}</div>
                <textarea id="fasilitas-input" name="fasilitas" class="d-none">{{ old('fasilitas', $service->fasilitas) }}</textarea>
                <small class="text-muted">Gunakan toolbar untuk format dasar (bold, italic, daftar, tautan).</small>
                @error('fasilitas') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Gambar (opsional)</label>
                @if($service->gambar)
                    <div class="mb-2"><img src="{{ asset($service->gambar) }}" width="180" class="img-thumbnail"></div>
                @endif
                <input type="file" name="gambar" id="gambar-input" class="form-control @error('gambar') is-invalid @enderror" accept=".jpg,.jpeg,.png">
                <small class="text-muted">Format: JPG, JPEG, PNG. Maksimal ukuran: 2 MB.</small>
                <div id="file-size-error" class="text-danger small mt-1 d-none">Ukuran file terlalu besar! Maksimal 2 MB.</div>
                @error('gambar') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Urutan</label>
                    <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror" value="{{ old('urutan', $service->urutan ?? 0) }}" min="0" max="999" required>
                    <small class="text-muted">Angka 0-999 untuk mengurutkan tampilan.</small>
                    @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 d-flex align-items-center">
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" {{ old('is_active', $service->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">Aktif</label>
                    </div>
                </div>
            </div>
            <div class="mb-3 mt-2">
                <label class="form-label fw-bold">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    @foreach(\App\Models\TrainingService::statuses() as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $service->status ?? null) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <button type="submit" id="submit-btn" class="btn btn-success mt-3"><i class="fas fa-save me-1"></i> Simpan</button>
            <a href="{{ route('admin.training-service.index') }}" class="btn btn-secondary mt-3">Kembali</a>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .wys-toolbar .btn { border-color: #e5e7eb; }
    .wys-editor {
        min-height: 140px;
        overflow: auto;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('training-service-form');
        const gambarInput = document.getElementById('gambar-input');
        const fileSizeError = document.getElementById('file-size-error');
        const submitBtn = document.getElementById('submit-btn');
        const MAX_SIZE = 2 * 1024 * 1024; // 2MB in bytes

        // Validasi ukuran file di sisi klien (Client-side validation)
        if (gambarInput) {
            gambarInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    if (this.files[0].size > MAX_SIZE) {
                        fileSizeError.classList.remove('d-none');
                        this.classList.add('is-invalid');
                        submitBtn.disabled = true;
                    } else {
                        fileSizeError.classList.add('d-none');
                        this.classList.remove('is-invalid');
                        submitBtn.disabled = false;
                    }
                }
            });
        }

        const toolbars = document.querySelectorAll('.wys-toolbar');
        const editors = document.querySelectorAll('.wys-editor');
        let activeEditor = null;
        let lastSelection = null;

        // Sink initial content to textareas on load
        editors.forEach(editor => {
            const target = document.querySelector(editor.dataset.editorTarget);
            if (target && !target.value) {
                target.value = editor.innerHTML.trim();
            }

            editor.addEventListener('focus', () => activeEditor = editor);
            editor.addEventListener('click', () => activeEditor = editor);
            editor.addEventListener('input', () => {
                const target = document.querySelector(editor.dataset.editorTarget);
                if (target) {
                    target.value = editor.innerHTML.trim();
                }
            });
            ['keyup', 'mouseup'].forEach(evt => {
                editor.addEventListener(evt, () => {
                    const sel = window.getSelection();
                    if (sel && sel.rangeCount) {
                        const range = sel.getRangeAt(0);
                        if (editor.contains(range.commonAncestorContainer)) {
                            lastSelection = range;
                        }
                    }
                });
            });
        });

        document.addEventListener('selectionchange', () => {
            if (!activeEditor) return;
            const sel = window.getSelection();
            if (sel && sel.rangeCount) {
                const range = sel.getRangeAt(0);
                if (activeEditor.contains(range.commonAncestorContainer)) {
                    lastSelection = range;
                }
            }
        });

        toolbars.forEach(toolbar => {
            toolbar.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-editor-action]');
                if (!btn) return;
                const action = btn.dataset.editorAction;
                let value = null;
                if (action === 'createLink') {
                    value = prompt('Masukkan URL tautan');
                    if (!value) return;
                }
                if (activeEditor) {
                    activeEditor.focus();
                }
                if (lastSelection) {
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(lastSelection);
                }
                document.execCommand(action, false, value);
                // Update hidden textarea after command
                editors.forEach(editor => {
                    const target = document.querySelector(editor.dataset.editorTarget);
                    if (target) {
                        target.value = editor.innerHTML.trim();
                    }
                });
            });
        });

        if (form) {
            form.addEventListener('submit', function (e) {
                // Double check before submit
                if (gambarInput && gambarInput.files && gambarInput.files[0] && gambarInput.files[0].size > MAX_SIZE) {
                    e.preventDefault();
                    alert('Maaf, ukuran file terlalu besar (maksimal 2MB). Silakan pilih file yang lebih kecil.');
                    return;
                }

                editors.forEach(editor => {
                    const target = document.querySelector(editor.dataset.editorTarget);
                    if (target) {
                        target.value = editor.innerHTML.trim();
                    }
                });
            });
        }
    })();
</script>
@endpush
