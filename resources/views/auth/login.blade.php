<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Satpel PVP Bantul</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --brand-primary: #0b6b7a;
            --brand-secondary: #0f4c75;
            --accent: #19b6d7;
        }
        body {
            min-height: 100vh;
            margin: 0;
            background: radial-gradient(circle at 20% 20%, rgba(25,182,215,0.18), transparent 35%),
                        radial-gradient(circle at 80% 10%, rgba(11,107,122,0.22), transparent 30%),
                        linear-gradient(145deg, #0b3354 0%, #0b6070 50%, #0b3354 100%);
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            font-family: "Inter", system-ui, -apple-system, sans-serif;
        }
        .auth-shell {
            width: min(1100px, 100%);
            background: rgba(255, 255, 255, 0.92);
            border-radius: 24px;
            box-shadow: 0 30px 80px -40px rgba(0,0,0,0.6);
            overflow: hidden;
        }
        .auth-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
        }
        .auth-hero {
            background: linear-gradient(150deg, #0d7a73 0%, #0b4d78 100%);
            color: #e7f7ff;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }
        .auth-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 70% 30%, rgba(255,255,255,0.12), transparent 55%);
            pointer-events: none;
        }
        .auth-hero h1 {
            font-weight: 800;
            letter-spacing: 0.4px;
        }
        .hero-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-top: 1.25rem;
        }
        .hero-badge {
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.25);
            color: #f8fbff;
            border-radius: 999px;
            padding: 0.35rem 0.9rem;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .auth-card {
            background: #fff;
            padding: 2.5rem;
        }
        .input-icon {
            position: relative;
        }
        .input-icon input {
            padding-left: 2.75rem;
            height: 50px;
        }
        .input-icon .icon {
            position: absolute;
            left: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 0.95rem;
        }
        .btn-primary {
            background: linear-gradient(120deg, var(--brand-primary), var(--accent));
            border: none;
        }
        .btn-outline-primary {
            border-color: var(--brand-primary);
            color: var(--brand-primary);
        }
        .btn-outline-primary:hover {
            background: var(--brand-primary);
            color: #fff;
        }
        .help-links a {
            color: var(--brand-secondary);
        }
        .sso-btn {
            border: 1px solid #d1d5db;
            color: #111827;
            background: #f8fafc;
        }
        .sso-btn:hover {
            border-color: var(--brand-primary);
            color: var(--brand-primary);
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(255,255,255,0.18);
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.95rem;
            color: #e9f7ff;
        }
        .brand-logo {
            height: 88px;
            filter: drop-shadow(0 10px 18px rgba(0,0,0,0.25));
        }
        @media (max-width: 992px) {
            .auth-grid {
                grid-template-columns: 1fr;
            }
            .auth-hero {
                border-bottom-left-radius: 0;
                border-bottom-right-radius: 0;
            }
            .auth-card {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <div class="auth-grid">
            <div class="auth-hero">
                <div class="text-center mb-3">
                    <img src="{{ asset('image/logo/Kemnaker_Logo_White.png') }}" alt="Kemnaker RI" class="brand-logo">
                </div>
                <h1 class="mt-3 mb-2 text-center">Satpel PVP Bantul<br>BPVP Surakarta<br>Dirjen Binalavottas<br>Kemnaker RI</h1>
                <p class="mb-4 text-white-75 text-center">Portal tunggal untuk peserta, instruktur, pimpinan balai, dan mitra: kelas, konten, dan kolaborasi Satpel PVP Bantul.</p>
                <div class="hero-badges">
                    <span class="hero-badge"><i class="fas fa-lock me-1"></i> 2FA siap pakai</span>
                    <span class="hero-badge"><i class="fas fa-bolt me-1"></i> Akses instan</span>
                    <span class="hero-badge"><i class="fas fa-users-cog me-1"></i> Role-based</span>
                </div>
            </div>
            <div class="auth-card">
                <div class="text-center mb-4"></div>

                @if(session('status'))
                    <div class="alert alert-success text-center small p-2">
                        {{ session('status') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger text-center small p-2">
                        {{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger text-center small p-2">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="mb-4">
                    <h4 class="fw-bold mb-1 text-center">Login dengan SIAP Kerja</h4>
                    <p class="text-muted small text-center mb-0">Portal ini hanya menerima autentikasi melalui akun SIAP Kerja.</p>
                </div>
                @php
                    $ssoClientId = \App\Models\SiteSetting::valueOf('siapkerja_client_id');
                    $ssoClientSecret = \App\Models\SiteSetting::valueOf('siapkerja_client_secret');
                    $ssoRedirect = \App\Models\SiteSetting::valueOf('siapkerja_redirect');
                    $hasSso = !empty($ssoClientId) && !empty($ssoClientSecret) && !empty($ssoRedirect);
                    if (! $hasSso) {
                        $ssoClientId = $ssoClientId ?: config('services.siapkerja.client_id');
                        $ssoClientSecret = $ssoClientSecret ?: config('services.siapkerja.client_secret');
                        $ssoRedirect = $ssoRedirect ?: config('services.siapkerja.redirect');
                        $hasSso = !empty($ssoClientId) && !empty($ssoClientSecret) && !empty($ssoRedirect);
                    }
                @endphp
                @if($hasSso)
                    <a href="{{ route('sso.siapkerja.redirect') }}" class="btn sso-btn w-100 py-3 fw-semibold">
                        <i class="fas fa-external-link-alt me-2"></i> Masuk dengan Akun SIAP Kerja
                    </a>
                @else
                    <div class="alert alert-warning text-center small">
                        Konfigurasi SIAP Kerja belum diisi. Lengkapi di Pengaturan Portal atau .env untuk mengaktifkan SSO.
                    </div>
                    <button type="button" class="btn sso-btn w-100 py-3" disabled title="Konfigurasi SIAPKerja belum diisi">
                        Masuk dengan Akun SIAP Kerja
                    </button>
                @endif
                
                <div class="text-center mt-3 help-links">
                    <a href="https://siapkerja.kemnaker.go.id" target="_blank" rel="noopener" class="text-decoration-none small d-block">Belum punya akun SIAP Kerja? Daftar di sini</a>
                    <a href="{{ route('admin.login') }}" class="text-decoration-none small d-block">Login Admin Manual</a>
                    <a href="{{ route('home') }}" class="text-decoration-none small">Kembali ke Website Utama</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
