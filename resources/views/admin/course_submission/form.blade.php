@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Nilai Submission</h4>
        <small class="text-muted">Perbarui status dan nilai submission peserta.</small>
    </div>
    <a href="{{ request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? route('instructor.lms.course-submission.index') : route('admin.course-submission.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ $action }}" method="POST" class="row g-3">
            @csrf
            @method($method)

            <div class="col-md-6">
                <label class="form-label">Peserta</label>
                <input type="text" class="form-control" value="{{ $submission->user->name ?? $submission->user_id }}" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Kelas / Tugas</label>
                <input type="text" class="form-control" value="{{ $submission->assignment->course->title ?? '-' }} - {{ $submission->assignment->title ?? '-' }}" disabled>
            </div>

            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $submission->status) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Waktu Pengumpulan</label>
                <input type="text" class="form-control" value="{{ $submission->submitted_at ? $submission->submitted_at->format('d M Y H:i') : '-' }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nilai Saat Ini</label>
                <input type="text" class="form-control" value="{{ $submission->total_score ?? '-' }}" disabled>
            </div>

            @php $rubric = $submission->assignment->rubric ?? null; @endphp

            @if($rubric)
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">Rubrik Penilaian</label>
                        <small class="text-muted">Nilai total dihitung otomatis.</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Kriteria</th>
                                    <th>Bobot</th>
                                    <th>Skor Maks</th>
                                    <th>Skor</th>
                                    <th>Komentar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rubric as $idx => $crit)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $crit['criterion'] ?? 'Kriteria' }}</div>
                                            <div class="small text-muted">{{ $crit['description'] ?? '' }}</div>
                                        </td>
                                        <td class="small">{{ $crit['weight'] ?? 0 }}%</td>
                                        <td class="small">{{ $crit['max_score'] ?? ($submission->assignment->max_score ?? 100) }}</td>
                                        <td style="width:120px">
                                            <input type="number" step="0.01" name="rubric_scores[{{ $idx }}][score]" class="form-control form-control-sm" value="{{ old("rubric_scores.$idx.score", $submission->scores[$idx]['score'] ?? null) }}">
                                        </td>
                                        <td>
                                            <input type="text" name="rubric_scores[{{ $idx }}][comment]" class="form-control form-control-sm" value="{{ old("rubric_scores.$idx.comment", $submission->scores[$idx]['comment'] ?? '') }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="col-md-4">
                    <label class="form-label">Nilai Total</label>
                    <input type="number" name="total_score" class="form-control @error('total_score') is-invalid @enderror" value="{{ old('total_score', $submission->total_score) }}" min="0" max="1000">
                    @error('total_score') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Scores per Kriteria (opsional)</label>
                    <textarea name="scores" rows="3" class="form-control @error('scores') is-invalid @enderror" placeholder='JSON atau "kriteria: nilai" per baris'>{{ old('scores', $submission->scores ? json_encode($submission->scores) : '') }}</textarea>
                    @error('scores') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            @endif

            <div class="col-12">
                <label class="form-label">Feedback</label>
                <textarea name="feedback" rows="4" class="form-control @error('feedback') is-invalid @enderror">{{ old('feedback', $submission->feedback) }}</textarea>
                @error('feedback') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="text-end">
                <button class="btn btn-primary px-4">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0 mt-3">
    <div class="card-body">
        <h6 class="mb-3">Riwayat Grading</h6>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Versi</th>
                        <th>Total</th>
                        <th>Grader</th>
                        <th>Waktu</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submission->grades as $grade)
                        <tr>
                            <td>#{{ $grade->version }}</td>
                            <td>{{ $grade->total_score ?? '-' }}</td>
                            <td>{{ $grade->grader->name ?? '-' }}</td>
                            <td class="small">{{ $grade->graded_at?->format('d M Y H:i') ?? $grade->created_at->format('d M Y H:i') }}</td>
                            <td class="small">{{ \Illuminate\Support\Str::limit($grade->feedback, 80) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted text-center">Belum ada riwayat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
