@extends('layouts.participant')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 fade-up">
    <div>
        <h4 class="mb-1 section-title">Tugas/Kuis Anda</h4>
        <div class="section-subtitle">Pilih kelas untuk memfilter tugas yang relevan.</div>
    </div>
</div>

<div class="card stat-card card-hover">
    <div class="card-body">
        <div class="card-soft p-3 mb-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-sm-4 col-md-3">
                    <label class="form-label mb-1">Filter Kelas</label>
                    <select name="class_id" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        @foreach($classes as $id => $title)
                            <option value="{{ $id }}" @selected(request('class_id', $classId ?? null) === $id)>{{ $title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-outline-primary">Terapkan</button>
                </div>
                @if(request('class_id'))
                    <div class="col-auto">
                        <a href="{{ route('participant.assignments') }}" class="btn btn-sm btn-link text-decoration-none">Reset</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Judul</th>
                        <th>Kelas</th>
                        <th>Kategori</th>
                        <th>Tipe</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        @php($submission = $submissionMap->get($assignment->id))
                        <tr>
                            <td>{{ $assignments->firstItem() + $loop->index }}</td>
                            <td>{{ $assignment->title }}</td>
                            <td>{{ $assignment->course->title ?? '-' }}</td>
                            <td>
                                @php($typeLabel = $assignment->assessment_type ?? 'regular')
                                @switch($typeLabel)
                                    @case('module_quiz')
                                        <span class="badge bg-info text-dark">Quiz Akhir Bab</span>
                                        @break
                                    @case('final_exam')
                                        <span class="badge bg-danger">Ujian Final</span>
                                        @break
                                    @case('final_project')
                                        <span class="badge bg-success">Proyek Akhir</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">Reguler</span>
                                @endswitch
                            </td>
                            <td>
                                @if($assignment->type === 'quiz')
                                    @php($scopeLabel = ($assignment->quiz_scope ?? 'class') === 'selection' ? 'CBT Seleksi' : 'CBT Kelas')
                                    <span class="badge bg-warning text-dark">{{ $scopeLabel }}</span>
                                @else
                                    <span class="badge bg-info text-dark text-uppercase">{{ $assignment->type }}</span>
                                @endif
                            </td>
                            <td>{{ $assignment->due_at ? $assignment->due_at->format('d M Y H:i') : '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $submission?->status === 'graded' ? 'success' : ($submission ? 'secondary' : 'warning text-dark') }}">
                                    {{ $submission?->status === 'graded' ? 'Dinilai' : ($submission ? 'Terkirim' : 'Belum') }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('participant.assignments.show', $assignment) }}" class="btn btn-sm btn-primary">Buka</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada tugas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $assignments->links() }}
        </div>
    </div>
</div>
@endsection
