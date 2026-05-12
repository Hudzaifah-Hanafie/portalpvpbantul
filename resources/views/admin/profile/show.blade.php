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

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h4 class="mb-1">Profil Saya</h4>
        <small class="text-muted">Data berikut diambil otomatis dari akun SIAP Kerja.</small>
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

@if(! $profile)
    <div class="alert alert-warning">
        Profil SIAP Kerja belum tersedia. Klik Sinkronkan atau logout dan login kembali untuk menarik data terbaru.
        Jika masih kosong, minta admin memastikan scope SSO berisi <strong>basic email</strong> dan Profile API Base URL sudah benar.
    </div>
@endif
@if(session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif
@if(! $profile?->phone)
    <div class="alert alert-info">
        Nomor HP diperlukan untuk sinkronisasi profil SIAP Kerja. Silakan isi sekali agar data profil bisa ditarik.
        <form action="{{ route('profile.phone.update') }}" method="POST" class="row g-2 mt-2">
            @csrf
            <div class="col-md-6">
                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $profile?->phone) }}" placeholder="Contoh: 08123456789">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-sm btn-primary">Simpan Nomor HP</button>
            </div>
        </form>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Ringkasan Akun</h5>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center" style="width:64px;height:64px;overflow:hidden;">
                        @if($profile?->picture_uri)
                            <img src="{{ $profile->picture_uri }}" alt="Foto profil" class="img-fluid">
                        @else
                            <i class="fas fa-user text-muted fa-2x"></i>
                        @endif
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $user->name }}</div>
                        <div class="text-muted small">{{ $user->email }}</div>
                    </div>
                </div>
                <div class="small text-muted">NIK</div>
                <div class="fw-semibold mb-2">{{ $user->nik ?? '-' }}</div>
                <div class="small text-muted">Terhubung Sejak</div>
                <div class="fw-semibold">{{ $user->created_at?->format('d M Y') ?? '-' }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Informasi Pribadi</h5>
                <div class="row g-3">
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
                        <div class="fw-semibold">{{ $profile?->birth_place ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Tanggal Lahir</div>
                        <div class="fw-semibold">{{ $birthDateLabel }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Alamat & Domisili</h5>
                <div class="row g-3">
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

<div class="mt-5">
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Riwayat Pendidikan</h5>
                    @if($user->educations->isEmpty())
                        <div class="text-muted small">Belum ada data pendidikan.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Institusi</th>
                                        <th>Jenjang</th>
                                        <th>Tahun</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->educations as $education)
                                        <tr>
                                            <td>{{ $education->school_name ?? '-' }}</td>
                                            <td>{{ $education->graduate ?? '-' }}</td>
                                            <td>{{ $education->start_year ?? '-' }} - {{ $education->finish_year ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Pengalaman Kerja</h5>
                    @if($user->experiences->isEmpty())
                        <div class="text-muted small">Belum ada data pengalaman.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Perusahaan</th>
                                        <th>Posisi</th>
                                        <th>Tahun</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->experiences as $experience)
                                        <tr>
                                            <td>{{ $experience->company_name ?? '-' }}</td>
                                            <td>{{ $experience->position ?? '-' }}</td>
                                            <td>{{ $experience->start_year ?? '-' }} - {{ $experience->finish_year ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Pelatihan</h5>
                    @if($user->trainings->isEmpty())
                        <div class="text-muted small">Belum ada data pelatihan.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Program</th>
                                        <th>Lembaga</th>
                                        <th>Tahun</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->trainings as $training)
                                        <tr>
                                            <td>{{ $training->training_program ?? '-' }}</td>
                                            <td>{{ $training->training_center ?? '-' }}</td>
                                            <td>{{ $training->start_year ?? '-' }} - {{ $training->finish_year ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Sertifikasi</h5>
                    @if($user->certifications->isEmpty())
                        <div class="text-muted small">Belum ada data sertifikasi.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Program</th>
                                        <th>Lembaga</th>
                                        <th>Tahun</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->certifications as $certification)
                                        <tr>
                                            <td>{{ $certification->program_name ?? '-' }}</td>
                                            <td>{{ $certification->institution_name ?? '-' }}</td>
                                            <td>{{ $certification->issued_year ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
