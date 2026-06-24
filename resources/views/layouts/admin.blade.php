@php
    $adminUser = auth()->user();
    $adminInitial = strtoupper(substr($adminUser->name ?? 'A', 0, 1));
    $isInstructor = $adminUser?->hasAnyRole(['instructor', 'instruktur']);
    $lmsPrefix = $isInstructor ? 'instructor.lms.' : 'admin.';
    $canInstructorSchedule = $isInstructor || $adminUser?->hasAnyRole(['superadmin','admin']);
        $canContent = ! $isInstructor && $adminUser?->hasAnyPermission([
            'manage-berita',
            'manage-program',
            'manage-publication',
            'manage-gallery',
            'approve-content',
            'review-content',
            'manage-seo',
        ]);
        $canPublication = ! $isInstructor && $adminUser?->hasAnyPermission([
            'manage-publication',
            'manage-program',
            'manage-berita',
            'manage-gallery',
            'manage-settings',
        ]);
        $canService = ! $isInstructor && $adminUser?->hasAnyPermission([
            'manage-public-service',
            'manage-faq',
            'manage-ppid',
            'manage-settings',
        ]);
        $canAlumni = ! $isInstructor && $adminUser?->hasAnyPermission([
            'manage-users',
            'moderate-alumni-forum',
            'access-alumni-forum',
            'manage-enrollment',
        ]);
        $canSurvey = $adminUser?->hasAnyPermission(['manage-surveys', 'view-survey-analytics']);
        $canClass = $adminUser?->hasAnyPermission([
            'manage-classes',
            'manage-sessions',
            'manage-assignments',
            'grade-submissions',
            'manage-announcements',
            'moderate-class-forum',
            'manage-enrollment',
        ]);
        $canPpid = ! $isInstructor && $adminUser?->hasAnyPermission([
            'manage-ppid',
            'manage-publication',
            'manage-faq',
            'manage-settings',
            'manage-public-service',
        ]);
        $canSettings = ! $isInstructor && $adminUser?->hasPermission('manage-settings');
        $canAdministration = ! $isInstructor && $adminUser?->hasAnyPermission([
            'manage-users',
            'manage-access',
            'manage-audit',
        ]);
    $adminNotifications = $adminUser
        ? \App\Models\AdminNotification::forUser($adminUser->id)->latest()->take(8)->get()
        : collect();
    $notifCount = $adminUser
        ? \App\Models\AdminNotification::forUser($adminUser->id)->whereNull('read_at')->count()
        : 0;
    $flashMessages = [
        'success' => session('success'),
        'error' => session('error'),
        'warning' => session('warning'),
        'info' => session('info'),
    ];
@endphp
@if($isInstructor)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruktur LMS - Satpel PVP Bantul</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <style>
        body.instructor-ui {
            background: #f6f8fb;
        }
        .instructor-wrapper {
            min-height: 100vh;
            display: flex;
            gap: 0;
        }
        .instructor-sidebar {
            width: 260px;
            background: linear-gradient(180deg, #0c2d48 0%, #0a2540 100%);
            color: #e6edf7;
            padding: 24px 18px;
        }
        .instructor-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }
        .instructor-brand img {
            height: 42px;
        }
        .instructor-brand h5 {
            margin: 0;
            font-weight: 700;
            letter-spacing: 0.4px;
        }
        .instructor-brand small {
            color: #a7bddb;
        }
        .instructor-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #d6e4f5;
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 6px;
            transition: all 0.15s ease;
        }
        .instructor-menu a.active,
        .instructor-menu a:hover {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }
        .instructor-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .instructor-topbar {
            background: #ffffff;
            border-bottom: 1px solid #e6ecf5;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .instructor-topbar .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #0c2d48;
            color: #fff;
            display: grid;
            place-items: center;
            font-weight: 700;
        }
        .instructor-content {
            padding: 24px;
        }
    </style>
    @stack('styles')
</head>
<body class="instructor-ui">
    <div class="instructor-wrapper">
        <aside class="instructor-sidebar">
            <div class="instructor-brand">
                <img src="{{ asset('image/logo/Kemnaker_Logo_White.png') }}" alt="Kemnaker">
                <div>
                    <h5>Instruktur LMS</h5>
                    <small>Satpel PVP Bantul</small>
                </div>
            </div>
            <nav class="instructor-menu">
                <a href="{{ route('instructor.dashboard') }}" class="{{ request()->routeIs('instructor.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chalkboard-teacher"></i> Dasbor
                </a>
                <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.show') ? 'active' : '' }}">
                    <i class="fas fa-user-circle"></i> Profil Saya
                </a>
                <a href="{{ route('instructor.lms.course-class.index') }}" class="{{ request()->routeIs('instructor.lms.course-class.*') ? 'active' : '' }}">
                    <i class="fas fa-graduation-cap"></i> Kelas
                </a>
                <a href="{{ route('instructor.lms.course-session.index') }}" class="{{ request()->routeIs('instructor.lms.course-session.*') ? 'active' : '' }}">
                    <i class="fas fa-video"></i> Sesi
                </a>
                <a href="{{ route('instructor.lms.task-letter.index') }}" class="{{ request()->routeIs('instructor.lms.task-letter.*') ? 'active' : '' }}">
                    <i class="fas fa-file-signature"></i> Surat Tugas
                </a>
                <a href="{{ route('instructor.lms.course-module.index') }}" class="{{ request()->routeIs('instructor.lms.course-module.*') ? 'active' : '' }}">
                    <i class="fas fa-layer-group"></i> Bab
                </a>
                <a href="{{ route('instructor.lms.course-material.index') }}" class="{{ request()->routeIs('instructor.lms.course-material.*') ? 'active' : '' }}">
                    <i class="fas fa-book-open"></i> Materi
                </a>
                <a href="{{ route('instructor.lms.course-assignment.index') }}" class="{{ request()->routeIs('instructor.lms.course-assignment.*') ? 'active' : '' }}">
                    <i class="fas fa-tasks"></i> Tugas/Kuis
                </a>
                <a href="{{ route('instructor.lms.course-submission.index') }}" class="{{ request()->routeIs('instructor.lms.course-submission.*') ? 'active' : '' }}">
                    <i class="fas fa-file-signature"></i> Penilaian
                </a>
                <a href="{{ route('instructor.lms.course-gradebook.index') }}" class="{{ request()->routeIs('instructor.lms.course-gradebook.*') ? 'active' : '' }}">
                    <i class="fas fa-table"></i> Buku Nilai
                </a>
                <a href="{{ route('instructor.lms.course-attendance.index') }}" class="{{ request()->routeIs('instructor.lms.course-attendance.*') ? 'active' : '' }}">
                    <i class="fas fa-user-check"></i> Presensi
                </a>
                <a href="{{ route('instructor.lms.course-announcement.index') }}" class="{{ request()->routeIs('instructor.lms.course-announcement.*') ? 'active' : '' }}">
                    <i class="fas fa-bullhorn"></i> Pengumuman
                </a>
                <a href="{{ route('instructor.lms.course-progress.index') }}" class="{{ request()->routeIs('instructor.lms.course-progress.index') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Perkembangan
                </a>
                <a href="{{ route('instructor.lms.course-forum-reports.index') }}" class="{{ request()->routeIs('instructor.lms.course-forum-reports.*') ? 'active' : '' }}">
                    <i class="fas fa-flag"></i> Moderasi Forum
                </a>
                <a href="{{ route('instructor.schedules.index') }}" class="{{ request()->routeIs('instructor.schedules.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i> Jadwal Pribadi
                </a>
                <a href="{{ route('home') }}" target="_blank" rel="noopener">
                    <i class="fas fa-globe"></i> Lihat Website
                </a>
            </nav>
        </aside>
        <div class="instructor-main">
            <div class="instructor-topbar">
                <div>
                    <div class="fw-semibold">Halo, {{ $adminUser->name ?? 'Instruktur' }}</div>
                    <small class="text-muted">Kelola kelas Anda di LMS</small>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <form action="{{ route('lms.search') }}" method="GET" class="d-none d-md-flex">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="fas fa-magnifying-glass"></i></span>
                            <input type="text" name="q" class="form-control" placeholder="Cari di LMS..." value="{{ request('q') }}" aria-label="Cari di LMS">
                        </div>
                    </form>
                    <div class="avatar">{{ $adminInitial }}</div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-dark btn-sm">Keluar</button>
                    </form>
                </div>
            </div>
            @if(session('impersonator_id'))
                <div class="px-4 pt-3">
                    <div class="alert alert-warning d-flex justify-content-between align-items-center mb-0" role="alert">
                        <div>
                            <div class="fw-semibold">Mode impersonasi</div>
                            <small>Anda sedang masuk sebagai <strong>{{ $adminUser->name }}</strong>. Akun asli: {{ session('impersonator_name') }}.</small>
                        </div>
                        <form action="{{ route('impersonate.stop') }}" method="POST" class="m-0">
                            @csrf
                            <button class="btn btn-outline-dark btn-sm">
                                <i class="fas fa-rotate-left"></i> Kembali ke akun asli
                            </button>
                        </form>
                    </div>
                </div>
            @endif
            <main class="instructor-content">
                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
