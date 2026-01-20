@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Tambah Alumni</h3>
    <a href="{{ route('admin.alumni.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('admin.alumni.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                    @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jurusan / Program</label>
                    <input type="text" name="field_of_study" value="{{ old('field_of_study') }}" class="form-control">
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="form-label">Tahun Lulus</label>
                    <input type="number" name="graduation_year" value="{{ old('graduation_year') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status Pekerjaan</label>
                    <input type="text" name="employment_status" value="{{ old('employment_status') }}" class="form-control">
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Catatan</label>
                <textarea name="notes" rows="4" class="form-control">{{ old('notes') }}</textarea>
            </div>
            <div class="form-check form-switch mt-3">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                <label class="form-check-label" for="isActive">Aktif</label>
            </div>
            <div class="text-end mt-4">
                <button class="btn btn-primary">Simpan Alumni</button>
            </div>
        </form>
    </div>
</div>
@endsection