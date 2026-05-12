@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Dashboard Instruktur</h4>
        <small class="text-muted">Buat sesi, tautan meet/zoom, dan pengumuman kelas dengan cepat.</small>
    </div>
    <a href="{{ route('instructor.lms.course-class.index') }}" class="btn btn-outline-secondary btn-sm">Kelola Kelas</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap gap-2">
                <a href="{{ route('profile.show') }}" class="btn btn-light border"><i class="fas fa-user-circle me-1"></i> Profil Saya</a>
                <a href="{{ route('instructor.lms.course-class.index') }}" class="btn btn-light border"><i class="fas fa-graduation-cap me-1"></i> Kelas</a>
                <a href="{{ route('instructor.lms.course-session.index') }}" class="btn btn-light border"><i class="fas fa-video me-1"></i> Sesi</a>
                <a href="{{ route('instructor.lms.course-module.index') }}" class="btn btn-light border"><i class="fas fa-layer-group me-1"></i> Bab</a>
                <a href="{{ route('instructor.lms.course-material.index') }}" class="btn btn-light border"><i class="fas fa-book-open me-1"></i> Materi</a>
                <a href="{{ route('instructor.lms.course-assignment.index') }}" class="btn btn-light border"><i class="fas fa-tasks me-1"></i> Tugas/Quiz</a>
                <a href="{{ route('instructor.lms.course-submission.index') }}" class="btn btn-light border"><i class="fas fa-file-signature me-1"></i> Penilaian</a>
                <a href="{{ route('instructor.lms.course-gradebook.index') }}" class="btn btn-light border"><i class="fas fa-table me-1"></i> Gradebook</a>
                <a href="{{ route('instructor.lms.course-attendance.index') }}" class="btn btn-light border"><i class="fas fa-user-check me-1"></i> Presensi</a>
                <a href="{{ route('instructor.lms.course-announcement.index') }}" class="btn btn-light border"><i class="fas fa-bullhorn me-1"></i> Pengumuman</a>
                <a href="{{ route('instructor.lms.course-progress.index') }}" class="btn btn-light border"><i class="fas fa-chart-line me-1"></i> Progress</a>
                <a href="{{ route('instructor.lms.course-forum-reports.index') }}" class="btn btn-light border"><i class="fas fa-flag me-1"></i> Moderasi Forum</a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Buat Sesi Baru</h6>
                <form action="{{ route('instructor.sessions.store') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">Kelas</label>
                        <select name="course_class_id" class="form-select @error('course_class_id') is-invalid @enderror" required>
                            <option value="">Pilih kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('course_class_id') === $class->id)>{{ $class->title }}</option>
                            @endforeach
                        </select>
                        @error('course_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Judul Sesi</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mulai</label>
                        <input type="datetime-local" name="start_at" class="form-control @error('start_at') is-invalid @enderror" value="{{ old('start_at') }}" required>
                        @error('start_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Selesai</label>
                        <input type="datetime-local" name="end_at" class="form-control @error('end_at') is-invalid @enderror" value="{{ old('end_at') }}">
                        @error('end_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Link Meet/Zoom (opsional)</label>
                        <input type="url" name="meeting_link" class="form-control @error('meeting_link') is-invalid @enderror" value="{{ old('meeting_link') }}" placeholder="https://meet.google.com/...">
                        @error('meeting_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Batas Presensi (opsional)</label>
                        <input type="datetime-local" name="attendance_code_expires_at" class="form-control @error('attendance_code_expires_at') is-invalid @enderror" value="{{ old('attendance_code_expires_at') }}">
                        @error('attendance_code_expires_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 text-end">
                        <button class="btn btn-primary">Buat Sesi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Pengumuman Cepat</h6>
                <form action="{{ route('instructor.announcements.store') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">Kelas</label>
                        <select name="course_class_id" class="form-select @error('course_class_id') is-invalid @enderror" required>
                            <option value="">Pilih kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('course_class_id') === $class->id)>{{ $class->title }}</option>
                            @endforeach
                        </select>
                        @error('course_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Judul</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Isi Pengumuman</label>
                        <textarea name="body" rows="4" class="form-control @error('body') is-invalid @enderror" required>{{ old('body') }}</textarea>
                        @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 text-end">
                        <button class="btn btn-primary">Kirim Pengumuman</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Sesi Terdekat</h6>
                @forelse($upcomingSessions as $session)
                    <div class="border rounded p-2 mb-2">
                        <div class="fw-semibold">{{ $session->course->title ?? '-' }}</div>
                        <div class="text-muted small">{{ $session->title ?? 'Sesi' }}</div>
                        <div class="text-muted small">{{ $session->start_at?->format('d M Y H:i') ?? '-' }}</div>
                        @if($session->meeting_link)
                            <a href="{{ $session->meeting_link }}" target="_blank" rel="noopener" class="small">Buka Meet/Zoom</a>
                        @endif
                    </div>
                @empty
                    <div class="text-muted small">Belum ada sesi terjadwal.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Pengumuman Terakhir</h6>
                @forelse($recentAnnouncements as $announcement)
                    <div class="border rounded p-2 mb-2">
                        <div class="fw-semibold">{{ $announcement->title }}</div>
                        <div class="text-muted small">{{ $announcement->course->title ?? '-' }} • {{ $announcement->published_at?->format('d M Y H:i') ?? '-' }}</div>
                    </div>
                @empty
                    <div class="text-muted small">Belum ada pengumuman terbaru.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