@else
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Satpel PVP Bantul</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <style>
        /* Pertahankan warna default; hanya menambahkan rotasi ikon */
        .menu-group.open .menu-group-header i { transform: rotate(180deg); transition: transform 0.2s ease; }
        /* Header sidebar selaras tema Kemnaker (lihat referensi) */
        .sidebar-header {
            background: #0c1328;
            color: #e9eef7;
            padding: 16px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .sidebar-header .brand-logo {
            height: 44px;
            width: auto;
        }
        .sidebar-header h4 {
            letter-spacing: 0.5px;
            color: #e9eef7;
        }
        .sidebar-header small {
            color: #9cb3d9;
        }
    </style>
    @stack('styles')
</head>
<body data-user-id="{{ $adminUser?->id }}">
    <a href="#adminMain" class="skip-link">Lewati ke konten utama</a>
    <div class="admin-wrapper">
    <div class="sidebar" id="adminSidebar" aria-hidden="false">
        <div class="sidebar-header d-flex justify-content-between align-items-start">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ asset('image/logo/Kemnaker_Logo_White.png') }}" alt="Kemnaker" class="brand-logo">
                <div>
                    <h4 class="fw-bold mb-0">Satpel PVP Bantul</h4>
                    <small>BPVP Surakarta • Kemnaker RI</small>
                </div>
            </div>
            <button class="btn btn-sm btn-outline-light d-lg-none" id="sidebarClose" aria-label="Tutup menu">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="sidebar-menu" role="navigation" aria-label="Menu utama admin">
            @if($isInstructor)
                    <div class="menu-group">
                        <button type="button" class="menu-group-header" data-target="#group-instructor">
                            <span>Instruktur</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="submenu show" id="group-instructor">
                            <a href="{{ route('instructor.dashboard') }}" class="{{ request()->routeIs('instructor.dashboard') ? 'active' : '' }}">
                                <i class="fas fa-chalkboard-teacher"></i> Dasbor Instruktur
                            </a>
                        </div>
                    </div>
                    @if($canClass)
                        <div class="menu-group">
                            <button type="button" class="menu-group-header" data-target="#group-class">
                                <span>Kelas</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="submenu show" id="group-class">
                                <a href="{{ route($lmsPrefix . 'course-class.index') }}" class="{{ request()->routeIs($lmsPrefix . 'course-class.*') ? 'active' : '' }}">
                                    <i class="fas fa-graduation-cap"></i> Kelas
                                </a>
                                @if($adminUser?->hasPermission('manage-enrollment'))
                                    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-enrollment.index' : 'admin.course-enrollment.index')) }}" class="{{ request()->routeIs('admin.course-enrollment.*') ? 'active' : '' }}">
                                        <i class="fas fa-user-plus"></i> Pendaftaran Peserta
                                    </a>
                                    <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-enrollment.import' : 'admin.course-enrollment.import')) }}" class="{{ request()->routeIs('admin.course-enrollment.import*') ? 'active' : '' }}">
                                        <i class="fas fa-file-import"></i> Impor Pendaftaran
                                    </a>
                                @endif
                                <a href="{{ route($lmsPrefix . 'course-session.index') }}" class="{{ request()->routeIs($lmsPrefix . 'course-session.*') ? 'active' : '' }}">
                                    <i class="fas fa-video"></i> Sesi Kelas
                                </a>
                                <a href="{{ route($lmsPrefix . 'task-letter.index') }}" class="{{ request()->routeIs($lmsPrefix . 'task-letter.*') ? 'active' : '' }}">
                                    <i class="fas fa-file-signature"></i> Surat Tugas
                                </a>
                                @if($adminUser?->hasAnyRole(['admin', 'superadmin']))
                                    <a href="{{ route($lmsPrefix . 'course-nominative.index') }}" class="{{ request()->routeIs($lmsPrefix . 'course-nominative.*') ? 'active' : '' }}">
                                        <i class="fas fa-list-ol"></i> Nominatif Peserta
                                    </a>
                                @endif
                                <a href="{{ route($lmsPrefix . 'course-module.index') }}" class="{{ request()->routeIs($lmsPrefix . 'course-module.*') ? 'active' : '' }}">
                                    <i class="fas fa-layer-group"></i> Bab Pembelajaran
                                </a>
                                <a href="{{ route($lmsPrefix . 'kejuruan-modules.index') }}" class="{{ request()->routeIs($lmsPrefix . 'kejuruan-modules.*') ? 'active' : '' }}">
                                    <i class="fas fa-folder-open"></i> Modul Kejuruan
                                </a>
                                <a href="{{ route($lmsPrefix . 'training-documentations.index') }}" class="{{ request()->routeIs($lmsPrefix . 'training-documentations.*') ? 'active' : '' }}">
                                    <i class="fas fa-camera"></i> Dokumentasi Pelatihan
                                </a>
                                @if($adminUser?->hasAnyRole(['admin', 'superadmin']))
                                    <a href="{{ route($lmsPrefix . 'course-curriculum.index') }}" class="{{ request()->routeIs($lmsPrefix . 'course-curriculum.*') ? 'active' : '' }}">
                                        <i class="fas fa-clipboard-list"></i> Kurikulum
                                    </a>
                                    <a href="{{ route($lmsPrefix . 'course-curriculum-unit.index') }}" class="{{ request()->routeIs($lmsPrefix . 'course-curriculum-unit.*') ? 'active' : '' }}">
                                        <i class="fas fa-list-check"></i> Unit Kompetensi
                                    </a>
                                @endif
                                <a href="{{ route($lmsPrefix . 'course-material.index') }}" class="{{ request()->routeIs($lmsPrefix . 'course-material.*') ? 'active' : '' }}">
                                    <i class="fas fa-book-open"></i> Materi Pembelajaran
                                </a>
                                <a href="{{ route($lmsPrefix . 'course-assignment.index') }}" class="{{ request()->routeIs($lmsPrefix . 'course-assignment.*') ? 'active' : '' }}">
                                    <i class="fas fa-tasks"></i> Tugas & Kuis
                                </a>
                                <a href="{{ route($lmsPrefix . 'course-attendance.index') }}" class="{{ request()->routeIs($lmsPrefix . 'course-attendance.*') ? 'active' : '' }}">
                                    <i class="fas fa-user-check"></i> Presensi Peserta
                                </a>
                                @if($adminUser?->hasPermission('grade-submissions'))
                                    <a href="{{ route($lmsPrefix . 'course-submission.index') }}" class="{{ request()->routeIs($lmsPrefix . 'course-submission.*') ? 'active' : '' }}">
                                        <i class="fas fa-file-signature"></i> Penilaian Tugas
                                    </a>
                                @endif
                                <a href="{{ route($lmsPrefix . 'course-announcement.index') }}" class="{{ request()->routeIs($lmsPrefix . 'course-announcement.*') ? 'active' : '' }}">
                                    <i class="fas fa-bullhorn"></i> Pengumuman Kelas
                                </a>
                                @if($adminUser?->hasPermission('moderate-class-forum'))
                                    <a href="{{ route($lmsPrefix . 'course-forum-reports.index') }}" class="{{ request()->routeIs($lmsPrefix . 'course-forum-reports.*') ? 'active' : '' }}">
                                        <i class="fas fa-flag"></i> Laporan Forum
                                    </a>
                                @endif
                                @if($adminUser?->hasPermission('manage-classes'))
                                    <a href="{{ route($lmsPrefix . 'course-progress.index') }}" class="{{ request()->routeIs($lmsPrefix . 'course-progress.index') ? 'active' : '' }}">
                                        <i class="fas fa-chart-line"></i> Perkembangan Kelas
                                    </a>
                                    <a href="{{ route($lmsPrefix . 'course-gradebook.index') }}" class="{{ request()->routeIs($lmsPrefix . 'course-gradebook.index') ? 'active' : '' }}">
                                        <i class="fas fa-table"></i> Buku Nilai
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                    @if($canInstructorSchedule)
                        <div class="menu-group">
                            <button type="button" class="menu-group-header" data-target="#group-schedule">
                                <span>Jadwal Instruktur</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="submenu show" id="group-schedule">
                                <a href="{{ route('instructor.schedules.index') }}" class="{{ request()->routeIs('instructor.schedules.*') ? 'active' : '' }}">
                                    <i class="fas fa-calendar-alt"></i> Kelola Jadwal
                                </a>
                            </div>
                        </div>
                    @endif
                    @if(auth()->user()?->hasPermission('view-talent-pool') || auth()->user()?->hasRole('superadmin'))
                        <div class="menu-group">
                            <button type="button" class="menu-group-header" data-target="#group-talent">
                                <span>Kemitraan</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="submenu show" id="group-talent">
                                <a href="{{ route('admin.talent-pool.index') }}" class="{{ request()->routeIs('admin.talent-pool.*') ? 'active' : '' }}">
                                    <i class="fas fa-users"></i> Bank Talenta & Buku CV
                                </a>
                            </div>
                        </div>
                    @endif
                    <div class="menu-group">
                        <button type="button" class="menu-group-header" data-target="#group-shortcuts">
                            <span>Pintasan</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="submenu show" id="group-shortcuts">
                            <a href="{{ route('home') }}" target="_blank" rel="noopener">
                                <i class="fas fa-globe"></i> Lihat Website
                            </a>
                        </div>
                    </div>
            @else
                <div class="menu-group">
                    <button type="button" class="menu-group-header" data-target="#group-dashboard">
                        <span>Ringkasan</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="submenu show" id="group-dashboard">
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-gauge"></i> Dasbor
                        </a>
                        <a href="{{ route('admin.pesan.index') }}" class="{{ request()->routeIs('admin.pesan.*') ? 'active' : '' }}">
                            <i class="fas fa-envelope-open-text"></i> Kotak Masuk
                        </a>
                    </div>
                </div>

                <div class="menu-group">
                    <button type="button" class="menu-group-header" data-target="#group-quick">
                        <span>Navigasi Cepat</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="submenu show" id="group-quick">
                        <div class="submenu-section">
                            <div class="submenu-title">Favorit</div>
                            <div class="submenu-links" id="quickFavorites">
                                <span class="submenu-placeholder">Belum ada favorit.</span>
                            </div>
                        </div>
                        <div class="submenu-section">
                            <div class="submenu-title">Terakhir Dibuka</div>
                            <div class="submenu-links" id="quickRecent">
                                <span class="submenu-placeholder">Belum ada riwayat.</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($canContent)
                <div class="menu-group">
                    <button type="button" class="menu-group-header" data-target="#group-training">
                        <span>Pelatihan</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="submenu show" id="group-training">
                        <a href="{{ route('admin.program.index') }}" class="{{ request()->routeIs('admin.program.*') ? 'active' : '' }}">
                            <i class="fas fa-graduation-cap"></i> Program
                        </a>
                        <a href="{{ route('admin.training-schedule.index') }}" class="{{ request()->routeIs('admin.training-schedule.*') ? 'active' : '' }}">
                            <i class="fas fa-calendar-check"></i> Jadwal
                        </a>
                        <a href="{{ route('admin.instructor.index') }}" class="{{ request()->routeIs('admin.instructor.*') ? 'active' : '' }}">
                            <i class="fas fa-chalkboard-teacher"></i> Instruktur
                        </a>
                        <a href="{{ route('admin.training-service.index') }}" class="{{ request()->routeIs('admin.training-service.*') ? 'active' : '' }}">
                            <i class="fas fa-layer-group"></i> Layanan Pelatihan
                        </a>
                        <a href="{{ route('admin.benefit.index') }}" class="{{ request()->routeIs('admin.benefit.*') ? 'active' : '' }}">
                            <i class="fas fa-list-check"></i> Benefit Pelatihan
                        </a>
                        <a href="{{ route('admin.flow.index') }}" class="{{ request()->routeIs('admin.flow.*') ? 'active' : '' }}">
                            <i class="fas fa-route"></i> Alur Pelatihan
                        </a>
                    </div>
                </div>
                @endif

                @if($canClass)
                <div class="menu-group">
                    <button type="button" class="menu-group-header" data-target="#group-class">
                        <span>Kelas</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="submenu show" id="group-class">
                        <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-class.index' : 'admin.course-class.index')) }}" class="{{ request()->routeIs('admin.course-class.*') ? 'active' : '' }}">
                            <i class="fas fa-graduation-cap"></i> Kelas
                        </a>
                        @if($adminUser?->hasPermission('manage-enrollment'))
                            <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-enrollment.index' : 'admin.course-enrollment.index')) }}" class="{{ request()->routeIs('admin.course-enrollment.*') ? 'active' : '' }}">
                                <i class="fas fa-user-plus"></i> Pendaftaran Peserta
                            </a>
                            <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-enrollment.import' : 'admin.course-enrollment.import')) }}" class="{{ request()->routeIs('admin.course-enrollment.import*') ? 'active' : '' }}">
                                <i class="fas fa-file-import"></i> Impor Pendaftaran
                            </a>
                        @endif
                        <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-session.index' : 'admin.course-session.index')) }}" class="{{ request()->routeIs('admin.course-session.*') ? 'active' : '' }}">
                            <i class="fas fa-video"></i> Sesi Kelas
                        </a>
                        <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-module.index' : 'admin.course-module.index')) }}" class="{{ request()->routeIs('admin.course-module.*') ? 'active' : '' }}">
                            <i class="fas fa-layer-group"></i> Bab Pembelajaran
                        </a>
                        <a href="{{ route('admin.kejuruan-modules.index') }}" class="{{ request()->routeIs('admin.kejuruan-modules.*') ? 'active' : '' }}">
                            <i class="fas fa-folder-open"></i> Modul Kejuruan
                        </a>
                        <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-material.index' : 'admin.course-material.index')) }}" class="{{ request()->routeIs('admin.course-material.*') ? 'active' : '' }}">
                            <i class="fas fa-book-open"></i> Materi Pembelajaran
                        </a>
                        <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-assignment.index' : 'admin.course-assignment.index')) }}" class="{{ request()->routeIs('admin.course-assignment.*') ? 'active' : '' }}">
                            <i class="fas fa-tasks"></i> Tugas & Kuis
                        </a>
                        <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-attendance.index' : 'admin.course-attendance.index')) }}" class="{{ request()->routeIs('admin.course-attendance.*') ? 'active' : '' }}">
                            <i class="fas fa-user-check"></i> Presensi Peserta
                        </a>
                        @if($adminUser?->hasPermission('grade-submissions'))
                            <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-submission.index' : 'admin.course-submission.index')) }}" class="{{ request()->routeIs('admin.course-submission.*') ? 'active' : '' }}">
                                <i class="fas fa-file-signature"></i> Penilaian Tugas
                            </a>
                        @endif
                        <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-announcement.index' : 'admin.course-announcement.index')) }}" class="{{ request()->routeIs('admin.course-announcement.*') ? 'active' : '' }}">
                            <i class="fas fa-bullhorn"></i> Pengumuman Kelas
                        </a>
                        @if($adminUser?->hasPermission('moderate-class-forum'))
                            <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-forum-reports.index' : 'admin.course-forum-reports.index')) }}" class="{{ request()->routeIs('admin.course-forum-reports.*') ? 'active' : '' }}">
                                <i class="fas fa-flag"></i> Laporan Forum
                            </a>
                        @endif
                    </div>
                </div>
                @endif

                @if($adminUser?->hasPermission('manage-enrollment') || $adminUser?->hasPermission('manage-surveys') || $adminUser?->hasPermission('manage-classes'))
                <div class="menu-group">
                    <button type="button" class="menu-group-header" data-target="#group-reports">
                        <span>Laporan</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="submenu show" id="group-reports">
                        @if($adminUser?->hasPermission('manage-enrollment'))
                            <a href="{{ route('admin.reports.learning-summary') }}" class="{{ request()->routeIs('admin.reports.learning-summary') ? 'active' : '' }}">
                                <i class="fas fa-chart-bar"></i> Monitoring Pelatihan
                            </a>
                            <a href="{{ route('admin.task-letter.index') }}" class="{{ request()->routeIs('admin.task-letter.*') ? 'active' : '' }}">
                                <i class="fas fa-file-signature"></i> Surat Tugas
                            </a>
                            <a href="{{ route('admin.decision-letter.index') }}" class="{{ request()->routeIs('admin.decision-letter.*') ? 'active' : '' }}">
                                <i class="fas fa-file-contract"></i> Surat Keputusan (SK)
                            </a>
                            @if($adminUser?->hasAnyRole(['admin', 'superadmin']))
                                <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-nominative.index' : 'admin.course-nominative.index')) }}" class="{{ request()->routeIs('admin.course-nominative.*') ? 'active' : '' }}">
                                    <i class="fas fa-list-ol"></i> Nominatif Peserta
                                </a>
                                <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-curriculum.index' : 'admin.course-curriculum.index')) }}" class="{{ request()->routeIs('admin.course-curriculum.*') ? 'active' : '' }}">
                                    <i class="fas fa-clipboard-list"></i> Kurikulum
                                </a>
                                <a href="{{ route((request()->routeIs('instructor.*') || request()->routeIs('*.lms.*') ? 'instructor.lms.course-curriculum-unit.index' : 'admin.course-curriculum-unit.index')) }}" class="{{ request()->routeIs('admin.course-curriculum-unit.*') ? 'active' : '' }}">
                                    <i class="fas fa-list-check"></i> Unit Kompetensi
                                </a>
                            @endif
                        @endif
                        @if($adminUser?->hasPermission('manage-surveys'))
                            <a href="{{ route('admin.survey-instance.dashboard') }}" class="{{ request()->routeIs('admin.survey-instance.dashboard') ? 'active' : '' }}">
                                <i class="fas fa-clipboard-check"></i> Laporan Survei
                            </a>
                        @endif
                        @if($adminUser?->hasPermission('manage-classes'))
                            <a href="{{ route('admin.reports.kejuruan-modules') }}" class="{{ request()->routeIs('admin.reports.kejuruan-modules') ? 'active' : '' }}">
                                <i class="fas fa-folder-open"></i> Laporan Modul Kejuruan
                            </a>
                            <a href="{{ route('admin.reports.training-documentations') }}" class="{{ request()->routeIs('admin.reports.training-documentations') ? 'active' : '' }}">
                                <i class="fas fa-camera"></i> Laporan Dokumentasi
                            </a>
                        @endif
                    </div>
                </div>
                @endif

                @if($canContent)
                <div class="menu-group">
                    <button type="button" class="menu-group-header" data-target="#group-content-publication">
                        <span>Konten & Publikasi</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="submenu show" id="group-content-publication">
                        <a href="{{ route('admin.berita.index') }}" class="{{ request()->routeIs('admin.berita.*') ? 'active' : '' }}">
                            <i class="fas fa-newspaper"></i> Berita / Artikel
                        </a>
                        <a href="{{ route('admin.pengumuman.index') }}" class="{{ request()->routeIs('admin.pengumuman.*') ? 'active' : '' }}">
                            <i class="fas fa-bullhorn"></i> Pengumuman
                        </a>
                        <a href="{{ route('admin.galeri.index') }}" class="{{ request()->routeIs('admin.galeri.*') ? 'active' : '' }}">
                            <i class="fas fa-image"></i> Galeri Foto
                        </a>
                        <a href="{{ route('admin.lowongan.index') }}" class="{{ request()->routeIs('admin.lowongan.*') ? 'active' : '' }}">
                            <i class="fas fa-briefcase"></i> Lowongan Kerja
                        </a>
                        <a href="{{ route('admin.partner.index') }}" class="{{ request()->routeIs('admin.partner.*') ? 'active' : '' }}">
                            <i class="fas fa-handshake-angle"></i> Mitra & Kolaborasi
                        </a>
                        <a href="{{ route('admin.testimonial.index') }}" class="{{ request()->routeIs('admin.testimonial.*') ? 'active' : '' }}">
                            <i class="fas fa-comment-dots"></i> Testimonial
                        </a>
                    </div>
                </div>
                @endif

                @if($canPublication)
                <div class="menu-group">
                    <button type="button" class="menu-group-header" data-target="#group-publication">
                        <span>Publikasi Digital</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="submenu show" id="group-publication">
                        <a href="{{ route('admin.publication.settings') }}" class="{{ request()->routeIs('admin.publication.settings*') ? 'active' : '' }}">
                            <i class="fas fa-sliders"></i> Pengaturan Publikasi
                        </a>
                        <a href="{{ route('admin.publication-category.index') }}" class="{{ request()->routeIs('admin.publication-category.*') ? 'active' : '' }}">
                            <i class="fas fa-folder-tree"></i> Kategori Publikasi
                        </a>
                        <a href="{{ route('admin.publication-item.index') }}" class="{{ request()->routeIs('admin.publication-item.*') ? 'active' : '' }}">
                            <i class="fas fa-book-open"></i> Item Publikasi
                        </a>
                        <a href="{{ route('admin.certification-content.index') }}" class="{{ request()->routeIs('admin.certification-content.*') ? 'active' : '' }}">
                            <i class="fas fa-layer-group"></i> Konten Sertifikasi
                        </a>
                        <a href="{{ route('admin.certification-scheme.index') }}" class="{{ request()->routeIs('admin.certification-scheme.*') ? 'active' : '' }}">
                            <i class="fas fa-id-badge"></i> Skema Sertifikasi
                        </a>
                        <a href="{{ route('admin.profile.index') }}" class="{{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                            <i class="fas fa-building"></i> Profil Instansi
                        </a>
                    </div>
                </div>
                @endif

                @if($canService)
                <div class="menu-group">
                    <button type="button" class="menu-group-header" data-target="#group-service">
                        <span>Pelayanan Publik</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="submenu show" id="group-service">
                        <a href="{{ route('admin.public-service.settings') }}" class="{{ request()->routeIs('admin.public-service.settings*') ? 'active' : '' }}">
                            <i class="fas fa-headset"></i> Pengaturan Pelayanan
                        </a>
                        <a href="{{ route('admin.public-service-flow.index') }}" class="{{ request()->routeIs('admin.public-service-flow.*') ? 'active' : '' }}">
                            <i class="fas fa-route"></i> Alur Pelayanan
                        </a>
                        <a href="{{ route('admin.contact.settings') }}" class="{{ request()->routeIs('admin.contact.settings*') ? 'active' : '' }}">
                            <i class="fas fa-phone"></i> Hubungi Kami
                        </a>
                        <a href="{{ route('admin.contact-channel.index') }}" class="{{ request()->routeIs('admin.contact-channel.*') ? 'active' : '' }}">
                            <i class="fas fa-address-card"></i> Saluran Kontak
                        </a>
                        <a href="{{ route('admin.faq.settings') }}" class="{{ request()->routeIs('admin.faq.settings*') ? 'active' : '' }}">
                            <i class="fas fa-circle-question"></i> Pengaturan FAQ
                        </a>
                        <a href="{{ route('admin.faq-category.index') }}" class="{{ request()->routeIs('admin.faq-category.*') ? 'active' : '' }}">
                            <i class="fas fa-folder-open"></i> Kategori FAQ
                        </a>
                        <a href="{{ route('admin.faq-item.index') }}" class="{{ request()->routeIs('admin.faq-item.*') ? 'active' : '' }}">
                            <i class="fas fa-question"></i> Item FAQ
                        </a>
                    </div>
                </div>
                @endif

                @if($canAlumni)
                <div class="menu-group">
                    <button type="button" class="menu-group-header" data-target="#group-alumni">
                        <span>Alumni & Tracer</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="submenu show" id="group-alumni">
                        <a href="{{ route('admin.alumni.index') }}" class="{{ request()->routeIs('admin.alumni.*') ? 'active' : '' }}">
                            <i class="fas fa-user-graduate"></i> Data Alumni
                        </a>
                        <a href="{{ route('admin.alumni-tracer.index') }}" class="{{ request()->routeIs('admin.alumni-tracer.*') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-check"></i> Tracer Study
                        </a>
                    </div>
                </div>
                @endif

                @if($canSurvey)
                <div class="menu-group">
                    <button type="button" class="menu-group-header" data-target="#group-survey">
                        <span>Survei & Umpan Balik</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="submenu show" id="group-survey">
                        @if($adminUser?->hasPermission('manage-surveys'))
                            <a href="{{ route('admin.surveys.index') }}" class="{{ request()->routeIs('admin.surveys.*') ? 'active' : '' }}">
                                <i class="fas fa-clipboard-list"></i> Survei Dinamis
                            </a>
                        @endif
                    </div>
                </div>
                @endif

                @if($canPpid)
                <div class="menu-group">
                    <button type="button" class="menu-group-header" data-target="#group-ppid">
                        <span>PPID & Infografis</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="submenu show" id="group-ppid">
                        <a href="{{ route('admin.ppid.settings') }}" class="{{ request()->routeIs('admin.ppid.settings*') ? 'active' : '' }}">
                            <i class="fas fa-shield-alt"></i> Pengaturan PPID
                        </a>
                        <a href="{{ route('admin.ppid-highlight.index') }}" class="{{ request()->routeIs('admin.ppid-highlight.*') ? 'active' : '' }}">
                            <i class="fas fa-icons"></i> Highlight PPID
                        </a>
                        <a href="{{ route('admin.ppid-request.index') }}" class="{{ request()->routeIs('admin.ppid-request.*') ? 'active' : '' }}">
                            <i class="fas fa-inbox"></i> Permohonan PPID
                        </a>
                        <a href="{{ route('admin.infographic-year.index') }}" class="{{ request()->routeIs('admin.infographic-year.*') ? 'active' : '' }}">
                            <i class="fas fa-calendar"></i> Infografis Tahun
                        </a>
                        <a href="{{ route('admin.infographic-metric.index') }}" class="{{ request()->routeIs('admin.infographic-metric.*') ? 'active' : '' }}">
                            <i class="fas fa-list-ol"></i> Infografis Metric
                        </a>
                        <a href="{{ route('admin.infographic-card.index') }}" class="{{ request()->routeIs('admin.infographic-card.*') ? 'active' : '' }}">
                            <i class="fas fa-layer-group"></i> Infografis Kartu
                        </a>
                        <a href="{{ route('admin.infographic-embed.index') }}" class="{{ request()->routeIs('admin.infographic-embed.*') ? 'active' : '' }}">
                            <i class="fas fa-video"></i> Infografis Embed
                        </a>
                    </div>
                </div>
                @endif

                @if(auth()->user()?->hasPermission('view-talent-pool') || auth()->user()?->hasRole('superadmin'))
                <div class="menu-group">
                    <button type="button" class="menu-group-header" data-target="#group-talent">
                        <span>Kemitraan</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="submenu show" id="group-talent">
                        <a href="{{ route('admin.talent-pool.index') }}" class="{{ request()->routeIs('admin.talent-pool.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i> Bank Talenta & Buku CV
                        </a>
                    </div>
                </div>
                @endif

                @if($canAdministration)
                <div class="menu-group">
                    <button type="button" class="menu-group-header" data-target="#group-admin">
                        <span>Administrasi</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="submenu show" id="group-admin">
                        @if(auth()->user()?->hasPermission('manage-users'))
                            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i class="fas fa-users-cog"></i> Manajemen Pengguna
                            </a>
                        @endif
                        @if(auth()->user()?->hasPermission('manage-access'))
                            <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                <i class="fas fa-user-shield"></i> Peran
                            </a>
                            <a href="{{ route('admin.permissions.index') }}" class="{{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                                <i class="fas fa-key"></i> Izin Akses
                            </a>
                        @endif
                        @if(auth()->user()?->hasPermission('manage-audit'))
                            <a href="{{ route('admin.activity-logs.index') }}" class="{{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                                <i class="fas fa-clipboard-list"></i> Log Aktivitas
                            </a>
                        @endif
                        @if(Route::has('admin.branding-kpi.index'))
                            <a href="{{ route('admin.branding-kpi.index') }}" class="{{ request()->routeIs('admin.branding-kpi.*') ? 'active' : '' }}">
                                <i class="fas fa-bullseye"></i> KPI Pencitraan
                            </a>
                        @endif
                    </div>
                </div>
                @endif

                @if($canSettings)
                <div class="menu-group">
                    <button type="button" class="menu-group-header" data-target="#group-settings">
                        <span>Pengaturan Situs</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="submenu show" id="group-settings">
                        <a href="{{ route('admin.settings.portal') }}" class="{{ request()->routeIs('admin.settings.portal*') || request()->routeIs('admin.settings.site*') ? 'active' : '' }}">
                            <i class="fas fa-sliders-h"></i> Pengaturan Portal
                        </a>
                    </div>
                </div>
                @endif

                <div class="menu-group">
                    <button type="button" class="menu-group-header" data-target="#group-shortcuts">
                        <span>Pintasan</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="submenu show" id="group-shortcuts">
                        <a href="{{ route('home') }}" target="_blank" rel="noopener">
                            <i class="fas fa-globe"></i> Lihat Website
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="sidebar-overlay d-lg-none"></div>

    <main class="content-area" id="adminMain" role="main">
        <div class="topbar topbar-modern topbar-compact">
            <div class="topbar-left">
            <button class="sidebar-toggle d-lg-none" id="sidebarToggle" aria-label="Buka menu" aria-controls="adminSidebar" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
                <div class="topbar-title-group">
                    <div class="admin-breadcrumb">
                        <a href="{{ route('admin.dashboard') }}">Dasbor</a>
                        <span>/</span>
                        <span>@yield('page_title', 'Panel Admin')</span>
                    </div>
                    <span class="topbar-separator">•</span>
                    <div class="topbar-title">
                        <h1 class="mb-0">@yield('page_title', 'Panel Admin')</h1>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <form action="{{ route('lms.search') }}" method="GET" class="topbar-search d-none d-lg-flex">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-magnifying-glass"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Cari di LMS..." value="{{ request('q') }}" aria-label="Cari di LMS">
                    </div>
                </form>
                <div class="dropdown">
                    <button class="btn btn-soft-secondary btn-sm position-relative d-flex align-items-center gap-2" id="notifyDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifikasi">
                        <i class="fas fa-bell"></i>
                        <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle notif-badge {{ $notifCount ? '' : 'd-none' }}" id="notifBadge">{{ $notifCount }}</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0 shadow" style="min-width: 300px;">
                        <div class="p-2 border-bottom d-flex justify-content-between align-items-center gap-2">
                            <span class="fw-semibold">Notifikasi</span>
                            <div class="d-flex align-items-center gap-2">
                                <small class="text-muted" id="notifCountLabel">{{ $notifCount }} belum dibaca</small>
                                @if($notifCount > 0)
                                    <form action="{{ route('admin.notifications.read-all') }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="btn btn-link btn-sm text-decoration-none p-0">Tandai semua</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <div class="list-group list-group-flush" id="notifList">
                            @forelse($adminNotifications as $note)
                                @php
                                    $type = $note->type ?? 'info';
                                    $badgeClass = match ($type) {
                                        'success' => 'bg-success',
                                        'error', 'danger' => 'bg-danger',
                                        'warning' => 'bg-warning text-dark',
                                        'info' => 'bg-info text-dark',
                                        default => 'bg-secondary',
                                    };
                                    $title = $note->title ?: 'Info';
                                    $isUnread = is_null($note->read_at);
                                @endphp
                                <div class="list-group-item small d-flex gap-2 align-items-start notification-item {{ $isUnread ? 'notification-unread' : '' }}" data-unread="{{ $isUnread ? 1 : 0 }}">
                                    <span class="badge rounded-pill {{ $badgeClass }}">{{ ucfirst($type) }}</span>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $title }}</div>
                                        <div>{{ $note->message }}</div>
                                        <small class="text-muted">{{ $note->created_at?->format('d M Y H:i') }}</small>
                                    </div>
                                    @if($isUnread)
                                        <form action="{{ route('admin.notifications.read', $note) }}" method="POST" class="ms-auto">
                                            @csrf
                                            <button type="submit" class="btn btn-link btn-sm text-decoration-none px-0">Tandai dibaca</button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <div class="list-group-item text-muted small" id="notifEmpty">Belum ada notifikasi.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @php
                    $adminRoleLabel = $adminUser?->hasRole('superadmin')
                        ? 'Superadmin'
                        : ($adminUser?->hasRole('admin') ? 'Admin' : 'Pengelola');
                @endphp
                <div class="dropdown">
                    <button class="user-pill shadow-sm dropdown-toggle" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">{{ $adminInitial }}</div>
                        <div class="user-info text-start">
                            <div class="fw-semibold">{{ $adminUser->name ?? 'Admin' }}</div>
                            <small class="text-muted">{{ session('impersonator_id') ? 'Impersonasi aktif' : $adminRoleLabel }}</small>
                        </div>
                        <i class="fas fa-chevron-down ms-1 text-muted"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userMenuDropdown">
                        <a class="dropdown-item" href="{{ route('profile.show') }}">
                            <i class="fas fa-user-circle me-2"></i> Profil Saya
                        </a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-arrow-right-from-bracket me-2"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if(session('impersonator_id'))
            <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-user-secret"></i>
                    <div>
                        <div class="fw-semibold">Mode impersonasi</div>
                        <small>Anda sedang masuk sebagai <strong>{{ $adminUser->name }}</strong>. Akun asli: {{ session('impersonator_name') }}.</small>
                    </div>
                </div>
                <form action="{{ route('impersonate.stop') }}" method="POST" class="m-0">
                    @csrf
                    <button class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-rotate-left"></i> Kembali ke akun asli
                    </button>
                </form>
            </div>
        @endif

        <div class="content-card">
            @yield('content')
        </div>
    </main>

    </div>
    <div aria-live="polite" aria-atomic="true" class="position-fixed" style="top:1rem; right:1rem; z-index:1080;">
        <div id="admin-toast-container" class="toast-container"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const toastContainer = document.getElementById('admin-toast-container');
        function pushToast({ type = 'info', title = '', message = '', delay = 4500 } = {}) {
            if (!toastContainer) return;
            const toastEl = document.createElement('div');
            const typeClass = {
                success: 'text-bg-success',
                error: 'text-bg-danger',
                danger: 'text-bg-danger',
                warning: 'text-bg-warning',
                info: 'text-bg-info',
            }[type] || 'text-bg-secondary';
            toastEl.className = `toast align-items-center border-0 shadow ${typeClass}`;
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${title ? `<strong class="d-block mb-1">${title}</strong>` : ''}
                        <span>${message}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            toastContainer.appendChild(toastEl);
            const toast = new bootstrap.Toast(toastEl, { delay });
            toast.show();
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        }

        // Bell dropdown list elements and functions
        const notifList = document.getElementById('notifList');
        const notifBadge = document.getElementById('notifBadge');
        const notifCountLabel = document.getElementById('notifCountLabel');
        function updateNotifCount() {
            if (!notifList || !notifBadge) return;
            const unreadCount = notifList.querySelectorAll('.notification-item[data-unread="1"]').length;
            notifBadge.textContent = unreadCount;
            notifBadge.classList.toggle('d-none', unreadCount === 0);
            if (notifCountLabel) {
                notifCountLabel.textContent = unreadCount + ' belum dibaca';
            }
        }
        function addNotification({ type = 'info', title = 'Info', message = '', time = null } = {}) {
            if (!notifList) return;
            const notifEmpty = document.getElementById('notifEmpty');
            if (notifEmpty) notifEmpty.remove();
            const item = document.createElement('div');
            const badgeClass = {
                success: 'bg-success',
                error: 'bg-danger',
                danger: 'bg-danger',
                warning: 'bg-warning text-dark',
                info: 'bg-info text-dark',
            }[type] || 'bg-secondary';
            item.className = 'list-group-item small d-flex gap-2 align-items-start notification-item notification-unread';
            item.dataset.unread = '1';
            item.innerHTML = `
                <span class="badge rounded-pill ${badgeClass}">${(type || 'info').charAt(0).toUpperCase() + (type || 'info').slice(1)}</span>
                <div class="flex-grow-1">
                    <div class="fw-semibold">${title}</div>
                    <div>${message}</div>
                    <small class="text-muted">${time || new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}</small>
                </div>
            `;
            notifList.prepend(item);
            updateNotifCount();
        }

        // Flash toasts from backend sessions
        const flashPayload = @json($flashMessages);
        Object.entries(flashPayload).forEach(([key, val]) => {
            if (!val) return;
            const map = { success: 'Berhasil', error: 'Gagal', warning: 'Perhatian', info: 'Info' };
            pushToast({ type: key, title: map[key] || 'Info', message: val });
            addNotification({ type: key, title: map[key] || 'Info', message: val });
        });

        // Realtime-style hook: dispatch CustomEvent('notify', { detail: { type, title, message, delay } })
        window.addEventListener('notify', (e) => {
            const detail = e.detail || {};
            if (detail.message) {
                pushToast(detail);
                addNotification(detail);
            }
        });

        const bodyEl = document.body;
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarClose = document.getElementById('sidebarClose');
        const sidebarOverlay = document.querySelector('.sidebar-overlay');
        const sidebar = document.getElementById('adminSidebar');

        function syncSidebarAria() {
            const isMobile = window.matchMedia('(max-width: 1199.98px)').matches;
            const isOpen = !isMobile || bodyEl.classList.contains('sidebar-open');
            if (sidebarToggle) {
                sidebarToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }
            if (sidebar) {
                sidebar.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            }
        }

        function toggleSidebar(state) {
            if (state === 'close') {
                bodyEl.classList.remove('sidebar-open');
            } else {
                bodyEl.classList.toggle('sidebar-open');
            }
            syncSidebarAria();
        }

        if (sidebarToggle) sidebarToggle.addEventListener('click', () => toggleSidebar());
        if (sidebarClose) sidebarClose.addEventListener('click', () => toggleSidebar('close'));
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', () => toggleSidebar('close'));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && bodyEl.classList.contains('sidebar-open')) {
                toggleSidebar('close');
            }
        });
        syncSidebarAria();

        const groupButtons = Array.from(document.querySelectorAll('.menu-group-header'));
        const storageKey = 'adminSidebarOpenGroups';

        function saveOpenGroups() {
            const openIds = Array.from(document.querySelectorAll('.submenu.show')).map(el => el.id);
            localStorage.setItem(storageKey, JSON.stringify(openIds));
        }

        function applySubmenuHeight(submenu) {
            if (!submenu) return;
            if (submenu.classList.contains('show')) {
                submenu.style.maxHeight = submenu.scrollHeight + 'px';
            } else {
                submenu.style.maxHeight = '0px';
            }
        }

        function restoreGroups() {
            const saved = localStorage.getItem(storageKey);
            if (!saved) return;
            try {
                const ids = JSON.parse(saved);
                document.querySelectorAll('.submenu').forEach(el => {
                    el.classList.toggle('show', ids.includes(el.id));
                });
                document.querySelectorAll('.submenu').forEach(applySubmenuHeight);
            } catch (e) {}
        }

        restoreGroups();

        // Selalu buka grup yang memiliki link aktif
        document.querySelectorAll('.submenu').forEach(function (submenu) {
            if (submenu.querySelector('a.active')) {
                submenu.classList.add('show');
            } else if (!localStorage.getItem(storageKey)) {
                submenu.classList.remove('show');
            }
        });
        document.querySelectorAll('.submenu').forEach(applySubmenuHeight);

        function getGroupTarget(btn) {
            if (!btn?.dataset?.target) return null;
            return document.querySelector(btn.dataset.target);
        }

        function syncGroupState(btn, index) {
            const target = getGroupTarget(btn);
            if (!target) return;
            if (!btn.id) {
                btn.id = `menu-group-${index}`;
            }
            const targetId = target.id || btn.dataset.target.replace('#', '');
            if (!target.id) target.id = targetId;
            const isOpen = target.classList.contains('show');
            btn.setAttribute('aria-controls', targetId);
            btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            btn.setAttribute('type', 'button');
            target.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            target.setAttribute('role', 'region');
            target.setAttribute('aria-labelledby', btn.id);
            btn.closest('.menu-group')?.classList.toggle('open', isOpen);
        }

        function syncAllGroups() {
            groupButtons.forEach((btn, index) => syncGroupState(btn, index));
        }

        function setGroupState(btn, open, closeOthers = true) {
            const target = getGroupTarget(btn);
            if (!target) return;
            if (closeOthers) {
                document.querySelectorAll('.submenu').forEach(el => {
                    if (el !== target) el.classList.remove('show');
                });
                document.querySelectorAll('.menu-group').forEach(g => {
                    if (g !== btn.closest('.menu-group')) g.classList.remove('open');
                });
            }
            target.classList.toggle('show', open);
            btn.closest('.menu-group')?.classList.toggle('open', open);
            document.querySelectorAll('.submenu').forEach(applySubmenuHeight);
            syncAllGroups();
            saveOpenGroups();
        }

        syncAllGroups();

        groupButtons.forEach(function (btn, index) {
            btn.addEventListener('click', function () {
                const target = getGroupTarget(btn);
                if (!target) return;
                const isOpen = target.classList.contains('show');
                setGroupState(btn, !isOpen, true);
            });

            btn.addEventListener('keydown', function (event) {
                if (groupButtons.length === 0) return;
                const lastIndex = groupButtons.length - 1;
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    const next = groupButtons[Math.min(index + 1, lastIndex)];
                    next?.focus();
                }
                if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    const prev = groupButtons[Math.max(index - 1, 0)];
                    prev?.focus();
                }
                if (event.key === 'Home') {
                    event.preventDefault();
                    groupButtons[0]?.focus();
                }
                if (event.key === 'End') {
                    event.preventDefault();
                    groupButtons[lastIndex]?.focus();
                }
                if (event.key === 'ArrowRight') {
                    event.preventDefault();
                    setGroupState(btn, true, true);
                }
                if (event.key === 'ArrowLeft') {
                    event.preventDefault();
                    setGroupState(btn, false, false);
                }
            });
        });

        window.addEventListener('resize', () => {
            document.querySelectorAll('.submenu.show').forEach(applySubmenuHeight);
            syncSidebarAria();
        });

        // Navigasi cepat (favorit & terakhir dibuka)
        const favoritesContainer = document.getElementById('quickFavorites');
        const recentContainer = document.getElementById('quickRecent');
        const userId = document.body?.dataset?.userId || 'guest';
        const favoritesKey = `adminMenuFavorites:${userId}`;
        const recentKey = `adminMenuRecent:${userId}`;
        const MAX_RECENT = 6;

        function safeStorageGet(key) {
            try {
                return JSON.parse(localStorage.getItem(key)) || [];
            } catch (err) {
                return [];
            }
        }

        function safeStorageSet(key, value) {
            try {
                localStorage.setItem(key, JSON.stringify(value));
            } catch (err) {}
        }

        function buildMenuItem(link) {
            const label = (link.dataset.menuLabel || link.textContent || '').replace(/\s+/g, ' ').trim();
            const icon = link.dataset.menuIcon || link.querySelector('i')?.className || 'fas fa-circle';
            return {
                href: link.getAttribute('href'),
                label,
                icon,
                target: link.getAttribute('target') || null,
            };
        }

        function renderQuickList(container, items, emptyText) {
            if (!container) return;
            container.innerHTML = '';
            if (!items.length) {
                const placeholder = document.createElement('span');
                placeholder.className = 'submenu-placeholder';
                placeholder.textContent = emptyText;
                container.appendChild(placeholder);
                return;
            }
            items.forEach((item) => {
                const link = document.createElement('a');
                link.href = item.href;
                link.className = 'quick-link';
                link.innerHTML = `<i class="${item.icon}"></i><span>${item.label}</span>`;
                if (item.target) {
                    link.target = item.target;
                    link.rel = 'noopener';
                }
                container.appendChild(link);
            });
        }

        function syncPinButtons(favorites) {
            document.querySelectorAll('.menu-pin').forEach((btn) => {
                const href = btn.dataset.href;
                const isPinned = favorites.some((item) => item.href === href);
                btn.classList.toggle('is-pinned', isPinned);
                btn.setAttribute('aria-pressed', isPinned ? 'true' : 'false');
                btn.setAttribute('aria-label', isPinned ? 'Lepas sematan' : 'Sematkan menu');
            });
        }

        function toggleFavorite(link) {
            const item = buildMenuItem(link);
            if (!item.href) return;
            const favorites = safeStorageGet(favoritesKey);
            const existingIndex = favorites.findIndex((fav) => fav.href === item.href);
            if (existingIndex >= 0) {
                favorites.splice(existingIndex, 1);
            } else {
                favorites.unshift(item);
            }
            safeStorageSet(favoritesKey, favorites);
            renderQuickList(favoritesContainer, favorites, 'Belum ada favorit.');
            syncPinButtons(favorites);
        }

        function registerRecent(link) {
            const item = buildMenuItem(link);
            if (!item.href) return;
            const recent = safeStorageGet(recentKey).filter((entry) => entry.href !== item.href);
            recent.unshift(item);
            recent.splice(MAX_RECENT);
            safeStorageSet(recentKey, recent);
            renderQuickList(recentContainer, recent, 'Belum ada riwayat.');
        }

        const initialFavorites = safeStorageGet(favoritesKey);
        const initialRecent = safeStorageGet(recentKey);
        renderQuickList(favoritesContainer, initialFavorites, 'Belum ada favorit.');
        renderQuickList(recentContainer, initialRecent, 'Belum ada riwayat.');

        document.querySelectorAll('.sidebar-menu .submenu > a').forEach((link) => {
            const label = (link.textContent || '').replace(/\s+/g, ' ').trim();
            if (label) link.dataset.menuLabel = label;
            const icon = link.querySelector('i')?.className;
            if (icon) link.dataset.menuIcon = icon;

            if (!link.closest('.menu-item')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'menu-item';
                link.parentNode.insertBefore(wrapper, link);
                wrapper.appendChild(link);

                const pinButton = document.createElement('button');
                pinButton.type = 'button';
                pinButton.className = 'menu-pin';
                pinButton.dataset.href = link.getAttribute('href');
                pinButton.innerHTML = '<i class="fas fa-thumbtack"></i>';
                pinButton.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    toggleFavorite(link);
                });
                wrapper.appendChild(pinButton);
            }

            link.addEventListener('click', () => registerRecent(link));
        });

        syncPinButtons(initialFavorites);

        const activeLink = document.querySelector('.submenu a.active');
        document.querySelectorAll('.submenu a.active').forEach((link) => {
            link.setAttribute('aria-current', 'page');
        });
        if (activeLink) {
            registerRecent(activeLink);
            activeLink.scrollIntoView({ block: 'center' });
        }

        updateNotifCount();

        let tableObserver = null;

        function wrapAdminTables() {
            document.querySelectorAll('.content-card table').forEach((table) => {
                if (table.closest('.table-responsive')) return;
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            });
        }

        function observeAdminTables() {
            if (tableObserver) return;
            const target = document.querySelector('.content-card');
            if (!target) return;
            tableObserver = new MutationObserver((mutations) => {
                if (mutations.some(mutation => mutation.addedNodes.length)) {
                    wrapAdminTables();
                }
            });
            tableObserver.observe(target, { childList: true, subtree: true });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                wrapAdminTables();
                observeAdminTables();
            });
        } else {
            wrapAdminTables();
            observeAdminTables();
        }
    </script>
    @stack('scripts')
</body>
</html>
@endif
