@extends('layouts.admin')

@section('content')
@php
    $profile = $user->profile;
    $rolesLabel = $user->roles->pluck('label')->filter()->implode(', ');
    $genderMap = [
        '10' => 'Laki-laki',
        '20' => 'Perempuan',
        '1' => 'Laki-laki',
        '2' => 'Perempuan',
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        'M' => 'Laki-laki',
        'F' => 'Perempuan',
    ];
    $genderValue = $profile?->gender;
    $genderKey = $genderValue !== null ? (string) $genderValue : null;
    $genderLabel = $genderKey && array_key_exists($genderKey, $genderMap) ? $genderMap[$genderKey] : ($genderKey ?: '-');
    $birthDateLabel = '-';
    if (! empty($profile?->birth_date)) {
        try {
            $birthDateLabel = \Illuminate\Support\Carbon::parse($profile->birth_date)->format('d M Y');
        } catch (\Throwable $exception) {
            $birthDateLabel = $profile->birth_date;
        }
    }
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 fade-up">
    <div>
        <h4 class="mb-1 section-title">Profil Saya</h4>
        <div class="section-subtitle">Data berikut diambil otomatis dari akun SIAP Kerja.</div>
    </div>
    <div class="text-muted small text-end">
        <div>Terakhir sinkron: {{ $profile?->synced_at?->format('d M Y H:i') ?? '-' }}</div>
        <div>Peran: {{ $rolesLabel ?: '-' }}</div>
        <form action="{{ route('profile.sync') }}" method="POST" class="mt-2">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary">Sinkronkan</button>
        </form>
    </div>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if(session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
@endif

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-bold">Ringkasan Akun</h6>
                <div class="d-flex align-items-center gap-3 mt-3">
                    <img src="{{ $profile?->picture_uri ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name ?? 'P') }}" class="rounded-circle" width="54" height="54" alt="Avatar">
                    <div>
                        <div class="fw-semibold">{{ $user->name ?? '-' }}</div>
                        <small class="text-muted">{{ $user->email ?? '-' }}</small>
                    </div>
                </div>
                <div class="mt-3 small text-muted">NIK</div>
                <div class="fw-semibold">{{ $user->nik ?? '-' }}</div>
                <div class="mt-3 small text-muted">Terhubung Sejak</div>
                <div class="fw-semibold">{{ $user->created_at?->format('d M Y') ?? '-' }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h6 class="fw-bold">Informasi Pribadi</h6>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <div class="text-muted small">Nomor HP</div>
                        <div class="fw-semibold">{{ $profile?->phone ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Jenis Kelamin</div>
                        <div class="fw-semibold">{{ $genderLabel }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Tempat Lahir</div>
                        <div class="fw-semibold">{{ $profile?->place_of_birth ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Tanggal Lahir</div>
                        <div class="fw-semibold">{{ $birthDateLabel }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold">Alamat & Domisili</h6>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <div class="text-muted small">Alamat</div>
                        <div class="fw-semibold">{{ $profile?->address ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Alamat Domisili</div>
                        <div class="fw-semibold">{{ $profile?->domicile_address ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-4">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold">Riwayat Pendidikan</h6>
                @forelse($user->educations as $education)
                    <div class="mb-3">
                        <div class="fw-semibold">{{ $education->institution ?? '-' }}</div>
                        <div class="text-muted small">{{ $education->degree ?? '-' }} • {{ $education->year_start ?? '-' }} - {{ $education->year_end ?? '-' }}</div>
                    </div>
                @empty
                    <div class="text-muted small">Belum ada data pendidikan.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold">Pengalaman Kerja</h6>
                @forelse($user->experiences as $experience)
                    <div class="mb-3">
                        <div class="fw-semibold">{{ $experience->company_name ?? '-' }}</div>
                        <div class="text-muted small">{{ $experience->position ?? '-' }} • {{ $experience->year_start ?? '-' }} - {{ $experience->year_end ?? '-' }}</div>
                    </div>
                @empty
                    <div class="text-muted small">Belum ada data pengalaman.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-4">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold">Pelatihan</h6>
                @forelse($user->trainings as $training)
                    <div class="mb-3">
                        <div class="fw-semibold">{{ $training->program_name ?? '-' }}</div>
                        <div class="text-muted small">{{ $training->institution_name ?? '-' }}</div>
                    </div>
                @empty
                    <div class="text-muted small">Belum ada data pelatihan.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold">Sertifikasi</h6>
                @forelse($user->certifications as $certification)
                    <div class="mb-3">
                        <div class="fw-semibold">{{ $certification->program_name ?? '-' }}</div>
                        <div class="text-muted small">{{ $certification->institution_name ?? '-' }}</div>
                    </div>
                @empty
                    <div class="text-muted small">Belum ada data sertifikasi.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-4">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold">Keterampilan</h6>
                @forelse($user->skills as $skill)
                    <span class="badge text-bg-light border me-1 mb-1">{{ $skill->name ?? $skill->skill ?? '-' }}</span>
                @empty
                    <div class="text-muted small">Belum ada data keterampilan.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold">Bahasa</h6>
                @forelse($user->languages as $language)
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ $language->name ?? $language->language ?? '-' }}</span>
                        <span class="text-muted">{{ $language->level ?? $language->proficiency ?? '-' }}</span>
                    </div>
                @empty
                    <div class="text-muted small">Belum ada data bahasa.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
