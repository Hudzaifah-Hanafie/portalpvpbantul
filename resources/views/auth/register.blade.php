<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Satpel PVP Bantul</title>
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
            <div class="auth-hero text-center">
                <div class="d-flex justify-content-center mb-3">
                    <img src="{{ asset('image/logo/Kemnaker_Logo_White.png') }}" alt="Kemnaker RI" class="brand-logo">
                </div>
                <div class="text-white fw-semibold" style="line-height:1.5; font-size:1.05rem;">
                    Satpel PVP Bantul<br>
                    BPVP Surakarta<br>
                    Dirjen Binalavottas<br>
                    Kemnaker RI
                </div>
                <h2 class="mt-3 mb-2 h4 text-white">Gabung ekosistem pelatihan & kolaborasi</h2>
                <p class="mb-0 text-white-75">Akses tunggal untuk peserta, instruktur, pimpinan balai, dan mitra guna mengikuti kelas, forum, serta informasi terbaru Satpel PVP Bantul.</p>
            </div>
            <div class="auth-card">
                <div class="text-center mb-4"></div>

                <div class="mb-4 text-center">
                    <h4 class="fw-bold mb-1">Registrasi melalui SIAP Kerja</h4>
                    <p class="text-muted small mb-0">Pendaftaran manual dinonaktifkan. Gunakan akun SIAP Kerja untuk mengakses portal.</p>
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
                    <a href="{{ route('sso.siapkerja.redirect') }}" class="btn btn-primary w-100 py-3">Masuk dengan Akun SIAP Kerja</a>
                @else
                    <div class="alert alert-warning text-center small">Konfigurasi SIAP Kerja belum tersedia. Lengkapi di Pengaturan Portal atau hubungi admin.</div>
                @endif
                <div class="text-center mt-3 help-links">
                    <a href="{{ route('login') }}" class="text-decoration-none small">Kembali ke halaman login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
