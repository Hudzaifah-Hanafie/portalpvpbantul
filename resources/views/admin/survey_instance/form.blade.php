@extends('layouts.admin')

@php
    $statusOptions = \App\Models\SurveyInstance::statuses();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $instance->exists ? 'Ubah' : 'Buat' }} Survei Instans</h4>
        <small class="text-muted">Binding survei ke kelas/instruktur dan atur jendela buka/tutup.</small>
    </div>
    <a href="{{ route('admin.survey-instance.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<form action="{{ $action }}" method="POST" class="bg-white rounded shadow-sm p-4" novalidate>
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Survei</label>
            <select name="survey_id" class="form-select @error('survey_id') is-invalid @enderror" required>
                <option value="">Pilih Survei</option>
                @foreach($surveys as $id => $title)
                    <option value="{{ $id }}" @selected(old('survey_id', $instance->survey_id) == $id)>{{ $title }}</option>
                @endforeach
            </select>
            @error('survey_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror">
                @foreach($statusOptions as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $instance->status ?? 'draft') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <small class="text-muted">Buka akan langsung bisa diisi; Tutup menutup respons.</small>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-6">
            <label class="form-label">Kelas (opsional)</label>
            <select name="course_class_id" class="form-select @error('course_class_id') is-invalid @enderror">
                <option value="">Tanpa binding kelas</option>
                @foreach($classes as $id => $title)
                    <option value="{{ $id }}" @selected(old('course_class_id', $instance->course_class_id) == $id)>{{ $title }}</option>
                @endforeach
            </select>
            @error('course_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Instruktur (opsional)</label>
            <input type="text" name="instructor_id" class="form-control @error('instructor_id') is-invalid @enderror" value="{{ old('instructor_id', $instance->instructor_id) }}" placeholder="UUID instruktur">
            <small class="text-muted">Isi jika survei ditujukan untuk instruktur tertentu.</small>
            @error('instructor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-4">
            <label class="form-label">Mulai</label>
            <input type="datetime-local" name="opens_at" class="form-control @error('opens_at') is-invalid @enderror" value="{{ old('opens_at', optional($instance->opens_at)->format('Y-m-d\TH:i')) }}">
            @error('opens_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Selesai</label>
            <input type="datetime-local" name="closes_at" class="form-control @error('closes_at') is-invalid @enderror" value="{{ old('closes_at', optional($instance->closes_at)->format('Y-m-d\TH:i')) }}">
            @error('closes_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Ambang Respons Minimum</label>
            <input type="number" name="min_responses_threshold" class="form-control @error('min_responses_threshold') is-invalid @enderror" value="{{ old('min_responses_threshold', $instance->min_responses_threshold ?? 5) }}" min="1" max="1000">
            <small class="text-muted">Hasil baru ditampilkan jika respons ≥ ambang.</small>
            @error('min_responses_threshold') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="text-end mt-4">
        <button class="btn btn-primary px-4">{{ $instance->exists ? 'Perbarui' : 'Simpan' }}</button>
    </div>
</form>
@endsection
