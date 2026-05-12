@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $pageTitle }}</h4>
        <small class="text-muted">Hanya simpan tautan (Google Drive/OneDrive/dll) untuk hemat storage.</small>
    </div>
    <a href="{{ route($routePrefix . '.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ $action }}" method="POST" class="row g-3">
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif
            <div class="col-md-6">
                <label class="form-label">Kejuruan</label>
                <select name="program_id" class="form-select @error('program_id') is-invalid @enderror" required>
                    <option value="">Pilih kejuruan</option>
                    @foreach($programOptions as $id => $label)
                        <option value="{{ $id }}" @selected(old('program_id', $module->program_id) === $id)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('program_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            @if($showOwnerField)
                <div class="col-md-6">
                    <label class="form-label">Instruktur (opsional)</label>
                    <select name="owner_id" class="form-select @error('owner_id') is-invalid @enderror">
                        <option value="">Tanpa instruktur</option>
                        @foreach($ownerOptions as $id => $label)
                            <option value="{{ $id }}" @selected(old('owner_id', $module->owner_id) === $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('owner_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            @endif
            <div class="col-12">
                <label class="form-label">Judul Modul</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $module->title) }}" required>
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label class="form-label">Tautan Modul</label>
                <input type="url" name="link_url" class="form-control @error('link_url') is-invalid @enderror" value="{{ old('link_url', $module->link_url) }}" placeholder="https://drive.google.com/..." required>
                @error('link_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label class="form-label">Deskripsi (opsional)</label>
                <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $module->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" @checked(old('is_active', $module->is_active ?? true))>
                    <label class="form-check-label" for="isActive">Aktif</label>
                </div>
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
