<?php

namespace App\Services;

use App\Models\BrandingKpiReport;
use App\Models\BrandingKpiSyncLog;
use App\Models\SiteSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BrandingKpiSyncService
{
    public function syncAll(): array
    {
        $results = [
            $this->syncInstagram(),
            $this->syncGoogleMaps(),
            $this->syncYoutube(),
            $this->syncFacebook(),
        ];

        $successCount = collect($results)->where('status', 'success')->count();
        $failedCount = collect($results)->where('status', 'failed')->count();

        $message = sprintf(
            'Sinkron KPI selesai. Berhasil: %d, Gagal: %d, Dilewati: %d.',
            $successCount,
            $failedCount,
            collect($results)->where('status', 'skipped')->count()
        );

        $this->storeSyncStatus($failedCount === 0 ? 'success' : 'warning', $message);

        return [
            'status' => $failedCount === 0 ? 'success' : 'warning',
            'message' => $message,
            'results' => $results,
        ];
    }

    public function syncInstagram(): array
    {
        $token = trim((string) SiteSetting::valueOf('branding_kpi_instagram_access_token', ''));
        $userId = trim((string) SiteSetting::valueOf('branding_kpi_instagram_user_id', ''));
        $metric = trim((string) SiteSetting::valueOf('branding_kpi_instagram_metric', 'followers_count'));
        $indicatorKey = $this->resolveIndicatorKey('branding_kpi_map_instagram', 'reach');

        if ($token === '' || $userId === '') {
            $this->storeLog('instagram', 'skipped', 'Pengaturan Instagram belum lengkap. Sinkron otomatis dilewati.', $indicatorKey, $metric, null, null);
            return [
                'status' => 'skipped',
                'message' => 'Pengaturan Instagram belum lengkap. Isi Access Token dan User ID terlebih dahulu.',
            ];
        }

        $metricValue = $this->fetchInstagramMetric($userId, $token, $metric);
        if ($metricValue === null) {
            $this->storeLog('instagram', 'failed', 'Gagal mengambil data Instagram. Periksa token dan user ID.', $indicatorKey, $metric, null, null);
            return [
                'status' => 'failed',
                'message' => 'Gagal mengambil data Instagram. Periksa token, user ID, atau izin API.',
            ];
        }

        $this->updateCurrentMonthReport($indicatorKey, (float) $metricValue, 'Instagram');
        $this->storeLog('instagram', 'success', 'Sinkron KPI Instagram berhasil.', $indicatorKey, $metric, (float) $metricValue, ['user_id' => $userId]);

        return [
            'status' => 'success',
            'message' => 'Sinkron KPI Instagram berhasil.',
        ];
    }

    public function syncGoogleMaps(): array
    {
        $apiKey = trim((string) SiteSetting::valueOf('branding_kpi_google_api_key', ''));
        $placeId = trim((string) SiteSetting::valueOf('branding_kpi_google_place_id', ''));
        $metric = trim((string) SiteSetting::valueOf('branding_kpi_google_metric', 'rating'));
        $indicatorKey = $this->resolveIndicatorKey('branding_kpi_map_google', 'rating');

        if ($apiKey === '' || $placeId === '') {
            $this->storeLog('google_maps', 'skipped', 'Pengaturan Google Maps belum lengkap. Sinkron otomatis dilewati.', $indicatorKey, $metric, null, null);
            return [
                'status' => 'skipped',
                'message' => 'Pengaturan Google Maps belum lengkap.',
            ];
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $placeId,
            'fields' => 'rating,user_ratings_total',
            'key' => $apiKey,
        ]);

        if (! $response->ok() || ($response->json('status') !== 'OK')) {
            $this->storeLog('google_maps', 'failed', 'Gagal mengambil data Google Maps.', $indicatorKey, $metric, null, $response->json());
            return [
                'status' => 'failed',
                'message' => 'Gagal mengambil data Google Maps.',
            ];
        }

        $value = $metric === 'user_ratings_total'
            ? (float) $response->json('result.user_ratings_total')
            : (float) $response->json('result.rating');

        $this->updateCurrentMonthReport($indicatorKey, $value, 'Google Maps');
        $this->storeLog('google_maps', 'success', 'Sinkron KPI Google Maps berhasil.', $indicatorKey, $metric, $value, $response->json());

        return [
            'status' => 'success',
            'message' => 'Sinkron KPI Google Maps berhasil.',
        ];
    }

    public function syncYoutube(): array
    {
        $apiKey = trim((string) SiteSetting::valueOf('branding_kpi_youtube_api_key', ''));
        $channelId = trim((string) SiteSetting::valueOf('branding_kpi_youtube_channel_id', ''));
        $metric = trim((string) SiteSetting::valueOf('branding_kpi_youtube_metric', 'subscriberCount'));
        $indicatorKey = $this->resolveIndicatorKey('branding_kpi_map_youtube', 'reach');

        if ($apiKey === '' || $channelId === '') {
            $this->storeLog('youtube', 'skipped', 'Pengaturan YouTube belum lengkap. Sinkron otomatis dilewati.', $indicatorKey, $metric, null, null);
            return [
                'status' => 'skipped',
                'message' => 'Pengaturan YouTube belum lengkap.',
            ];
        }

        $response = Http::get('https://www.googleapis.com/youtube/v3/channels', [
            'part' => 'statistics',
            'id' => $channelId,
            'key' => $apiKey,
        ]);

        $statistics = $response->json('items.0.statistics');
        if (! $response->ok() || ! $statistics) {
            $this->storeLog('youtube', 'failed', 'Gagal mengambil data YouTube.', $indicatorKey, $metric, null, $response->json());
            return [
                'status' => 'failed',
                'message' => 'Gagal mengambil data YouTube.',
            ];
        }

        $value = (float) ($statistics[$metric] ?? 0);
        $this->updateCurrentMonthReport($indicatorKey, $value, 'YouTube');
        $this->storeLog('youtube', 'success', 'Sinkron KPI YouTube berhasil.', $indicatorKey, $metric, $value, $response->json());

        return [
            'status' => 'success',
            'message' => 'Sinkron KPI YouTube berhasil.',
        ];
    }

    public function syncFacebook(): array
    {
        $token = trim((string) SiteSetting::valueOf('branding_kpi_facebook_access_token', ''));
        $pageId = trim((string) SiteSetting::valueOf('branding_kpi_facebook_page_id', ''));
        $metric = trim((string) SiteSetting::valueOf('branding_kpi_facebook_metric', 'fan_count'));
        $indicatorKey = $this->resolveIndicatorKey('branding_kpi_map_facebook', 'reach');

        if ($token === '' || $pageId === '') {
            $this->storeLog('facebook', 'skipped', 'Pengaturan Facebook belum lengkap. Sinkron otomatis dilewati.', $indicatorKey, $metric, null, null);
            return [
                'status' => 'skipped',
                'message' => 'Pengaturan Facebook belum lengkap.',
            ];
        }

        $response = Http::get("https://graph.facebook.com/v19.0/{$pageId}", [
            'fields' => $metric,
            'access_token' => $token,
        ]);

        if (! $response->ok()) {
            $this->storeLog('facebook', 'failed', 'Gagal mengambil data Facebook.', $indicatorKey, $metric, null, $response->json());
            return [
                'status' => 'failed',
                'message' => 'Gagal mengambil data Facebook.',
            ];
        }

        $value = (float) ($response->json($metric) ?? 0);
        $this->updateCurrentMonthReport($indicatorKey, $value, 'Facebook');
        $this->storeLog('facebook', 'success', 'Sinkron KPI Facebook berhasil.', $indicatorKey, $metric, $value, $response->json());

        return [
            'status' => 'success',
            'message' => 'Sinkron KPI Facebook berhasil.',
        ];
    }

    private function fetchInstagramMetric(string $userId, string $token, string $metric): ?int
    {
        $response = Http::get("https://graph.facebook.com/v19.0/{$userId}", [
            'fields' => $metric,
            'access_token' => $token,
        ]);

        if ($response->ok()) {
            $value = $response->json($metric);
            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    private function updateCurrentMonthReport(string $indicatorKey, float $value, string $source): void
    {
        $now = Carbon::now();
        $report = BrandingKpiReport::firstOrNew([
            'month' => $now->month,
            'year' => $now->year,
        ]);

        $currentField = $indicatorKey . '_current';
        $previousField = $indicatorKey . '_previous';
        $notesField = $indicatorKey . '_notes';

        if (in_array($previousField, $report->getFillable(), true) && $report->{$previousField} === null) {
            $previous = BrandingKpiReport::where('year', $now->copy()->subMonth()->year)
                ->where('month', $now->copy()->subMonth()->month)
                ->first();
            if ($previous) {
                $report->{$previousField} = $previous->{$currentField};
            }
        }

        $report->{$currentField} = $value;
        if (in_array($notesField, $report->getFillable(), true) && empty($report->{$notesField})) {
            $report->{$notesField} = "Auto sync {$source}";
        }
        $report->save();
    }

    private function resolveIndicatorKey(string $settingKey, string $default): string
    {
        $indicator = SiteSetting::valueOf($settingKey, $default);
        $indicator = $indicator ?: $default;
        $allowed = ['reach', 'registrant', 'rating', 'partner'];
        return in_array($indicator, $allowed, true) ? $indicator : $default;
    }

    private function storeLog(
        string $platform,
        string $status,
        string $message,
        ?string $indicatorKey,
        ?string $metric,
        ?float $value,
        $payload
    ): void {
        BrandingKpiSyncLog::create([
            'platform' => $platform,
            'status' => $status,
            'message' => $message,
            'indicator_key' => $indicatorKey,
            'metric' => $metric,
            'metric_value' => $value,
            'payload' => $payload,
            'synced_at' => now(),
        ]);
    }

    private function storeSyncStatus(string $status, string $message): void
    {
        SiteSetting::updateOrCreate(
            ['key' => 'branding_kpi_last_sync_status'],
            ['value' => $status]
        );
        SiteSetting::updateOrCreate(
            ['key' => 'branding_kpi_last_sync_message'],
            ['value' => $message]
        );
        SiteSetting::updateOrCreate(
            ['key' => 'branding_kpi_last_sync_at'],
            ['value' => now()->format('Y-m-d H:i:s')]
        );

        Cache::forget('site_setting:branding_kpi_last_sync_status');
        Cache::forget('site_setting:branding_kpi_last_sync_message');
        Cache::forget('site_setting:branding_kpi_last_sync_at');
    }
}
