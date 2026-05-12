@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Impor Pendaftaran</h4>
        <small class="text-muted">Unggah CSV dengan kolom pertama: email peserta.</small>
    </div>
    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-enrollment.index' : 'admin.course-enrollment.index')) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        @if(session('import_errors'))
            <div class="alert alert-warning">
                <strong>Beberapa baris dilewati:</strong>
                <ul class="mb-0">
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-enrollment.import.store' : 'admin.course-enrollment.import.store')) }}" method="POST" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label class="form-label">Kelas</label>
                <select name="course_class_id" class="form-select @error('course_class_id') is-invalid @enderror" required>
                    <option value="">Pilih Kelas</option>
                    @foreach($classes as $id => $title)
                        <option value="{{ $id }}" @selected(old('course_class_id') == $id)>{{ $title }}</option>
                    @endforeach
                </select>
                @error('course_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">File CSV</label>
                <input type="file" name="csv_file" class="form-control @error('csv_file') is-invalid @enderror" accept=".csv,text/csv" required>
                <small class="text-muted">Max 2MB, kolom pertama harus email peserta.</small>
                @error('csv_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="text-end">
                <button class="btn btn-primary px-4">Impor</button>
            </div>
        </form>
    </div>
</div>
@endsection
