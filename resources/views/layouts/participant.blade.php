<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Peserta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Alpine.js for interactive SPA-like UI components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --brand-ink: #0b1220;
            --brand-accent: #0b5ed7;
            --brand-accent-2: #22a6f2;
            --brand-soft: #eef4ff;
            --brand-warm: #ffe9c7;
        }
        body { background: #f4f7fb; color: var(--brand-ink); }
        .participant-nav {
            background: linear-gradient(120deg, #0b3d91 0%, #0b5ed7 60%, #1aa3e6 100%);
            color: #fff;
            box-shadow: 0 10px 30px rgba(11, 62, 145, 0.25);
            position: sticky;
            top: 0;
            z-index: 1020;
        }
        .participant-nav a { color: #fff; text-decoration: none; }
        .nav-pill {
            padding: 6px 12px;
            border-radius: 999px;
            transition: all 0.2s ease;
        }
        .nav-pill:hover { background: rgba(255,255,255,0.2); }
        .nav-pill.active {
            background: rgba(255,255,255,0.25);
            color: #fff;
        }
        .participant-card { border: none; }
        .section-title {
            font-weight: 700;
            letter-spacing: 0.2px;
        }
        .section-subtitle { color: #667085; }
        .stat-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        }
        .card-soft {
            background: var(--brand-soft);
            border-radius: 16px;
            border: 1px solid #e3ecff;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.12);
        }
        .fade-up {
            animation: fadeUp 0.5s ease both;
        }
        .delay-1 { animation-delay: 0.05s; }
        .delay-2 { animation-delay: 0.1s; }
        .delay-3 { animation-delay: 0.15s; }
        .delay-4 { animation-delay: 0.2s; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .badge-outline {
            border: 1px solid #d0d5dd;
            background: #fff;
            color: #344054;
        }
        .progress {
            background: #e9edf5;
        }
        .tab-pill .nav-link {
            border-radius: 999px;
            border: 1px solid #e4e7ec;
            margin-right: 8px;
            color: #344054;
        }
        .tab-pill .nav-link.active {
            background: var(--brand-accent);
            border-color: var(--brand-accent);
            color: #fff;
        }
        .alert-modern {
            border-radius: 14px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        }
        .alert-modern.alert-info {
            background: #eef4ff;
            color: #1e3a8a;
            border-color: #dbe7ff;
        }
        .alert-modern.alert-success {
            background: #ecfdf3;
            color: #05603a;
            border-color: #c7f2d7;
        }
        .alert-modern.alert-warning {
            background: #fff7ed;
            color: #7a2e0e;
            border-color: #ffd7b0;
        }
        .alert-modern.alert-danger {
            background: #fef2f2;
            color: #7f1d1d;
            border-color: #fecaca;
        }
        .alert-modern.alert-secondary {
            background: #f8fafc;
            color: #0f172a;
            border-color: #e2e8f0;
        }
        .skip-link { position: absolute; left: -999px; top: -999px; background:#fff; color:#0b5ed7; padding:8px 12px; z-index:1000; }
        .skip-link:focus { left: 8px; top: 8px; outline: 2px solid #0b5ed7; }
        .lms-cta {
            padding: 0.6rem 1.1rem;
            border-radius: 12px;
            font-weight: 600;
        }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $participantUser = auth()->user();
        $hasLearningAccess = $participantUser?->enrollments()
            ->whereIn('status', ['active', 'approved', 'completed'])
            ->exists();
        $hasPendingEnrollment = $participantUser?->enrollments()
            ->where('status', 'pending')
            ->exists();
    @endphp
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    <nav class="participant-nav py-3 mb-4" role="navigation" aria-label="Navigasi peserta">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-graduation-cap fa-lg"></i>
                <div>
                    <div class="fw-bold">Portal Peserta</div>
                    <small class="text-white-50">Kelas & Tugas</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('participant.dashboard') }}" class="fw-semibold nav-pill {{ request()->routeIs('participant.dashboard') ? 'active' : '' }}">Dashboard</a>
                @if($hasLearningAccess)
                    <a href="{{ route('participant.classes') }}" class="fw-semibold nav-pill {{ request()->routeIs('participant.classes') || request()->routeIs('participant.class.show') ? 'active' : '' }}">Kelas Saya</a>
                    <a href="{{ route('participant.assignments') }}" class="fw-semibold nav-pill {{ request()->routeIs('participant.assignments*') ? 'active' : '' }}">Tugas/Quiz</a>
                    <a href="{{ route('participant.sessions.index') }}" class="fw-semibold nav-pill {{ request()->routeIs('participant.sessions.index') ? 'active' : '' }}">Presensi</a>
                    <a href="{{ route('participant.progress') }}" class="fw-semibold nav-pill {{ request()->routeIs('participant.progress') ? 'active' : '' }}">Progres</a>
                    <a href="{{ route('participant.gamification.leaderboard') }}" class="fw-semibold nav-pill {{ request()->routeIs('participant.gamification.leaderboard') ? 'active' : '' }}"><i class="fas fa-trophy me-1 text-warning"></i>Leaderboard</a>
                @endif
                <a href="{{ route('participant.applications') }}" class="fw-semibold nav-pill {{ request()->routeIs('participant.applications') ? 'active' : '' }}">Pendaftaran Saya</a>
                <a href="{{ route('profile.show') }}" class="fw-semibold nav-pill {{ request()->routeIs('profile.show') ? 'active' : '' }}">Profil</a>
                <form action="{{ route('logout') }}" method="POST" class="mb-0">
                    @csrf
                    <button class="btn btn-sm btn-outline-light">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    @if(session('impersonator_id'))
        <div class="alert alert-warning alert-modern rounded-0 mb-0">
            <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    Mode impersonasi: Anda sebagai <strong>{{ auth()->user()->name ?? 'peserta' }}</strong>.
                    <span class="text-muted">Akun asli: {{ session('impersonator_name') }}.</span>
                </div>
                <form action="{{ route('impersonate.stop') }}" method="POST" class="m-0">
                    @csrf
                    <button class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-rotate-left"></i> Kembali ke akun asli
                    </button>
                </form>
            </div>
        </div>
    @endif

    <div class="container mb-5">
        <main id="main-content" class="mt-3">
            @if(session('consent_required'))
                <div class="alert alert-warning alert-modern d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <strong>Privasi & Rekaman:</strong> Dengan mengikuti kelas, Anda setuju pada tata tertib, kemungkinan dokumentasi foto/video untuk keperluan laporan, dan perlindungan data sesuai kebijakan.
                    </div>
                    <form action="{{ route('participant.consent') }}" method="POST" class="ms-3">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ session('consent_class') }}">
                        <button class="btn btn-sm btn-primary">Saya Mengerti</button>
                    </form>
                </div>
            @endif
            @php
                $selectionAlerts = auth()->user()?->enrollments()
                    ->with(['course', 'trainingSchedule'])
                    ->whereIn('status', ['approved', 'rejected'])
                    ->orderByDesc('updated_at')
                    ->take(3)
                    ->get();
                $waitingAlerts = auth()->user()?->enrollments()
                    ->with(['course', 'trainingSchedule'])
                    ->where('status', 'pending')
                    ->where('admin_status', 'verified')
                    ->orderByDesc('updated_at')
                    ->take(2)
                    ->get();
            @endphp
            @if(($selectionAlerts && $selectionAlerts->isNotEmpty()) || ($waitingAlerts && $waitingAlerts->isNotEmpty()))
                <div class="mb-3">
                    @foreach($selectionAlerts ?? [] as $alert)
                        @if($alert->status === 'approved')
                            <div class="alert alert-success alert-modern">
                                <div class="fw-bold mb-1">SELAMAT! ANDA DITERIMA</div>
                                <div class="small mb-1">Kelas: {{ $alert->course->title ?? '-' }}</div>
                                <div class="small text-muted">Skor akhir: {{ $alert->final_score !== null ? number_format($alert->final_score, 2) : '-' }}</div>
                                @php
                                    $registrationUrl = $alert->trainingSchedule?->pendaftaran_link
                                        ?? ($alert->trainingSchedule?->external_id ? "https://skillhub.kemnaker.go.id/pelatihan/{$alert->trainingSchedule->external_id}/daftar" : null)
                                        ?? 'https://skillhub.kemnaker.go.id/app/pelatihan';
                                @endphp
                                <div class="mt-2">
                                    @if($alert->coupon_code)
                                        <div class="small mb-2">Kupon SIAP Kerja: <span class="fw-semibold">{{ $alert->coupon_code }}</span></div>
                                        <a href="{{ $registrationUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success">
                                            Gunakan Kupon di SIAP Kerja
                                        </a>
                                    @else
                                        <div class="small text-muted">Kupon akan diberikan setelah kelulusan diproses.</div>
                                    @endif
                                </div>
                            </div>
                        @elseif($alert->status === 'rejected')
                            <div class="alert alert-danger alert-modern">
                                <div class="fw-bold mb-1">MOHON MAAF, ANDA BELUM BERHASIL</div>
                                <div class="small mb-1">Kelas: {{ $alert->course->title ?? '-' }}</div>
                                <div class="small mb-0 text-muted">Terima kasih sudah mendaftar. Anda bisa mencoba batch berikutnya.</div>
                            </div>
                        @endif
                    @endforeach
                    @foreach($waitingAlerts ?? [] as $alert)
                        <div class="alert alert-warning alert-modern">
                            <div class="fw-bold mb-1">STATUS CADANGAN</div>
                            <div class="small mb-1">Kelas: {{ $alert->course->title ?? '-' }}</div>
                            <div class="small mb-0 text-muted">Anda berada di daftar cadangan menunggu ketersediaan kuota.</div>
                        </div>
                    @endforeach
                </div>
            @endif
            @yield('content')
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
