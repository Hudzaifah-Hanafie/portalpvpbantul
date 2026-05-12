@extends('layouts.participant')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 fade-up">
    @php
        $formatLabel = [
            'sinkron' => 'Daring Sinkron',
            'asinkron' => 'Daring Asinkron',
            'blended' => 'Blended',
            'luring' => 'Luring',
        ][$class->format] ?? strtoupper($class->format ?? '-');
    @endphp
    <div>
        <h4 class="mb-1 section-title">{{ $class->title }}</h4>
        <div class="section-subtitle">{{ $class->instructor?->name ?? '-' }} • Format: {{ $formatLabel }}</div>
    </div>
    <a href="{{ route('participant.classes') }}" class="btn btn-outline-light border-0 shadow-sm bg-white text-dark btn-sm">Kembali</a>
</div>

<ul class="nav nav-tabs tab-pill mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-summary" type="button" role="tab">Ringkasan</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-materials" type="button" role="tab">Materi</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-assignments" type="button" role="tab">Tugas/Quiz</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sessions" type="button" role="tab">Sesi & Presensi</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-announcements" type="button" role="tab">Pengumuman</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-forum" type="button" role="tab">Forum</button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="tab-summary" role="tabpanel">
        <div class="card stat-card card-hover mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="fw-semibold mb-2">Deskripsi Kelas</div>
                        <div class="text-muted">{!! nl2br(e($class->description ?? 'Belum ada deskripsi kelas.')) !!}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="fw-semibold mb-2">Ringkasan</div>
                        <div class="card-soft p-3">
                            <div class="small text-muted">Tugas aktif: {{ $assignments->count() }}</div>
                            <div class="small text-muted">Sesi aktif: {{ $sessions->count() }}</div>
                            <div class="small text-muted">Kehadiran: {{ $attendanceRate !== null ? $attendanceRate . '%' : '-' }}</div>
                            <div class="small text-muted">Rata-rata nilai: {{ $averageScore !== null ? $averageScore : '-' }}</div>
                        </div>
                        <div class="mt-3 d-grid gap-2">
                            <a href="{{ route('participant.assignments', ['class_id' => $class->id]) }}" class="btn btn-outline-primary lms-cta">Lihat Tugas</a>
                            <a href="{{ route('participant.sessions.index') }}" class="btn btn-outline-secondary lms-cta">Presensi</a>
                        </div>
                        <div class="mt-3">
                            <div class="fw-semibold mb-2">Status Kelulusan</div>
                            <div class="card-soft p-3">
                                <div class="d-flex justify-content-between align-items-center small mb-1">
                                    <span>Kehadiran ≥ 80%</span>
                                    <span class="badge {{ $graduationEligible['attendance'] === null ? 'bg-secondary' : ($graduationEligible['attendance'] ? 'bg-success' : 'bg-danger') }}">
                                        {{ $graduationEligible['attendance'] === null ? 'Belum ada' : ($graduationEligible['attendance'] ? 'Lulus' : 'Belum') }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center small mb-1">
                                    <span>Rata-rata nilai ≥ 70</span>
                                    <span class="badge {{ $graduationEligible['score'] === null ? 'bg-secondary' : ($graduationEligible['score'] ? 'bg-success' : 'bg-danger') }}">
                                        {{ $graduationEligible['score'] === null ? 'Belum ada' : ($graduationEligible['score'] ? 'Lulus' : 'Belum') }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center small mb-1">
                                    <span>Proyek akhir</span>
                                    <span class="badge {{ $graduationEligible['final_project'] === null ? 'bg-secondary' : ($graduationEligible['final_project'] ? 'bg-success' : 'bg-danger') }}">
                                        {{ $graduationEligible['final_project'] === null ? 'Tidak ada' : ($graduationEligible['final_project'] ? 'Selesai' : 'Belum') }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center small">
                                    <span>Ujian final</span>
                                    <span class="badge {{ $graduationEligible['final_exam'] === null ? 'bg-secondary' : ($graduationEligible['final_exam'] ? 'bg-success' : 'bg-danger') }}">
                                        {{ $graduationEligible['final_exam'] === null ? 'Tidak ada' : ($graduationEligible['final_exam'] ? 'Selesai' : 'Belum') }}
                                    </span>
                                </div>
                                <div class="small text-muted mt-2">
                                    Rubrik dinilai: {{ $rubricCompleted }} / {{ $rubricAssignments->count() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if($competencyChecklist->isNotEmpty())
                    <div class="mt-4">
                        <div class="fw-semibold mb-2">Checklist Kompetensi</div>
                        <div class="row g-2">
                            @foreach($competencyChecklist as $competency)
                                <div class="col-md-6">
                                    <div class="card-soft p-2 d-flex align-items-center gap-2">
                                        <i class="fas fa-check-circle {{ $competency['done'] ? 'text-success' : 'text-muted' }}"></i>
                                        <span class="small">{{ $competency['label'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-muted small mt-2">Checklist mengikuti rubrik dan penilaian instruktur.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-materials" role="tabpanel">
        <div x-data="learningPathTracker()" class="card stat-card card-hover mb-3">
            <div class="card-body">
                <div class="row align-items-center mb-4">
                    <div class="col-md-7">
                        <div class="fw-semibold fs-5"><i class="fas fa-route text-primary me-2"></i>Learning Path & Modul Checklist</div>
                        <div class="small text-muted mt-1">Selesaikan materi untuk mendapatkan XP dan Badge Kelulusan.</div>
                    </div>
                    <div class="col-md-5 text-md-end mt-3 mt-md-0">
                        <div class="d-inline-flex align-items-center gap-3 bg-light rounded-pill px-4 py-2 border shadow-sm">
                            <div class="text-start">
                                <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">Total XP</div>
                                <div class="fw-bold fs-5 text-primary" x-text="xp + ' XP'">0 XP</div>
                            </div>
                            <div class="fs-2 text-warning">
                                <i class="fas" :class="points >= 100 ? 'fa-trophy' : 'fa-medal'"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="progress mb-4" style="height: 12px; border-radius: 20px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" :style="'width: ' + progress + '%'"></div>
                </div>

                <div class="accordion" id="moduleAccordion">
                    @forelse($modules as $module)
                        <div class="accordion-item border mb-3 rounded shadow-sm overflow-hidden" 
                             :class="{ 'border-success': isModuleComplete([{{ $module->materials->pluck('id')->implode(',') }}]) }">
                            <h2 class="accordion-header" id="heading-{{ $module->id }}">
                                <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $module->id }}" aria-expanded="false" aria-controls="collapse-{{ $module->id }}">
                                    <i class="fas fa-book-open me-2 text-secondary" :class="{ 'text-success': isModuleComplete([{{ $module->materials->pluck('id')->implode(',') }}]) }"></i> 
                                    {{ $module->title }}
                                </button>
                            </h2>
                            <div id="collapse-{{ $module->id }}" class="accordion-collapse collapse show" aria-labelledby="heading-{{ $module->id }}" data-bs-parent="#moduleAccordion">
                                <div class="accordion-body bg-light p-0">
                                    <ul class="list-group list-group-flush">
                                        @forelse($module->materials as $material)
                                            <li class="list-group-item d-flex justify-content-between align-items-center p-3 transition-all"
                                                :class="{ 'bg-success bg-opacity-10': completed.includes({{ $material->id }}) }">
                                                <div class="d-flex align-items-start gap-3">
                                                    <button @click="toggleMaterial({{ $material->id }})" 
                                                            class="btn btn-sm btn-outline-success rounded-circle mt-1 shadow-sm"
                                                            :class="{ 'active': completed.includes({{ $material->id }}) }">
                                                        <i class="fas fa-check" x-show="completed.includes({{ $material->id }})"></i>
                                                    </button>
                                                    <div>
                                                        <div class="fw-semibold text-dark" :class="{'text-decoration-line-through text-muted': completed.includes({{ $material->id }}) }">{{ $material->title }}</div>
                                                        @if($material->content)
                                                            <div class="text-muted small text-truncate" style="max-width: 300px;">{!! strip_tags($material->content) !!}</div>
                                                        @endif
                                                        <div class="mt-1 d-flex gap-2">
                                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle"><i class="fas fa-coins me-1"></i>+10 XP</span>
                                                            <a href="{{ route('participant.materials.show', [$class, $material]) }}" class="text-decoration-none small text-primary"><i class="fas fa-external-link-alt me-1"></i>Buka Materi</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-muted">Belum ada materi.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">Belum ada bab pembelajaran.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('learningPathTracker', () => ({
                    completed: @json($completedMaterialIds),
                    totalMaterials: {{ $modules->sum(fn($m) => $m->materials->count()) }},
                    
                    get xp() {
                        return this.completed.length * 10;
                    },
                    get progress() {
                        return this.totalMaterials === 0 ? 0 : Math.round((this.completed.length / this.totalMaterials) * 100);
                    },
                    isModuleComplete(materialIds) {
                        if(materialIds.length === 0) return false;
                        return materialIds.every(id => this.completed.includes(id));
                    },
                    toggleMaterial(id) {
                        if (this.completed.includes(id)) {
                            return; // Sudah selesai
                        } 
                        this.completed.push(id); // Optimistic UI update

                        fetch(`/my/materials/${id}/complete`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        }).then(res => res.json()).then(data => {
                            if(!data.success) {
                                this.completed = this.completed.filter(i => i !== id);
                            }
                        }).catch(() => {
                            this.completed = this.completed.filter(i => i !== id);
                        });
                    }
                }))
            })
        </script>
    </div>

    <div class="tab-pane fade" id="tab-assignments" role="tabpanel">
        <div class="card stat-card card-hover">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="fw-semibold">Tugas & Quiz</div>
                        <div class="small text-muted">Pantau status pengerjaan tugas di kelas ini.</div>
                    </div>
                    <a href="{{ route('participant.assignments', ['class_id' => $class->id]) }}" class="btn btn-outline-primary lms-cta">Buka Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Judul</th>
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
                                    <td>{{ $assignment->title }}</td>
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
                                    <td>{{ $assignment->due_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $submission?->status === 'graded' ? 'success' : ($submission ? 'secondary' : 'warning text-dark') }}">
                                            {{ $submission?->status === 'graded' ? 'Dinilai' : ($submission ? 'Terkirim' : 'Belum') }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('participant.assignments.show', $assignment) }}" class="btn btn-primary lms-cta">Buka</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-muted text-center py-4">Belum ada tugas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-sessions" role="tabpanel">
        <div class="card stat-card card-hover">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="fw-semibold">Sesi & Presensi</div>
                        <div class="small text-muted">Pastikan presensi diisi saat sesi dibuka.</div>
                    </div>
                    <a href="{{ route('participant.sessions.index') }}" class="btn btn-outline-secondary lms-cta">Presensi</a>
                </div>
                        <div class="list-group list-group-flush">
                            @forelse($sessions as $session)
                                @php
                                    $attendance = $attendanceMap->get($session->id);
                                    $expired = $session->attendance_code_expires_at && $session->attendance_code_expires_at->isPast();
                                @endphp
                                <div class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div>
                                        <div class="fw-semibold">{{ $session->title ?? 'Sesi' }}</div>
                                        <div class="small text-muted">{{ $session->start_at?->format('d M Y H:i') ?? '-' }} @if($session->end_at) - {{ $session->end_at->format('H:i') }} @endif</div>
                                        @if($session->attendance_code_expires_at)
                                            <div class="small text-muted">
                                                Presensi dibuka sampai {{ $session->attendance_code_expires_at->format('d M Y H:i') }}
                                                <span class="ms-1 badge bg-light text-dark border attendance-countdown" data-expire="{{ $session->attendance_code_expires_at->toIso8601String() }}">--:--</span>
                                            </div>
                                        @endif
                                        @if($session->meeting_link)
                                            <div class="small text-muted mt-1">
                                                <i class="fas fa-video me-1"></i> Link tatap muka tersedia
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        @if($attendance)
                                            <span class="badge bg-success">Sudah Presensi</span>
                                        @elseif($expired)
                                            <span class="badge bg-secondary">Presensi Ditutup</span>
                                        @else
                                            <a href="{{ route('participant.sessions.attendance.form', $session) }}" class="btn btn-primary lms-cta">Isi Presensi</a>
                                        @endif
                                        @if($session->meeting_link)
                                            <div class="mt-2">
                                                <a href="{{ $session->meeting_link }}" class="btn btn-outline-primary lms-cta" target="_blank" rel="noopener">Buka Meet/Zoom</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                        <div class="text-muted">Belum ada sesi terjadwal.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-announcements" role="tabpanel">
        <div class="card stat-card card-hover">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="fw-semibold">Pengumuman</div>
                        <div class="small text-muted">Informasi terbaru dari kelas.</div>
                    </div>
                    <a href="{{ route('participant.class.announcements.index', $class) }}" class="btn btn-outline-secondary lms-cta">Lihat Semua</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($announcements as $announcement)
                        <a href="{{ route('participant.class.announcements.show', [$class, $announcement]) }}" class="list-group-item list-group-item-action">
                            <div class="fw-semibold">{{ $announcement->title }}</div>
                            <div class="small text-muted">{{ $announcement->published_at?->format('d M Y H:i') ?? $announcement->created_at?->format('d M Y H:i') }}</div>
                        </a>
                    @empty
                        <div class="text-muted">Belum ada pengumuman.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-forum" role="tabpanel">
        <div class="card stat-card card-hover">
            <div class="card-body">
                <div class="fw-semibold mb-2">Forum Diskusi</div>
                <div class="text-muted small mb-3">Gunakan forum untuk bertanya atau berdiskusi dengan instruktur dan peserta lain.</div>
                <a href="{{ route('participant.class.forum.index', $class) }}" class="btn btn-primary lms-cta">Masuk Forum Kelas</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const timers = document.querySelectorAll('.attendance-countdown');
        if (!timers.length) return;

        function formatRemaining(ms) {
            if (ms <= 0) return 'Berakhir';
            const totalSeconds = Math.floor(ms / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            if (hours > 0) {
                return `${hours}j ${minutes}m`;
            }
            return `${minutes}m ${seconds}s`;
        }

        function tick() {
            timers.forEach((el) => {
                const expire = new Date(el.dataset.expire);
                const diff = expire - new Date();
                el.textContent = formatRemaining(diff);
            });
        }

        tick();
        setInterval(tick, 1000);
    })();
</script>
@endpush
