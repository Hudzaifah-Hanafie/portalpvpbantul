@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0">{{ $session->exists ? 'Ubah' : 'Tambah' }} Sesi Wawancara</h3>
        <small class="text-muted">Atur jadwal, pewawancara, dan lokasi.</small>
    </div>
    <a href="{{ route('admin.interview-session.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="card border-0 shadow-sm col-lg-8">
    <div class="card-body">
        <form action="{{ $action }}" method="POST" class="row g-3">
            @csrf
            @if($method === 'PUT') @method('PUT') @endif
            <div class="col-md-6">
                <label class="form-label">Batch Pelatihan</label>
                <select name="training_schedule_id" class="form-select" required>
                    <option value="">Pilih batch</option>
                    @foreach($schedules as $id => $title)
                        <option value="{{ $id }}" @selected(old('training_schedule_id', $session->training_schedule_id) == $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Pewawancara</label>
                <select name="interviewer_id" class="form-select" required>
                    <option value="">Pilih pewawancara</option>
                    @foreach($interviewers as $id => $name)
                        <option value="{{ $id }}" @selected(old('interviewer_id', $session->interviewer_id) == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal</label>
                <input type="date" name="date" class="form-control" value="{{ old('date', optional($session->date)->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Mulai</label>
                <input type="time" name="start_time" class="form-control" value="{{ old('start_time', $session->start_time) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Selesai</label>
                <input type="time" name="end_time" class="form-control" value="{{ old('end_time', $session->end_time) }}" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Lokasi</label>
                <input type="text" name="location" class="form-control" value="{{ old('location', $session->location) }}" placeholder="Ruang A / Tautan Zoom" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Kuota per sesi</label>
                <input type="number" name="quota" class="form-control" value="{{ old('quota', $session->quota ?? 1) }}" min="1" max="500" required>
            </div>
            <div class="col-12">
                <button class="btn btn-primary">{{ $session->exists ? 'Perbarui' : 'Simpan' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
