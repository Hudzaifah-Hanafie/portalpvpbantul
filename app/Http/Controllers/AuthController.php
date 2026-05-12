<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorCode;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 8;
    private const LOCKOUT_MINUTES = 15;
    private const TWO_FACTOR_ATTEMPTS = 5;

    public function __construct(private ActivityLogger $logger)
    {
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showAdminLoginForm()
    {
        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        return redirect()->route('sso.siapkerja.redirect');
    }

    public function loginAdmin(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $attemptKey = $this->loginAttemptKey($data['email'], $request->ip());
        if (RateLimiter::tooManyAttempts($attemptKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($attemptKey);
            return back()
                ->withErrors(['email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik."])
                ->withInput($request->only('email'));
        }

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $request->boolean('remember'))) {
            RateLimiter::hit($attemptKey, self::LOCKOUT_MINUTES * 60);
            $this->logger->log(null, 'login.failed', 'Login admin gagal', null, ['email' => $data['email']]);
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->withInput($request->only('email'));
        }

        RateLimiter::clear($attemptKey);

        $user = $request->user();
        if (! $user || ! $user->hasPermission('access-admin')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Akun ini tidak memiliki akses admin. Gunakan login SIAP Kerja.'])
                ->withInput($request->only('email'));
        }

        if ($user->two_factor_enabled) {
            $intended = $request->session()->pull('url.intended', route('admin.dashboard'));
            Auth::logout();
            $request->session()->put('two_factor.pending_user', $user->id);
            $request->session()->put('two_factor.intended', $intended);
            $request->session()->regenerate();

            $this->sendTwoFactorCode($user);

            return redirect()->route('two-factor');
        }

        $request->session()->regenerate();
        $this->logger->log($user, 'login.success', 'Login admin berhasil');

        return redirect()->to($this->defaultRedirect($user));
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $this->logger->log($user, 'logout', 'Akun keluar');

        if ($user && ($user->siap_kerja_id || ! empty($user->sso_payload))) {
            $continueUrl = route('login');
            $logoutUrl = 'https://account.kemnaker.go.id/auth/logout?continue=' . urlencode($continueUrl);

            return redirect()->away($logoutUrl);
        }

        return redirect()->route('login');
    }

    public function showForgotPasswordForm()
    {
        return redirect()->route('login')->with('error', 'Reset password dikelola melalui SIAP Kerja.');
    }

    public function sendResetLink(Request $request)
    {
        return redirect()->route('login')->with('error', 'Reset password dikelola melalui SIAP Kerja.');
    }

    public function showResetForm(string $token)
    {
        return redirect()->route('login')->with('error', 'Reset password dikelola melalui SIAP Kerja.');
    }

    public function resetPassword(Request $request)
    {
        return redirect()->route('login')->with('error', 'Reset password dikelola melalui SIAP Kerja.');
    }

    public function showTwoFactorForm()
    {
        if (! session()->has('two_factor.pending_user')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor');
    }

    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $pendingId = session('two_factor.pending_user');
        if (! $pendingId) {
            return redirect()->route('login');
        }

        if (RateLimiter::tooManyAttempts($this->twoFactorAttemptKey($pendingId), self::TWO_FACTOR_ATTEMPTS)) {
            return back()->withErrors(['code' => 'Terlalu banyak percobaan 2FA. Coba lagi nanti.']);
        }

        $expectedCode = Cache::get($this->twoFactorCacheKey($pendingId));
        if (! $expectedCode || ! hash_equals($expectedCode, $request->input('code'))) {
            RateLimiter::hit($this->twoFactorAttemptKey($pendingId), self::LOCKOUT_MINUTES * 60);
            $this->logger->log(null, 'twofactor.failed', 'Kode 2FA tidak valid');
            return back()->withErrors(['code' => 'Kode 2FA tidak cocok.']);
        }

        Cache::forget($this->twoFactorCacheKey($pendingId));
        RateLimiter::clear($this->twoFactorAttemptKey($pendingId));

        $user = User::find($pendingId);
        if (! $user) {
            return redirect()->route('login');
        }

        Auth::login($user);
        session()->forget(['two_factor.pending_user']);
        $intended = session()->pull('two_factor.intended', route('admin.dashboard'));
        $request->session()->regenerate();

        $this->logger->log($user, 'login.success', 'Login 2FA berhasil');

        return redirect()->to($intended ?? $this->defaultRedirect($user));
    }

    public function resendTwoFactorCode(Request $request)
    {
        $pendingId = session('two_factor.pending_user');
        if (! $pendingId) {
            return redirect()->route('login');
        }

        $user = User::find($pendingId);
        if (! $user) {
            return redirect()->route('login');
        }

        $this->sendTwoFactorCode($user, true);
        return back()->with('status', 'Kode 2FA baru telah dikirim.');
    }

    private function twoFactorCacheKey(string $userId): string
    {
        return "two-factor:{$userId}";
    }

    private function twoFactorAttemptKey(string $userId): string
    {
        return "two-factor-attempts:{$userId}";
    }

    private function loginAttemptKey(string $email, ?string $ip): string
    {
        $normalized = Str::lower(trim($email));
        return "login:{$normalized}|{$ip}";
    }

    private function sendTwoFactorCode(User $user, bool $resend = false): void
    {
        $code = (string) random_int(100000, 999999);
        Cache::put($this->twoFactorCacheKey($user->id), $code, now()->addMinutes(5));
        RateLimiter::clear($this->twoFactorAttemptKey($user->id));
        Mail::to($user->email)->send(new TwoFactorCode($user, $code));
        $this->logger->log(
            $user,
            $resend ? 'twofactor.resend' : 'twofactor.sent',
            $resend ? 'Kode 2FA dikirim ulang' : 'Kode 2FA dikirim ke email'
        );
    }

    private function defaultRedirect(User $user): string
    {
        if ($user->hasPermission('access-admin')) {
            return route('admin.dashboard');
        }

        if ($user->hasAnyRole(['instructor', 'instruktur'])) {
            return route('instructor.dashboard');
        }

        if ($user->hasRole('participant')) {
            return route('participant.classes');
        }

        if ($user->hasPermission('access-alumni-forum')) {
            return route('alumni.forum.index');
        }

        return route('home');
    }
}
