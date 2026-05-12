@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1">Integrasi & Sinkron KPI</h3>
        <p class="text-muted mb-0">Konfigurasi API dan riwayat sinkron harian KPI branding.</p>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.branding-kpi.sync') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-primary">
                <i class="fas fa-rotate me-1"></i> Sinkron Sekarang
            </button>
        </form>
        <a href="{{ route('admin.branding-kpi.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<ul class="nav nav-pills mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-settings" type="button" role="tab">Konfigurasi API</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-logs" type="button" role="tab">Log Sinkron</button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="tab-settings" role="tabpanel">
        <form action="{{ route('admin.branding-kpi.settings') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-12">
                <h5 class="mb-2">Instagram</h5>
            </div>
            <div class="col-md-5">
                <label class="form-label">Token Akses</label>
                <input type="password" name="instagram_access_token" class="form-control" value="{{ $integrationSettings['instagram_access_token'] }}" placeholder="Masukkan token akses">
            </div>
            <div class="col-md-3">
                <label class="form-label">ID Pengguna</label>
                <input type="text" name="instagram_user_id" class="form-control" value="{{ $integrationSettings['instagram_user_id'] }}" placeholder="Contoh: 1789...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Metrik</label>
                <select name="instagram_metric" class="form-select">
                    <option value="followers_count" {{ $integrationSettings['instagram_metric'] === 'followers_count' ? 'selected' : '' }}>Pengikut</option>
                    <option value="media_count" {{ $integrationSettings['instagram_metric'] === 'media_count' ? 'selected' : '' }}>Total Konten</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Pemetaan KPI</label>
                <select name="map_instagram" class="form-select">
                    @foreach($indicatorDefinitions as $key => $indicator)
                        <option value="{{ $key }}" {{ $integrationSettings['map_instagram'] === $key ? 'selected' : '' }}>
                            {{ $indicator['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 mt-4">
                <h5 class="mb-2">Google Maps</h5>
            </div>
            <div class="col-md-5">
                <label class="form-label">Kunci API</label>
                <input type="password" name="google_api_key" class="form-control" value="{{ $integrationSettings['google_api_key'] }}" placeholder="Masukkan kunci API">
            </div>
            <div class="col-md-3">
                <label class="form-label">ID Tempat</label>
                <input type="text" name="google_place_id" class="form-control" value="{{ $integrationSettings['google_place_id'] }}" placeholder="Contoh: ChIJ...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Metrik</label>
                <select name="google_metric" class="form-select">
                    <option value="rating" {{ $integrationSettings['google_metric'] === 'rating' ? 'selected' : '' }}>Rating</option>
                    <option value="user_ratings_total" {{ $integrationSettings['google_metric'] === 'user_ratings_total' ? 'selected' : '' }}>Jumlah Ulasan</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Pemetaan KPI</label>
                <select name="map_google" class="form-select">
                    @foreach($indicatorDefinitions as $key => $indicator)
                        <option value="{{ $key }}" {{ $integrationSettings['map_google'] === $key ? 'selected' : '' }}>
                            {{ $indicator['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 mt-4">
                <h5 class="mb-2">YouTube</h5>
            </div>
            <div class="col-md-5">
                <label class="form-label">Kunci API</label>
                <input type="password" name="youtube_api_key" class="form-control" value="{{ $integrationSettings['youtube_api_key'] }}" placeholder="Masukkan kunci API">
            </div>
            <div class="col-md-3">
                <label class="form-label">ID Kanal</label>
                <input type="text" name="youtube_channel_id" class="form-control" value="{{ $integrationSettings['youtube_channel_id'] }}" placeholder="Contoh: UC...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Metrik</label>
                <select name="youtube_metric" class="form-select">
                    <option value="subscriberCount" {{ $integrationSettings['youtube_metric'] === 'subscriberCount' ? 'selected' : '' }}>Subskriber</option>
                    <option value="viewCount" {{ $integrationSettings['youtube_metric'] === 'viewCount' ? 'selected' : '' }}>Tayangan</option>
                    <option value="videoCount" {{ $integrationSettings['youtube_metric'] === 'videoCount' ? 'selected' : '' }}>Jumlah Video</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Pemetaan KPI</label>
                <select name="map_youtube" class="form-select">
                    @foreach($indicatorDefinitions as $key => $indicator)
                        <option value="{{ $key }}" {{ $integrationSettings['map_youtube'] === $key ? 'selected' : '' }}>
                            {{ $indicator['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 mt-4">
                <h5 class="mb-2">Facebook</h5>
            </div>
            <div class="col-md-5">
                <label class="form-label">Token Akses</label>
                <input type="password" name="facebook_access_token" class="form-control" value="{{ $integrationSettings['facebook_access_token'] }}" placeholder="Masukkan token akses">
            </div>
            <div class="col-md-3">
                <label class="form-label">ID Halaman</label>
                <input type="text" name="facebook_page_id" class="form-control" value="{{ $integrationSettings['facebook_page_id'] }}" placeholder="Contoh: 123...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Metrik</label>
                <select name="facebook_metric" class="form-select">
                    <option value="fan_count" {{ $integrationSettings['facebook_metric'] === 'fan_count' ? 'selected' : '' }}>Jumlah Penggemar</option>
                    <option value="followers_count" {{ $integrationSettings['facebook_metric'] === 'followers_count' ? 'selected' : '' }}>Pengikut</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Pemetaan KPI</label>
                <select name="map_facebook" class="form-select">
                    @foreach($indicatorDefinitions as $key => $indicator)
                        <option value="{{ $key }}" {{ $integrationSettings['map_facebook'] === $key ? 'selected' : '' }}>
                            {{ $indicator['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>

    <div class="tab-pane fade" id="tab-logs" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Platform</th>
                            <th>Status</th>
                            <th>Pemetaan KPI</th>
                            <th>Metrik</th>
                            <th>Nilai</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ optional($log->synced_at)->format('d M Y H:i') ?? '-' }}</td>
                                <td>{{ strtoupper(str_replace('_', ' ', $log->platform)) }}</td>
                                <td>
                                    <span class="badge bg-{{ $log->status === 'success' ? 'success' : ($log->status === 'failed' ? 'danger' : 'secondary') }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td>{{ $indicatorDefinitions[$log->indicator_key]['label'] ?? '-' }}</td>
                                <td>{{ $log->metric ?? '-' }}</td>
                                <td>{{ $log->metric_value ?? '-' }}</td>
                                <td>{{ $log->message ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada log sinkron.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
