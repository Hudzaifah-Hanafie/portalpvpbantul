@extends('layouts.admin')

@section('content')
<div class="card shadow-sm border-0 col-lg-10">
    <div class="card-header bg-white">
        <h5 class="mb-0">{{ $service->exists ? 'Ubah' : 'Tambah' }} Layanan Pelatihan</h5>
    </div>
    <div class="card-body">
        <form id="training-service-form" action="{{ $action }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($method === 'PUT') @method('PUT') @endif
            <div class="mb-3">
                <label class="form-label fw-bold">Judul</label>
                <input type="text" name="judul" class="form-control" value="{{ old('judul', $service->judul) }}" required>
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
                <div class="wys-editor form-control" contenteditable="true" data-editor-target="#deskripsi-input">{!! \App\Support\HtmlSanitizer::clean(old('deskripsi', $service->deskripsi)) !!}</div>
                <textarea id="deskripsi-input" name="deskripsi" class="d-none">{{ \App\Support\HtmlSanitizer::clean(old('deskripsi', $service->deskripsi)) }}</textarea>
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
                <div class="wys-editor form-control" contenteditable="true" data-editor-target="#fasilitas-input">{!! \App\Support\HtmlSanitizer::clean(old('fasilitas', $service->fasilitas)) !!}</div>
                <textarea id="fasilitas-input" name="fasilitas" class="d-none">{{ \App\Support\HtmlSanitizer::clean(old('fasilitas', $service->fasilitas)) }}</textarea>
                <small class="text-muted">Gunakan toolbar untuk format dasar (bold, italic, daftar, tautan).</small>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Gambar (opsional)</label>
                @if($service->gambar)
                    <div class="mb-2"><img src="{{ asset($service->gambar) }}" width="180" class="img-thumbnail"></div>
                @endif
                <input type="file" name="gambar" class="form-control" accept="image/*">
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Urutan</label>
                    <input type="number" name="urutan" class="form-control" value="{{ old('urutan', $service->urutan ?? 0) }}">
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
                <select name="status" class="form-select">
                    @foreach(\App\Models\TrainingService::statuses() as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $service->status ?? null) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-success mt-3">Simpan</button>
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
                // Perbarui hidden textarea after command
                editors.forEach(editor => {
                    const target = document.querySelector(editor.dataset.editorTarget);
                    if (target) {
                        target.value = editor.innerHTML.trim();
                    }
                });
            });
        });

        const form = document.getElementById('training-service-form');
        if (form) {
            form.addEventListener('submit', function () {
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
