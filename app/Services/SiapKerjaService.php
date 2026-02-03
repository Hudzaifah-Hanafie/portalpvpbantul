<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SiapKerjaService
{
    private const TOKEN_CACHE_KEY = 'siapkerja.admin_token';

    public function fetchPrograms(): array
    {
        if ($this->hasServiceToken()) {
            return $this->serviceGet('program');
        }

        return $this->get('/programs');
    }

    public function fetchSchedules(string $programId): array
    {
        if ($this->hasServiceToken()) {
            $schedules = $this->serviceGet('schedule');
            return $this->filterByProgram($schedules, $programId);
        }

        return $this->get("/programs/{$programId}/batches");
    }

    public function fetchInstructors(): array
    {
        if ($this->hasServiceToken()) {
            return $this->serviceGet('instructor');
        }

        return $this->get('/instructors');
    }

    public function fetchParticipants(string $batchId): array
    {
        if ($this->hasServiceToken()) {
            $participants = $this->serviceGet('participant');
            return $this->filterByBatch($participants, $batchId);
        }

        return $this->get("/training/{$batchId}/participants");
    }

    private function get(string $path, array $query = []): array
    {
        $response = $this->http()->get(ltrim($path, '/'), $query);

        if ($response->failed()) {
            $response->throw();
        }

        return $response->json('data') ?? $response->json() ?? [];
    }

    private function http(): PendingRequest
    {
        $baseUrl = rtrim($this->setting('api_base') ?? config('services.siapkerja.api_base'), '/');

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->withToken($this->getAccessToken());
    }

    private function serviceGet(string $serviceKey, array $query = []): array
    {
        $serviceId = $this->serviceId($serviceKey);
        if (! $serviceId) {
            throw new RuntimeException("Service ID untuk {$serviceKey} belum diatur.");
        }

        $response = $this->serviceHttp()->get(
            ltrim($serviceId, '/'),
            array_merge($this->serviceFilters(), $query)
        );

        if ($response->failed()) {
            $response->throw();
        }

        return $response->json('data') ?? $response->json() ?? [];
    }

    private function serviceHttp(): PendingRequest
    {
        $baseUrl = rtrim(
            $this->setting('service_base') ?? config('services.siapkerja.service_base'),
            '/'
        );

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->withToken($this->serviceToken());
    }

    private function getAccessToken(): string
    {
        $cached = Cache::get(self::TOKEN_CACHE_KEY);
        if ($cached) {
            return $cached;
        }

        $response = Http::asJson()
            ->acceptJson()
            ->post($this->setting('token_url') ?? config('services.siapkerja.token_url'), [
                'grant_type' => 'client_credentials',
                'client_id' => $this->setting('admin_client_id') ?? config('services.siapkerja.admin_client_id'),
                'client_secret' => $this->setting('admin_client_secret') ?? config('services.siapkerja.admin_client_secret'),
                'scope' => $this->setting('admin_scope') ?? config('services.siapkerja.admin_scope', 'client'),
            ]);

        if ($response->failed()) {
            $response->throw();
        }

        $data = $response->json();
        $token = Arr::get($data, 'data.access_token') ?? Arr::get($data, 'access_token');
        if (! $token) {
            throw new RuntimeException('Token SIAP Kerja tidak ditemukan dalam response.');
        }

        $expiresIn = (int) (Arr::get($data, 'data.expires_in') ?? Arr::get($data, 'expires_in') ?? 3600);
        Cache::put(self::TOKEN_CACHE_KEY, $token, now()->addSeconds(max(60, $expiresIn - 60)));

        return $token;
    }

    private function hasServiceToken(): bool
    {
        return (bool) $this->serviceToken();
    }

    private function serviceToken(): ?string
    {
        $token = $this->setting('service_token') ?? config('services.siapkerja.service_token');
        if (! is_string($token)) {
            return null;
        }

        $token = trim($token);

        return $token !== '' ? $token : null;
    }

    private function serviceId(string $key): ?string
    {
        $settingKey = "service_{$key}_id";
        $value = $this->setting($settingKey) ?? config("services.siapkerja.{$settingKey}");

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    private function serviceFilters(): array
    {
        $filters = [
            'prov_code' => $this->setting('service_prov_code') ?? config('services.siapkerja.service_prov_code'),
            'city_code' => $this->setting('service_city_code') ?? config('services.siapkerja.service_city_code'),
            'vocational' => $this->setting('service_vocational') ?? config('services.siapkerja.service_vocational'),
            'sub_vocational' => $this->setting('service_sub_vocational') ?? config('services.siapkerja.service_sub_vocational'),
        ];

        $filters = array_filter($filters, function ($value) {
            return is_string($value) && trim($value) !== '';
        });

        return array_map('trim', $filters);
    }

    /**
     * @param array<int, mixed> $items
     * @return array<int, mixed>
     */
    private function filterByProgram(array $items, string $programId): array
    {
        $needle = trim($programId);
        if ($needle === '') {
            return $items;
        }

        $filtered = array_filter($items, function ($item) use ($needle) {
            if (! is_array($item)) {
                return false;
            }

            $candidates = [
                Arr::get($item, 'program_id'),
                Arr::get($item, 'programId'),
                Arr::get($item, 'program.id'),
                Arr::get($item, 'program.external_id'),
                Arr::get($item, 'program.externalId'),
            ];

            foreach ($candidates as $candidate) {
                if (is_string($candidate) && trim($candidate) === $needle) {
                    return true;
                }
            }

            return false;
        });

        return array_values($filtered);
    }

    /**
     * @param array<int, mixed> $items
     * @return array<int, mixed>
     */
    private function filterByBatch(array $items, string $batchId): array
    {
        $needle = trim($batchId);
        if ($needle === '') {
            return $items;
        }

        $filtered = array_filter($items, function ($item) use ($needle) {
            if (! is_array($item)) {
                return false;
            }

            $candidates = [
                Arr::get($item, 'batch_id'),
                Arr::get($item, 'batchId'),
                Arr::get($item, 'batch.id'),
                Arr::get($item, 'training_id'),
                Arr::get($item, 'trainingId'),
            ];

            foreach ($candidates as $candidate) {
                if (is_string($candidate) && trim($candidate) === $needle) {
                    return true;
                }
            }

            return false;
        });

        return array_values($filtered);
    }

    private function setting(string $key): ?string
    {
        $value = SiteSetting::valueOf("siapkerja_{$key}");
        if (is_string($value) && trim($value) === '') {
            return null;
        }

        return $value;
    }
}
