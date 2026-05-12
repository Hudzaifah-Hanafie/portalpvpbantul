@extends('layouts.admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-0">Gradebook Ringkas</h4>
        <small class="text-muted">Ringkasan nilai peserta per kelas (teori, praktik, sikap, dan kelulusan).</small>
    </div>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1">Pilih Kelas</label>
                <select name="class_id" class="form-select form-select-sm" required>
                    <option value="">Pilih kelas</option>
                    @foreach($classes as $id => $title)
                        <option value="{{ $id }}" @selected($classFilter == $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">Lihat</button>
                @if($selectedClass)
                    <a href="{{ route(request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-gradebook.export-excel' : 'admin.course-gradebook.export-excel', ['class_id' => $classFilter]) }}" class="btn btn-sm btn-success ms-2">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($selectedClass)
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Peserta</div>
                    <div class="fs-4 fw-bold">{{ $summary['participants'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Rata-rata Teori</div>
                    <div class="fs-4 fw-bold">{{ isset($summary['post_avg']) ? number_format($summary['post_avg'], 1) : '-' }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Rata-rata Praktik</div>
                    <div class="fs-4 fw-bold">{{ isset($summary['practice_avg']) ? number_format($summary['practice_avg'], 1) : '-' }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Rata-rata Kehadiran</div>
                    <div class="fs-4 fw-bold">{{ isset($summary['attendance_avg']) ? number_format($summary['attendance_avg'], 1) . '%' : '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Peserta</th>
                            <th>Pre-Test</th>
                            <th>Post-Test</th>
                            <th>Praktik</th>
                            <th>Sikap</th>
                            <th>Kehadiran</th>
                            <th>Nilai Akhir</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $row['user']?->name ?? '-' }}</td>
                                <td>{{ $row['pre_test_score'] !== null ? number_format($row['pre_test_score'], 1) : '-' }}</td>
                                <td>{{ $row['post_test_score'] !== null ? number_format($row['post_test_score'], 1) : '-' }}</td>
                                <td>
                                    <input type="number" min="0" max="100" step="0.1"
                                        class="form-control form-control-sm grade-input"
                                        data-id="{{ $row['id'] }}" data-field="practice_score"
                                        value="{{ $row['practice_score'] !== null ? round($row['practice_score'], 1) : '' }}"
                                        placeholder="0 - 100" style="width: 80px; min-width: 80px;">
                                </td>
                                <td>
                                    <input type="number" min="0" max="100" step="0.1"
                                        class="form-control form-control-sm grade-input"
                                        data-id="{{ $row['id'] }}" data-field="attitude_score"
                                        value="{{ $row['attitude_score'] !== null ? round($row['attitude_score'], 1) : '' }}"
                                        placeholder="0 - 100" style="width: 80px; min-width: 80px;">
                                </td>
                                <td>{{ $row['attendance_rate'] !== null ? number_format($row['attendance_rate'], 1) . '%' : '-' }}</td>
                                <td><span id="final-grade-{{ $row['id'] }}" class="fw-bold">{{ $row['final_grade'] !== null ? number_format($row['final_grade'], 1) : '-' }}</span></td>
                                <td id="status-col-{{ $row['id'] }}">
                                    @php
                                        $status = $row['competency_status'] ?? 'pending';
                                        $badge = [
                                            'competent' => 'bg-success',
                                            'not_competent' => 'bg-danger',
                                            'pending' => 'bg-secondary',
                                        ][$status] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge {{ $badge }} status-badge">
                                        {{ $status === 'competent' ? 'Kompeten' : ($status === 'not_competent' ? 'Belum Kompeten' : 'Pending') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada data nilai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="small text-muted mt-2">
                <i class="fas fa-info-circle"></i> Angka yang diubah di kolom Praktik dan Sikap akan tersimpan otomatis.
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.grade-input');
    const updateUrlPattern = "{{ request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? route('instructor.lms.course-gradebook.inline-update', ':id') : route('admin.course-gradebook.inline-update', ':id') }}";
    
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            const id = this.dataset.id;
            const field = this.dataset.field;
            const value = this.value;
            
            this.classList.remove('is-invalid', 'is-valid');
            this.classList.add('bg-warning', 'bg-opacity-10'); // saving indicator
            
            fetch(updateUrlPattern.replace(':id', id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ field: field, value: value })
            })
            .then(response => response.json())
            .then(data => {
                this.classList.remove('bg-warning', 'bg-opacity-10');
                if (data.success) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                    setTimeout(() => this.classList.remove('is-valid'), 2000);
                    
                    // Update final grade and status
                    if (data.new_final_grade !== null) {
                        document.getElementById('final-grade-' + id).innerText = Number(data.new_final_grade).toFixed(1);
                    } else {
                        document.getElementById('final-grade-' + id).innerText = '-';
                    }
                    
                    const statusCol = document.getElementById('status-col-' + id);
                    let badgeClass = 'bg-secondary';
                    let badgeText = 'Pending';
                    
                    if (data.new_status === 'competent') {
                        badgeClass = 'bg-success';
                        badgeText = 'Kompeten';
                    } else if (data.new_status === 'not_competent') {
                        badgeClass = 'bg-danger';
                        badgeText = 'Belum Kompeten';
                    }
                    
                    statusCol.innerHTML = '<span class="badge ' + badgeClass + ' status-badge">' + badgeText + '</span>';
                } else {
                    this.classList.add('is-invalid');
                    console.error('Save failed', data);
                }
            })
            .catch(error => {
                this.classList.remove('bg-warning', 'bg-opacity-10');
                this.classList.add('is-invalid');
                console.error('Error:', error);
            });
        });
    });
});
</script>
@endpush
@endsection
