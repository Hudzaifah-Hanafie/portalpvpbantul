@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $pageTitle }}</h4>
        <small class="text-muted">Gunakan tautan share (Google Drive/OneDrive/dll) dan pastikan aksesnya publik atau sesuai kebutuhan.</small>
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
                <label class="form-label">Program Pelatihan</label>
                <select name="training_schedule_id" class="form-select @error('training_schedule_id') is-invalid @enderror" required>
                    <option value="">Pilih program</option>
                    @foreach($scheduleOptions as $id => $label)
                        <option value="{{ $id }}" @selected(old('training_schedule_id', $documentation->training_schedule_id) === $id)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('training_schedule_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Tanggal Dokumentasi (opsional)</label>
                <input type="date" name="documented_at" class="form-control @error('documented_at') is-invalid @enderror" value="{{ old('documented_at', $documentation->documented_at?->format('Y-m-d')) }}">
                @error('documented_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label class="form-label">Judul Dokumentasi</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $documentation->title) }}" required>
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label class="form-label">Tautan Dokumentasi</label>
                <input type="url" name="link_url" class="form-control @error('link_url') is-invalid @enderror" value="{{ old('link_url', $documentation->link_url) }}" placeholder="https://drive.google.com/..." required>
                @error('link_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label class="form-label">Deskripsi (opsional)</label>
                <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $documentation->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" @checked(old('is_active', $documentation->is_active ?? true))>
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
