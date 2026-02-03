<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SiapKerjaProfileService
{
    private const DEFAULT_BASE_URL = 'https://api.kemnaker.go.id/profile';
    private const MAX_PAGES = 50;
    private const PAGE_LIMIT = 100;

    public function fetchProfile(
        string $accessToken,
        ?string $identityNumber = null,
        ?string $email = null,
        ?string $nacoUserId = null,
        ?string $phone = null
    ): array
    {
        $hasIdentity = ! empty($identityNumber);
        $basePayload = [];
        if ($hasIdentity) {
            $basePayload['identity_number'] = $identityNumber;
            $basePayload['nik'] = $identityNumber;
        }
        if (! empty($email)) {
            $basePayload['email'] = $email;
        }
        if (! empty($nacoUserId)) {
            $basePayload['naco_user_id'] = $nacoUserId;
        }
        if (! empty($phone)) {
            $basePayload['phone'] = $phone;
        }

        $candidates = [
            ['method' => 'post', 'path' => 'v1/profiles/me', 'query' => [], 'payload' => $basePayload],
            ['method' => 'post', 'path' => 'v1/profiles', 'query' => ['me' => 1], 'payload' => array_merge($basePayload, ['me' => 1])],
            ['method' => 'post', 'path' => 'v1/profiles', 'query' => ['self' => 1], 'payload' => array_merge($basePayload, ['self' => 1])],
        ];
        $hasSearchIdentity = $hasIdentity && $email && $nacoUserId && $phone;

        if ($hasSearchIdentity) {
            $payload = [
                'identity_number' => $identityNumber,
                'nik' => $identityNumber,
                'email' => $email,
                'naco_user_id' => $nacoUserId,
                'phone' => $phone,
            ];
            $query = [];
            $query['email'] = $email;
            $query['naco_user_id'] = $nacoUserId;
            $query['phone'] = $phone;
            $query['identity_number'] = $identityNumber;
            $candidates[] = [
                'method' => 'post',
                'path' => 'v1/profiles',
                'query' => $query,
                'payload' => $payload,
            ];
            $candidates[] = [
                'method' => 'post',
                'path' => 'v1/profiles',
                'query' => $query,
                'payload' => $payload,
            ];
        }
        if (! $hasIdentity && $email && $nacoUserId && $phone) {
            $candidates[] = [
                'method' => 'post',
                'path' => 'v1/profiles',
                'query' => [],
                'payload' => [
                    'email' => $email,
                    'naco_user_id' => $nacoUserId,
                    'phone' => $phone,
                ],
            ];
        }

        $lastException = null;
        $hadSuccessfulResponse = false;

        foreach ($candidates as $candidate) {
            try {
                $payload = $this->requestRaw(
                    $accessToken,
                    $candidate['method'],
                    $candidate['path'],
                    $candidate['query'],
                    $candidate['payload']
                );
                $hadSuccessfulResponse = true;
                $payload = $this->normalizeProfilePayload($payload);

                $data = Arr::get($payload, 'data');
                if (! empty($data)) {
                    return $payload;
                }
            } catch (RequestException $exception) {
                $lastException = $exception;
                $status = $exception->response?->status();
                if ($status === 401) {
                    throw $exception;
                }
                continue;
            } catch (\Throwable $exception) {
                $lastException = $exception;
                continue;
            }
        }

        if ($hadSuccessfulResponse) {
            return [];
        }
        if ($lastException) {
            throw $lastException;
        }

        return [];
    }

    public function fetchProfileById(string $accessToken, string $profileId): array
    {
        $payload = $this->getRaw($accessToken, "v1/profiles/{$profileId}");
        return $this->normalizeProfilePayload($payload);
    }

    public function fetchExperiences(string $accessToken, string $profileId): array
    {
        return $this->fetchAll($accessToken, "v1/profiles/{$profileId}/experiences");
    }

    public function fetchTrainings(string $accessToken, string $profileId): array
    {
        return $this->fetchAll($accessToken, "v1/profiles/{$profileId}/trainings");
    }

    public function fetchCertifications(string $accessToken, string $profileId): array
    {
        return $this->fetchAll($accessToken, "v1/profiles/{$profileId}/certifications");
    }

    public function fetchSkills(string $accessToken, string $profileId): array
    {
        return $this->fetchAll($accessToken, "v1/profiles/{$profileId}/skills");
    }

    public function fetchLanguages(string $accessToken, string $profileId): array
    {
        return $this->fetchAll($accessToken, "v2/profiles/{$profileId}/languages");
    }

    public function fetchEducations(string $accessToken, string $profileId): array
    {
        return $this->fetchAll($accessToken, "v1/profiles/{$profileId}/educations");
    }

    private function fetchAll(string $accessToken, string $path): array
    {
        $items = [];
        $page = 1;
        $offset = 0;
        $mode = null;

        for ($attempt = 0; $attempt < self::MAX_PAGES; $attempt++) {
            $query = ['limit' => self::PAGE_LIMIT];
            if ($mode === 'page') {
                $query['page'] = $page;
            } elseif ($mode === 'offset') {
                $query['offset'] = $offset;
            }

            $payload = $this->getRaw($accessToken, $path, $query);
            $data = Arr::get($payload, 'data', []);
            if (! is_array($data)) {
                $data = [];
            }

            $items = array_merge($items, $data);
            $meta = Arr::get($payload, 'meta', []);

            if ($mode === null) {
                if (array_key_exists('current_page', $meta) || array_key_exists('last_page', $meta)) {
                    $mode = 'page';
                } elseif (array_key_exists('total', $meta) && (array_key_exists('offset', $meta) || array_key_exists('limit', $meta))) {
                    $mode = 'offset';
                } else {
                    break;
                }
            }

            if ($mode === 'page') {
                $currentPage = Arr::get($meta, 'current_page') ?? Arr::get($meta, 'page') ?? $page;
                $lastPage = Arr::get($meta, 'last_page') ?? Arr::get($meta, 'total_pages');
                if ($lastPage !== null && $currentPage >= $lastPage) {
                    break;
                }
                if (empty($data)) {
                    break;
                }
                $page = $currentPage + 1;
                continue;
            }

            if ($mode === 'offset') {
                $total = Arr::get($meta, 'total');
                $offset += count($data);
                if ($total !== null && $offset >= $total) {
                    break;
                }
                if (empty($data)) {
                    break;
                }
                continue;
            }
        }

        return $items;
    }

    private function getRaw(string $accessToken, string $path, array $query = []): array
    {
        return $this->requestRaw($accessToken, 'get', $path, $query, []);
    }

    private function requestRaw(
        string $accessToken,
        string $method,
        string $path,
        array $query = [],
        array $payload = []
    ): array {
        $method = strtolower($method);
        $path = ltrim($path, '/');
        $request = $this->http($accessToken);

        if ($method === 'get') {
            $response = $request->get($path, $query);
        } elseif ($method === 'post') {
            $response = $request
                ->asForm()
                ->withOptions(['query' => $query])
                ->post($path, $payload);
        } else {
            throw new RuntimeException("Unsupported HTTP method: {$method}");
        }

        if ($response->failed()) {
            $response->throw();
        }

        return $response->json() ?? [];
    }

    private function normalizeProfilePayload(array $payload): array
    {
        if (empty($payload)) {
            return $payload;
        }

        if (array_is_list($payload)) {
            return ['data' => $payload[0] ?? null];
        }

        $data = Arr::get($payload, 'data');
        if (is_array($data) && array_is_list($data)) {
            $payload['data'] = $data[0] ?? null;
        }

        if (! array_key_exists('data', $payload)) {
            if (array_key_exists('profile', $payload)) {
                $payload['data'] = $payload['profile'];
            } elseif (array_key_exists('result', $payload)) {
                $payload['data'] = $payload['result'];
            } elseif (array_key_exists('user_profile', $payload)) {
                $payload['data'] = $payload['user_profile'];
            }
        }

        if (! empty($payload) && array_key_exists('id', $payload)) {
            $payload = ['data' => $payload];
        }

        return $payload;
    }

    private function http(string $accessToken): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl(), '/'))
            ->acceptJson()
            ->withToken($accessToken);
    }

    private function baseUrl(): string
    {
        $value = SiteSetting::valueOf('siapkerja_profile_api_base');
        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        $base = config('services.siapkerja.profile_api_base', self::DEFAULT_BASE_URL);
        if (! $base) {
            throw new RuntimeException('Base URL API profil SIAP Kerja belum dikonfigurasi.');
        }

        return $base;
    }
}
