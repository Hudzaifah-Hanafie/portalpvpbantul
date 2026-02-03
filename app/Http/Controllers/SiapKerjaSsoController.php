<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Models\User;
use App\Models\UserCertification;
use App\Models\UserEducation;
use App\Models\UserExperience;
use App\Models\UserLanguage;
use App\Models\UserProfile;
use App\Models\UserSkill;
use App\Models\UserTraining;
use App\Models\Role;
use App\Services\SiapKerjaProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SiapKerjaSsoController extends Controller
{
    private const AUTH_URL = 'https://account.kemnaker.go.id/auth';
    private const TOKEN_URL = 'https://account.kemnaker.go.id/api/v1/tokens';
    private const PROFILE_URL = 'https://account.kemnaker.go.id/api/v1/users/me';

    public function redirect(bool $preserveIntended = false): RedirectResponse
    {
        $state = Str::random(40);
        session(['siapkerja_state' => $state]);
        if (! $preserveIntended) {
            session()->forget('siapkerja_intended');
        }
        $redirectUri = $this->normalizeRedirectUri(
            $this->setting('redirect', config('services.siapkerja.redirect'))
        );

        $scope = $this->normalizeScope(
            $this->setting('scope', config('services.siapkerja.scope', 'basic email'))
        );
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->setting('client_id', config('services.siapkerja.client_id')),
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'state' => $state,
        ]);

        return redirect()->away(self::AUTH_URL . '?' . $query);
    }

    public function sync(Request $request): RedirectResponse
    {
        $request->session()->put('siapkerja_intended', route('profile.show'));

        return $this->redirect(true);
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('login')->withErrors($request->get('error_description', 'SSO dibatalkan.'));
        }

        $requestState = $request->input('state');
        $sessionState = session('siapkerja_state');
        if (! $sessionState || $requestState !== $sessionState) {
            $alreadyRetried = (bool) session()->pull('siapkerja_state_retry', false);
            session()->forget('siapkerja_state');
            Log::warning('State SSO tidak valid, mencoba ulang login.', [
                'expected' => $sessionState,
                'actual' => $requestState,
            ]);

            if (! $alreadyRetried) {
                session(['siapkerja_state_retry' => true]);
                return $this->redirect(true);
            }

            return redirect()->route('login')->withErrors('State SSO tidak valid. Silakan login kembali.');
        }
        session()->forget(['siapkerja_state', 'siapkerja_state_retry']);

        $code = $request->input('code');
        if (! $code) {
            return redirect()->route('login')->withErrors('Kode otorisasi tidak ditemukan.');
        }

        $tokenResponse = Http::asJson()
            ->acceptJson()
            ->post($this->setting('token_url', config('services.siapkerja.token_url', self::TOKEN_URL)), [
                'client_id' => $this->setting('client_id', config('services.siapkerja.client_id')),
                'client_secret' => $this->setting('client_secret', config('services.siapkerja.client_secret')),
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->normalizeRedirectUri(
                    $this->setting('redirect', config('services.siapkerja.redirect'))
                ),
            ]);

        if ($tokenResponse->failed()) {
            return redirect()->route('login')->withErrors('Gagal menukar token SIAPKerja.');
        }

        $tokenData = $tokenResponse->json() ?? [];
        $accessToken = $this->extractAccessToken($tokenData);
        if (! $accessToken) {
            return redirect()->route('login')->withErrors('Token akses SIAPKerja tidak ditemukan.');
        }

        $accountPayload = null;
        $accountData = [];
        $tokenPayload = $this->extractTokenPayload($tokenData, $accessToken);
        $tokenIdentityRaw = $this->firstFilled($tokenData, [
            'data.user.profile.identity_number',
            'data.user.profile.nik',
            'data.user.profile.identityNumber',
            'data.user.profile.nik_number',
            'data.user.profile.user.identity_number',
            'data.user.profile.user.nik',
            'data.user.profile.user.identityNumber',
            'data.user.identity_number',
            'data.user.nik',
            'data.user.identityNumber',
            'data.profile.identity_number',
            'data.profile.nik',
            'data.profile.identityNumber',
            'data.identity_number',
            'data.nik',
            'data.identityNumber',
            'data.user_profile.identity_number',
            'data.user_profile.nik',
            'data.user_profile.identityNumber',
            'data.user_profile.user.identity_number',
            'data.user_profile.user.nik',
            'data.user_profile.user.identityNumber',
            'data.userProfile.identity_number',
            'data.userProfile.nik',
            'data.userProfile.identityNumber',
            'data.userProfile.user.identity_number',
            'data.userProfile.user.nik',
            'data.userProfile.user.identityNumber',
            'user.profile.identity_number',
            'user.profile.nik',
            'user.profile.identityNumber',
            'user.identity_number',
            'user.nik',
            'user.identityNumber',
            'profile.identity_number',
            'profile.nik',
            'profile.identityNumber',
            'identity_number',
            'nik',
            'identityNumber',
            'data.user.username',
            'data.username',
            'user.username',
            'username',
        ]) ?? $this->firstFilled($tokenPayload, [
            'identity_number',
            'nik',
            'username',
            'preferred_username',
            'user.username',
            'user.nik',
            'user.identity_number',
            'sub',
        ]);
        $tokenIdentity = $this->normalizeIdentityNumber($tokenIdentityRaw);
        $tokenEmail = $this->firstFilled($tokenData, [
            'data.user.profile.user.email',
            'data.user.profile.email',
            'data.user.email',
            'data.profile.user.email',
            'data.profile.email',
            'data.email',
            'data.user_profile.user.email',
            'data.user_profile.email',
            'data.userProfile.user.email',
            'data.userProfile.email',
            'user.profile.user.email',
            'user.profile.email',
            'user.email',
            'profile.user.email',
            'profile.email',
            'email',
        ]) ?? $this->firstFilled($tokenPayload, [
            'email',
            'user.email',
        ]);
        if (! $tokenEmail) {
            $tokenEmailCandidate = $this->firstFilled($tokenData, [
                'data.user.username',
                'data.username',
                'user.username',
                'username',
            ]) ?? data_get($tokenPayload, 'preferred_username')
                ?? data_get($tokenPayload, 'username');
            if (is_string($tokenEmailCandidate) && str_contains($tokenEmailCandidate, '@')) {
                $tokenEmail = $tokenEmailCandidate;
            }
        }
        $tokenName = $this->firstFilled($tokenData, [
            'data.user.profile.name',
            'data.user.name',
            'data.profile.name',
            'data.name',
            'data.user_profile.name',
            'data.userProfile.name',
            'user.profile.name',
            'user.name',
            'profile.name',
            'name',
        ]) ?? data_get($tokenPayload, 'name')
            ?? data_get($tokenPayload, 'user.name');
        if (! $tokenName) {
            $givenName = data_get($tokenPayload, 'given_name');
            $familyName = data_get($tokenPayload, 'family_name');
            $tokenName = trim(trim((string) $givenName . ' ' . (string) $familyName));
            if ($tokenName === '') {
                $tokenName = null;
            }
        }
        $accountResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->get($this->setting('profile_url', config('services.siapkerja.profile_url', self::PROFILE_URL)));

        if ($accountResponse->ok()) {
            $accountPayload = $accountResponse->json() ?? [];
            $accountData = $accountPayload['data'] ?? $accountPayload;
        } else {
            Log::warning('Gagal mengambil profil SIAPKerja (account).', [
                'status' => $accountResponse->status(),
                'body' => $accountResponse->json() ?? $accountResponse->body(),
            ]);
        }
        if (empty($accountData)) {
            $accountDataCandidate = $this->firstFilled($tokenData, [
                'data.user',
                'data.user_profile',
                'data.userProfile',
                'user',
                'user_profile',
                'userProfile',
                'data.profile',
                'profile',
            ]);
            if (is_array($accountDataCandidate)) {
                $accountData = $accountDataCandidate;
            }
        }

        $emailCandidate = data_get($accountData, 'email')
            ?? data_get($accountData, 'user.email')
            ?? $tokenEmail;
        if (! $emailCandidate) {
            $usernameCandidate = data_get($accountData, 'username')
                ?? data_get($accountData, 'user.username')
                ?? $this->firstFilled($tokenData, [
                    'data.user.username',
                    'data.username',
                    'user.username',
                    'username',
                ]);
            if (is_string($usernameCandidate) && str_contains($usernameCandidate, '@')) {
                $emailCandidate = $usernameCandidate;
            }
        }
        $nacoUserId = $this->firstFilled($accountData, [
            'naco_user_id',
            'nacoUserId',
            'id',
            'user.id',
            'user_id',
            'user.profile.id',
            'profile.user.id',
            'profile.user_id',
        ]) ?? $this->firstFilled($tokenData, [
            'data.user.naco_user_id',
            'data.user.nacoUserId',
            'data.naco_user_id',
            'data.nacoUserId',
            'data.user.id',
            'data.user_id',
            'data.user.profile.id',
            'data.user.profile.user.id',
            'data.profile.user.id',
            'data.profile.user_id',
            'data.user_profile.user.id',
            'data.userProfile.user.id',
            'data.userProfile.user_id',
            'data.user_profile.user_id',
            'user.id',
            'user_id',
        ]);
        if ($nacoUserId !== null) {
            $nacoUserId = (string) $nacoUserId;
        }
        $phoneCandidateRaw = $this->extractPhoneDigits($this->firstFilled($accountData, [
            'phone',
            'phone_number',
            'mobile_phone',
            'mobile_number',
            'no_hp',
            'hp',
            'user.phone',
            'user.phone_number',
            'user.mobile_phone',
            'user.mobile_number',
            'profile.phone',
            'profile.phone_number',
            'user.profile.phone',
        ]) ?? $this->firstFilled($tokenData, [
            'data.user.phone',
            'data.user.phone_number',
            'data.user.mobile_phone',
            'data.user.mobile_number',
            'data.user.profile.phone',
            'data.user.profile.phone_number',
            'data.profile.phone',
            'data.profile.phone_number',
            'data.phone',
            'data.phone_number',
            'data.mobile_phone',
            'data.mobile_number',
            'data.no_hp',
            'data.hp',
            'user.phone',
            'profile.phone',
            'phone',
            'phone_number',
            'no_hp',
            'hp',
        ]));
        $phoneCandidate = $this->normalizePhone($phoneCandidateRaw);

        $profilePayload = null;
        $profileData = [];
        $profileId = $this->firstFilled($accountData, [
            'profile.id',
            'profile_id',
            'user.profile.id',
            'user.profile_id',
        ]);
        $tokenProfileId = $this->firstFilled($tokenData, [
            'data.user.profile.id',
            'data.user.profile_id',
            'data.user_profile.id',
            'data.user_profile.profile_id',
            'data.userProfile.id',
            'data.userProfile.profile_id',
            'data.profile.id',
            'data.profile_id',
            'profile.id',
            'profile_id',
        ]);
        if (! $profileId && $tokenProfileId) {
            $profileId = $tokenProfileId;
        }
        if ($profileId !== null) {
            $profileId = (string) $profileId;
        }
        $identityNumber = $this->normalizeIdentityNumber(
            data_get($accountData, 'username')
            ?? data_get($accountData, 'nik')
            ?? $tokenIdentity
        );

        $localUser = null;
        if ($identityNumber || $emailCandidate) {
            $localUserQuery = User::query();
            if ($identityNumber) {
                $localUserQuery->where('nik', $identityNumber);
            }
            if ($emailCandidate) {
                $localUserQuery->{$identityNumber ? 'orWhere' : 'where'}('email', $emailCandidate);
            }
            $localUser = $localUserQuery->first();
        }
        if (! $emailCandidate && $localUser && $localUser->email) {
            $emailCandidate = $localUser->email;
        }
        if (! $nacoUserId && $localUser && $localUser->siap_kerja_id) {
            $nacoUserId = (string) $localUser->siap_kerja_id;
        }
        if (! $phoneCandidateRaw && $localUser) {
            $phoneCandidateRaw = $this->extractPhoneDigits(data_get($localUser, 'profile.phone'));
            $phoneCandidate = $this->normalizePhone($phoneCandidateRaw);
        }
        if (! $phoneCandidate) {
            $phoneCandidate = null;
        }

        if (! $identityNumber) {
            Log::warning('NIK SIAPKerja tidak ditemukan pada token/akun. Mencoba ambil profil dari token.');
        }

        try {
            $profileService = app(SiapKerjaProfileService::class);
            if ($profileId) {
                $profilePayload = $profileService->fetchProfileById($accessToken, $profileId);
            } elseif ($identityNumber || ($emailCandidate && $nacoUserId)) {
                $profilePayload = $profileService->fetchProfile(
                    $accessToken,
                    $identityNumber,
                    $emailCandidate,
                    $nacoUserId,
                    $phoneCandidate ?? $phoneCandidateRaw
                );
            } else {
                $profilePayload = null;
            }
            if ($profilePayload) {
                $profileData = $profilePayload['data'] ?? $profilePayload;
                if (is_array($profileData) && array_is_list($profileData)) {
                    $profileData = $profileData[0] ?? [];
                }
                $profileId = data_get($profileData, 'id') ?? $profileId;
            }
        } catch (\Throwable $exception) {
            Log::warning('Gagal mengambil profil lengkap SIAPKerja.', [
                'error' => $exception->getMessage(),
                'identity_number_length' => $identityNumber ? strlen($identityNumber) : null,
            ]);
        }

        $email = data_get($profileData, 'user.email')
            ?? data_get($accountData, 'email')
            ?? data_get($accountData, 'user.email')
            ?? $tokenEmail;
        $nik = $this->normalizeIdentityNumber(data_get($profileData, 'identity_number'))
            ?? $this->normalizeIdentityNumber(data_get($accountData, 'nik'))
            ?? $this->normalizeIdentityNumber(data_get($accountData, 'username'))
            ?? $tokenIdentity;

        if (empty($accountData) && empty($profileData)) {
            if (! $nik && ! $email) {
                return redirect()->route('login')->withErrors('Gagal mengambil profil SIAPKerja.');
            }

            Log::warning('Profil SIAPKerja kosong, menggunakan klaim token untuk autentikasi.', [
                'token_identity_length' => $tokenIdentity ? strlen($tokenIdentity) : null,
                'token_email_present' => (bool) $tokenEmail,
            ]);
        }

        if (! $nik && ! $email) {
            return redirect()->route('login')->withErrors('Profil SIAPKerja tidak menyediakan identitas NIK atau email.');
        }

        $name = data_get($profileData, 'name')
            ?? data_get($profileData, 'user.name')
            ?? data_get($accountData, 'name')
            ?? $tokenName
            ?? ($email ?: 'Pengguna SIAPKerja');
        $siapKerjaId = data_get($accountData, 'id')
            ?? data_get($profileData, 'user.id');

        $existingUserQuery = User::query();
        if ($nik) {
            $existingUserQuery->where('nik', $nik);
        }
        if ($email) {
            $existingUserQuery->{$nik ? 'orWhere' : 'where'}('email', $email);
        }
        $existingUser = $existingUserQuery->first();

        $lookupAttributes = $existingUser
            ? ['id' => $existingUser->id]
            : ($nik ? ['nik' => $nik] : ['email' => $email]);

        $ssoPayload = ['account' => $accountPayload];
        if ($profilePayload) {
            $ssoPayload['profile'] = $profilePayload;
        }

        $user = User::updateOrCreate($lookupAttributes, [
            'name' => $name,
            'nik' => $nik,
            'email' => $email,
            'siap_kerja_id' => $siapKerjaId,
            'sso_payload' => $ssoPayload,
            'password' => Str::random(32), // dummy password because login is via SSO
            'email_verified_at' => now(),
        ]);

        if (! $user->roles()->exists()) {
            $participantRole = Role::where('name', 'participant')->first();
            if ($participantRole) {
                $user->roles()->syncWithoutDetaching([$participantRole->id]);
            }
        }

        $this->syncSiapKerjaProfile(
            $user,
            $accountData,
            $profilePayload,
            $profileData,
            $profileId,
            $accessToken
        );

        Auth::login($user, true);
        session()->regenerate();

        $intended = session()->pull('siapkerja_intended')
            ?? session()->pull('url.intended');
        return redirect()->to($intended ?? route('home'));
    }

    private function setting(string $key, ?string $default = null): ?string
    {
        $value = SiteSetting::valueOf("siapkerja_{$key}");
        if (is_string($value) && trim($value) === '') {
            $value = null;
        }

        return $value ?? $default;
    }

    private function normalizeRedirectUri(?string $uri): ?string
    {
        if (! $uri) {
            return $uri;
        }

        $uri = trim($uri);
        return preg_replace('~(?<!:)/{2,}~', '/', $uri);
    }

    private function normalizeScope(?string $scope): string
    {
        $tokens = preg_split('/\s+/', trim($scope ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $required = ['basic', 'email'];
        $blocked = ['profile'];

        $normalized = $required;
        foreach ($tokens as $token) {
            if (in_array($token, $blocked, true)) {
                continue;
            }
            if (! in_array($token, $required, true)) {
                $normalized[] = $token;
            }
        }

        return implode(' ', array_unique($normalized));
    }

    private function firstFilled(array $source, array $paths): mixed
    {
        foreach ($paths as $path) {
            $value = data_get($source, $path);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function extractAccessToken(array $tokenData): ?string
    {
        return data_get($tokenData, 'data.access_token')
            ?? data_get($tokenData, 'access_token');
    }

    private function extractTokenPayload(array $tokenData, ?string $accessToken = null): array
    {
        $candidates = [
            data_get($tokenData, 'data.id_token'),
            data_get($tokenData, 'id_token'),
            $accessToken,
        ];

        foreach ($candidates as $token) {
            if (! is_string($token) || $token === '') {
                continue;
            }

            $payload = $this->decodeJwtPayload($token);
            if (! empty($payload)) {
                return $payload;
            }
        }

        return [];
    }

    private function decodeJwtPayload(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) < 2) {
            return [];
        }

        $payload = $parts[1];
        $payload = strtr($payload, '-_', '+/');
        $payload .= str_repeat('=', (4 - (strlen($payload) % 4)) % 4);
        $decoded = base64_decode($payload);
        if ($decoded === false) {
            return [];
        }

        $data = json_decode($decoded, true);
        return is_array($data) ? $data : [];
    }

    private function normalizeIdentityNumber(mixed $value): ?string
    {
        $wasNumeric = is_int($value) || is_float($value);
        if (is_int($value)) {
            $value = (string) $value;
        } elseif (is_float($value)) {
            if (! is_finite($value)) {
                return null;
            }
            $value = sprintf('%.0f', $value);
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === '') {
            return null;
        }
        if ($wasNumeric && strlen($digits) < 16) {
            $digits = str_pad($digits, 16, '0', STR_PAD_LEFT);
        }
        if (strlen($digits) !== 16) {
            return null;
        }

        return $digits;
    }

    private function normalizePhone(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        $digits = $value;
        if ($digits === '' || strlen($digits) < 8 || strlen($digits) > 20) {
            return null;
        }

        return $digits;
    }

    private function extractPhoneDigits(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            $value = (string) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', trim($value));
        return $digits === '' ? null : $digits;
    }

    private function syncSiapKerjaProfile(
        User $user,
        array $accountData,
        ?array $profilePayload,
        array $profileData,
        ?string $profileId,
        string $accessToken
    ): void {
        if (empty($profileData)) {
            return;
        }

        $domicile = $this->extractDomicileRegion($profileData);
        $phone = $this->normalizePhone($this->extractPhoneDigits(
            $this->firstFilled($profileData, [
                'phone',
                'phone_number',
                'mobile_phone',
                'mobile_number',
                'no_hp',
                'hp',
                'contact.phone',
                'contact.phone_number',
                'contact.mobile_phone',
                'contact.mobile_number',
                'user.phone',
                'user.phone_number',
                'user.mobile_phone',
                'user.mobile_number',
                'user.profile.phone',
                'user.profile.phone_number',
            ]) ?? $this->firstFilled($accountData, [
                'phone',
                'phone_number',
                'mobile_phone',
                'mobile_number',
                'no_hp',
                'hp',
                'user.phone',
                'user.phone_number',
                'user.mobile_phone',
                'user.mobile_number',
                'profile.phone',
                'profile.phone_number',
            ])
        ));

        $existingProfile = UserProfile::where('user_id', $user->id)->first();

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'sso_user_id' => data_get($accountData, 'id') ?? data_get($profileData, 'user.id'),
                'sso_profile_id' => $profileId,
                'identity_number' => data_get($profileData, 'identity_number') ?? data_get($accountData, 'username'),
                'phone' => $phone ?? $existingProfile?->phone,
                'address' => data_get($profileData, 'address'),
                'domicile_address' => data_get($profileData, 'domicile_address'),
                'domicile_region_id' => data_get($profileData, 'domicile_region_id'),
                'domicile_region_type' => $domicile['region_type'],
                'domicile_province_id' => $domicile['province_id'],
                'domicile_city_id' => $domicile['city_id'],
                'domicile_sub_district_id' => $domicile['sub_district_id'],
                'domicile_village_id' => $domicile['village_id'],
                'birth_place' => data_get($profileData, 'pob'),
                'birth_date' => data_get($profileData, 'bod'),
                'about' => data_get($profileData, 'about'),
                'picture_uri' => data_get($profileData, 'picture_uri'),
                'blood_type' => data_get($profileData, 'blood_type'),
                'gender' => data_get($profileData, 'gender'),
                'status' => data_get($profileData, 'status'),
                'domicile_region_payload' => data_get($profileData, 'domicile_region'),
                'payload' => $profilePayload,
                'synced_at' => now(),
            ]
        );

        if (! $profileId) {
            return;
        }

        $profileService = app(SiapKerjaProfileService::class);

        $this->syncEducations($user, $this->fetchProfileCollection(
            fn () => $profileService->fetchEducations($accessToken, $profileId),
            'pendidikan'
        ));
        $this->syncExperiences($user, $this->fetchProfileCollection(
            fn () => $profileService->fetchExperiences($accessToken, $profileId),
            'pengalaman kerja'
        ));
        $this->syncTrainings($user, $this->fetchProfileCollection(
            fn () => $profileService->fetchTrainings($accessToken, $profileId),
            'pelatihan'
        ));
        $this->syncCertifications($user, $this->fetchProfileCollection(
            fn () => $profileService->fetchCertifications($accessToken, $profileId),
            'sertifikasi'
        ));
        $this->syncSkills($user, $this->fetchProfileCollection(
            fn () => $profileService->fetchSkills($accessToken, $profileId),
            'keterampilan'
        ));
        $this->syncLanguages($user, $this->fetchProfileCollection(
            fn () => $profileService->fetchLanguages($accessToken, $profileId),
            'bahasa'
        ));
    }

    private function fetchProfileCollection(callable $fetch, string $label): array
    {
        try {
            return $fetch();
        } catch (\Throwable $exception) {
            Log::warning("Gagal mengambil data {$label} SIAPKerja.", [
                'error' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    private function extractDomicileRegion(array $profileData): array
    {
        $regionType = data_get($profileData, 'domicile_region.regionable_type');
        $regionable = data_get($profileData, 'domicile_region.regionable');
        $provinceId = null;
        $cityId = null;
        $subDistrictId = null;
        $villageId = null;

        switch ($regionType) {
            case 'village':
                $villageId = data_get($regionable, 'id');
                $subDistrictId = data_get($regionable, 'sub_district.id');
                $cityId = data_get($regionable, 'sub_district.city.id');
                $provinceId = data_get($regionable, 'sub_district.city.province.id');
                break;
            case 'sub_district':
                $subDistrictId = data_get($regionable, 'id');
                $cityId = data_get($regionable, 'city.id');
                $provinceId = data_get($regionable, 'city.province.id');
                break;
            case 'city':
                $cityId = data_get($regionable, 'id');
                $provinceId = data_get($regionable, 'province.id');
                break;
            case 'province':
                $provinceId = data_get($regionable, 'id');
                break;
        }

        return [
            'region_type' => $regionType,
            'province_id' => $provinceId,
            'city_id' => $cityId,
            'sub_district_id' => $subDistrictId,
            'village_id' => $villageId,
        ];
    }

    private function syncEducations(User $user, array $items): void
    {
        foreach ($items as $item) {
            $ssoId = data_get($item, 'id');
            if (! $ssoId) {
                continue;
            }

            UserEducation::updateOrCreate(
                ['user_id' => $user->id, 'sso_id' => (string) $ssoId],
                [
                    'school_name' => data_get($item, 'school.name'),
                    'school_address' => data_get($item, 'school.address'),
                    'graduate_name' => data_get($item, 'graduate.name'),
                    'study_field_name' => data_get($item, 'study_field.name'),
                    'start_year' => data_get($item, 'start_year'),
                    'finish_year' => data_get($item, 'finish_year'),
                    'payload' => $item,
                ]
            );
        }
    }

    private function syncExperiences(User $user, array $items): void
    {
        foreach ($items as $item) {
            $ssoId = data_get($item, 'id');
            if (! $ssoId) {
                continue;
            }

            UserExperience::updateOrCreate(
                ['user_id' => $user->id, 'sso_id' => (string) $ssoId],
                [
                    'company_name' => data_get($item, 'company.name'),
                    'job_title' => data_get($item, 'job.name'),
                    'start_month' => data_get($item, 'start_month'),
                    'start_year' => data_get($item, 'start_year'),
                    'finish_month' => data_get($item, 'finish_month'),
                    'finish_year' => data_get($item, 'finish_year'),
                    'payload' => $item,
                ]
            );
        }
    }

    private function syncTrainings(User $user, array $items): void
    {
        foreach ($items as $item) {
            $ssoId = data_get($item, 'id');
            if (! $ssoId) {
                continue;
            }

            UserTraining::updateOrCreate(
                ['user_id' => $user->id, 'sso_id' => (string) $ssoId],
                [
                    'training_center_name' => data_get($item, 'training_center.name'),
                    'vocational_name' => data_get($item, 'vocational.name'),
                    'sub_vocational_name' => data_get($item, 'sub_vocational.name'),
                    'training_program_name' => data_get($item, 'training_program.name'),
                    'start_month' => data_get($item, 'start_month'),
                    'start_year' => data_get($item, 'start_year'),
                    'finish_month' => data_get($item, 'finish_month'),
                    'finish_year' => data_get($item, 'finish_year'),
                    'payload' => $item,
                ]
            );
        }
    }

    private function syncCertifications(User $user, array $items): void
    {
        foreach ($items as $item) {
            $ssoId = data_get($item, 'id');
            if (! $ssoId) {
                continue;
            }

            UserCertification::updateOrCreate(
                ['user_id' => $user->id, 'sso_id' => (string) $ssoId],
                [
                    'institution_name' => data_get($item, 'institution.name'),
                    'program_name' => data_get($item, 'program.name'),
                    'issued_month' => data_get($item, 'issued_month'),
                    'issued_year' => data_get($item, 'issued_year'),
                    'expire_month' => data_get($item, 'expire_month'),
                    'expire_year' => data_get($item, 'expire_year'),
                    'payload' => $item,
                ]
            );
        }
    }

    private function syncSkills(User $user, array $items): void
    {
        foreach ($items as $item) {
            $ssoId = data_get($item, 'id');
            if (! $ssoId) {
                continue;
            }

            UserSkill::updateOrCreate(
                ['user_id' => $user->id, 'sso_id' => (string) $ssoId],
                [
                    'name' => data_get($item, 'name'),
                    'payload' => $item,
                ]
            );
        }
    }

    private function syncLanguages(User $user, array $items): void
    {
        foreach ($items as $item) {
            $ssoId = data_get($item, 'id');
            if (! $ssoId) {
                continue;
            }

            UserLanguage::updateOrCreate(
                ['user_id' => $user->id, 'sso_id' => (string) $ssoId],
                [
                    'language_name' => data_get($item, 'language.name'),
                    'proficiency_name' => data_get($item, 'proficiency.name'),
                    'payload' => $item,
                ]
            );
        }
    }
}
