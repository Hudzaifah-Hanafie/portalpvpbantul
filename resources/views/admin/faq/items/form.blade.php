@extends('layouts.admin')

@php
    $isEdit = $item->exists;
    $statusOptions = \App\Models\FaqItem::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $isEdit ? 'Ubah' : 'Tambah' }} FAQ</h4>
        <small class="text-muted">Isi pertanyaan, jawaban, serta kategori agar tampil rapi pada UI.</small>
    </div>
    <a href="{{ route('admin.faq-item.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Validasi gagal.</strong> Pastikan pilih kategori, isi pertanyaan, dan urutan tidak bernilai negatif.
    </div>
@endif

<form action="{{ $isEdit ? route('admin.faq-item.update', $item) : route('admin.faq-item.store') }}" method="POST" class="bg-white rounded shadow-sm p-4" novalidate>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row g-4">
        <div class="col-md-6">
            <label class="form-label">Kategori</label>
            <select name="faq_category_id" class="form-select @error('faq_category_id') is-invalid @enderror" required>
                <option value="">Pilih Kategori</option>
                @foreach($categories as $id => $name)
                    <option value="{{ $id }}" {{ (string) old('faq_category_id', $item->faq_category_id) === (string) $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
            @error('faq_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">Urutan</label>
            <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror" value="{{ old('urutan', $item->urutan ?? 0) }}" min="0">
            <small class="text-muted">Kosongkan untuk otomatis menjadi 0.</small>
            @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="faq-active" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
                <label for="faq-active" class="form-check-label">Aktif</label>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <label class="form-label">Pertanyaan</label>
        <input type="text" name="question" class="form-control @error('question') is-invalid @enderror" value="{{ old('question', $item->question) }}" maxlength="255" required>
        @error('question') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mt-3">
        <label class="form-label">Jawaban</label>
        <textarea name="answer" class="form-control @error('answer') is-invalid @enderror" rows="6">{{ old('answer', $item->answer) }}</textarea>
        @error('answer') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <small class="text-muted">Dapat menggunakan HTML dasar untuk list atau penekanan.</small>
    </div>

    <div class="mt-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            @foreach($statusOptions as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $item->status ?? 'draft') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <small class="text-muted">Instruktur/editor dapat memilih Draft/Pending; Reviewer/Admin dapat langsung memublikasikan.</small>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="text-end mt-4">
        <button class="btn btn-primary px-4">{{ $isEdit ? 'Perbarui' : 'Simpan' }}</button>
    </div>
</form>
@endsection
