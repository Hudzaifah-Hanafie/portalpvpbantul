@extends('layouts.participant')

@section('content')
<div class="container-fluid py-4">
    <div class="row gx-4">
        <!-- Sidebar Navigation (Learning Path Checklist) -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card shadow-sm sticky-top" style="top: 80px; z-index: 1000;">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h5 class="mb-0 fw-bold">Learning Path</h5>
                    <small class="text-light">{{ $class->title }}</small>
                </div>
                <div class="card-body p-0" style="max-height: 70vh; overflow-y: auto;">
                    <div class="accordion" id="learningPathAccordion">
                        @foreach($modules as $index => $mod)
                            <div class="accordion-item border-0 border-bottom">
                                <h2 class="accordion-header" id="heading-{{ $mod->id }}">
                                    <button class="accordion-button {{ $mod->id === $material->module_id ? '' : 'collapsed' }} fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $mod->id }}" aria-expanded="{{ $mod->id === $material->module_id ? 'true' : 'false' }}" aria-controls="collapse-{{ $mod->id }}">
                                        Modul {{ $index + 1 }}: {{ $mod->title }}
                                    </button>
                                </h2>
                                <div id="collapse-{{ $mod->id }}" class="accordion-collapse collapse {{ $mod->id === $material->module_id ? 'show' : '' }}" aria-labelledby="heading-{{ $mod->id }}" data-bs-parent="#learningPathAccordion">
                                    <div class="accordion-body p-0">
                                        <div class="list-group list-group-flush rounded-0">
                                            @foreach($mod->materials as $mat)
                                                @php
                                                    $isCurrent = $mat->id === $material->id;
                                                    $isDone = isset($allProgress[$mat->id]) && $allProgress[$mat->id]->is_completed;
                                                @endphp
                                                <a href="{{ route('participant.materials.show', [$class, $mat]) }}" class="list-group-item list-group-item-action {{ $isCurrent ? 'list-group-item-primary border-start border-primary border-4 fw-bold' : '' }}">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="text-truncate me-2" style="max-width: 80%; font-size: 0.9rem;">
                                                            {{ $mat->title }}
                                                        </span>
                                                        @if($isDone)
                                                            <i class="fas fa-check-circle text-success fs-5"></i>
                                                        @else
                                                            <i class="far fa-circle text-muted fs-5"></i>
                                                        @endif
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-lg-9 col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="{{ route('participant.class.show', $class) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Kelas
                </a>
                <a href="{{ route('participant.gamification.leaderboard') }}" class="btn btn-warning btn-sm fw-bold">
                    <i class="fas fa-trophy text-light me-1"></i> Leaderboard Poin
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-lg-5">
                    <h2 class="fw-bold mb-4">{{ $material->title }}</h2>
                    
                    <!-- Konten Materi -->
                    <div class="material-content fs-5" style="line-height: 1.8;">
                        @if($material->content)
                            {!! $material->content !!}
                        @else
                            <div class="text-muted fst-italic">Materi ini tidak memiliki konten teks panjang.</div>
                        @endif
                    </div>

                    @if($material->link_url)
                        <div class="mt-4 p-4 bg-light rounded shadow-sm d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1"><i class="fas fa-external-link-alt me-2 text-primary"></i> Lampiran / Link Eksternal</h5>
                                <p class="mb-0 text-muted small">Materi ini menyertakan tautan untuk dipelajari lebih lanjut.</p>
                            </div>
                            <a href="{{ $material->link_url }}" target="_blank" rel="noopener" class="btn btn-primary px-4">
                                Buka Link Tautan
                            </a>
                        </div>
                    @endif

                    <hr class="my-5">

                    <!-- Aksi & Gamifikasi -->
                    <div class="d-flex flex-column align-items-center text-center p-4 rounded bg-light border border-2 {{ $progress->is_completed ? 'border-success bg-soft-success' : 'border-primary bg-soft-primary' }}">
                        @if($progress->is_completed)
                            <div class="mb-3">
                                <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            </div>
                            <h4 class="text-success fw-bold">Materi Selesai!</h4>
                            <p class="text-muted">Anda berhasil menyelesaikan materi ini pada {{ $progress->completed_at->format('d M Y H:i') }}.</p>
                        @else
                            <div class="mb-3">
                                <i class="fas fa-award text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h4 class="fw-bold">Anda sudah paham?</h4>
                            <p class="text-muted">Tandai telah selesai dan dapatkan <strong class="text-warning">10 Poin Gamifikasi</strong>!</p>
                            <form action="{{ route('participant.materials.complete', $material) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm fw-bold rounded-pill">
                                    <i class="fas fa-check me-2"></i> Tandai Selesai
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bagian Diskusi (Forum per Modul/Materi) -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-comments text-primary me-2"></i> Diskusi Interaktif</h4>
                    <p class="text-muted small mb-0 mt-1">Sampaikan pertanyaan atau diskusikan materi ini bersama instruktur dan rekan lainnya. (+5 Poin saat bertanya)</p>
                </div>
                <div class="card-body">
                    <!-- Form Diskusi Utama -->
                    <form action="{{ route('participant.materials.discussion.store', $material) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="form-group mb-2">
                            <textarea name="message" class="form-control bg-light" rows="3" placeholder="Tulis sesuatu untuk memulai diskusi..." required></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Kirim Diskusi</button>
                        </div>
                    </form>

                    <hr>

                    <!-- List Diskusi -->
                    <div class="discussion-list mt-4">
                        @forelse($discussions as $disc)
                            <div class="d-flex mb-4 p-3 bg-light rounded shadow-sm">
                                <div class="me-3">
                                    <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center fw-bold" style="width: 45px; height: 45px;">
                                        {{ substr($disc->user->name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0 fw-bold">{{ $disc->user->name }}</h6>
                                        <small class="text-muted">{{ $disc->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-2">{{ $disc->message }}</p>
                                    
                                    <!-- Balasan -->
                                    <div class="mt-3 ps-4 border-start border-2 border-primary">
                                        @foreach($disc->replies as $reply)
                                            <div class="d-flex mb-3">
                                                <div class="me-2">
                                                    <div class="rounded-circle bg-info text-white d-flex justify-content-center align-items-center fw-bold" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                                        {{ substr($reply->user->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="mb-0 fw-bold small">{{ $reply->user->name }}</span>
                                                        <small class="text-muted" style="font-size: 0.75rem;">{{ $reply->created_at->diffForHumans() }}</small>
                                                    </div>
                                                    <p class="mb-0 small bg-white p-2 border rounded">{{ $reply->message }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                        
                                        <!-- Form Balas -->
                                        <form action="{{ route('participant.materials.discussion.store', $material) }}" method="POST" class="mt-2 text-end">
                                            @csrf
                                            <input type="hidden" name="parent_id" value="{{ $disc->id }}">
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="message" class="form-control" placeholder="Tulis balasan..." required>
                                                <button class="btn btn-outline-primary" type="submit">Balas</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-comment-dots fs-1 mb-3 text-light"></i>
                                <p>Belum ada diskusi untuk materi ini. Jadilah yang pertama!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
