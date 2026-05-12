<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Models\CourseEnrollment;
use App\Observers\CourseEnrollmentObserver;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        if ($this->app->environment('production')) {
            $appUrl = config('app.url');
            if ($appUrl) {
                URL::forceRootUrl($appUrl);
            }
            URL::forceScheme('https');
        }
        if (Schema::hasTable('site_settings')) {
            $mailer = SiteSetting::valueOf('mail_mailer');
            if ($mailer) {
                config(['mail.default' => $mailer]);
            }

            $smtp = config('mail.mailers.smtp', []);
            $host = SiteSetting::valueOf('mail_host');
            if ($host) {
                $smtp['host'] = $host;
                $smtp['url'] = null;
            }
            $port = SiteSetting::valueOf('mail_port');
            if ($port !== null && $port !== '') {
                $smtp['port'] = (int) $port;
                $smtp['url'] = null;
            }
            $username = SiteSetting::valueOf('mail_username');
            if ($username !== null && $username !== '') {
                $smtp['username'] = $username;
                $smtp['url'] = null;
            }
            $password = SiteSetting::valueOf('mail_password');
            if ($password !== null && $password !== '') {
                $smtp['password'] = $password;
                $smtp['url'] = null;
            }
            $encryption = SiteSetting::valueOf('mail_encryption');
            if ($encryption) {
                $smtp['encryption'] = $encryption === 'none' ? null : $encryption;
                if ($encryption === 'ssl') {
                    $smtp['scheme'] = 'smtps';
                }
                if ($encryption === 'tls') {
                    $smtp['scheme'] = 'smtp';
                }
                $smtp['url'] = null;
            }

            config(['mail.mailers.smtp' => $smtp]);

            $fromAddress = SiteSetting::valueOf('mail_from_address');
            if ($fromAddress) {
                config(['mail.from.address' => $fromAddress]);
            }
            $fromName = SiteSetting::valueOf('mail_from_name');
            if ($fromName) {
                config(['mail.from.name' => $fromName]);
            }
        }
        View::composer('layouts.app', function ($view) {
            $footerSettings = SiteSetting::pluck('value', 'key');
            $view->with('footerSettings', $footerSettings);
        });

        View::composer('layouts.admin', function ($view) {
            $view->with('groupActive', fn ($patterns) => collect((array) $patterns)->contains(fn ($pattern) => request()->routeIs($pattern)));
        });

        CourseEnrollment::observe(CourseEnrollmentObserver::class);

        // 1. AUTO-CACHE INVALIDATION OBSERVERS
        // Otomatis menghancurkan memori statis apabila Admin CMS merilis konten baru.
        // Dengan ini public landing page akan 100% Real-Time.
        \App\Models\Berita::saved(fn () => \Illuminate\Support\Facades\Cache::forget('home_berita_terbaru'));
        \App\Models\Berita::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('home_berita_terbaru'));

        \App\Models\Program::saved(fn () => \Illuminate\Support\Facades\Cache::forget('home_programs'));
        \App\Models\Program::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('home_programs'));

        \App\Models\Pengumuman::saved(fn () => \Illuminate\Support\Facades\Cache::forget('home_announcements'));
        \App\Models\Pengumuman::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('home_announcements'));

        \App\Models\Galeri::saved(fn () => \Illuminate\Support\Facades\Cache::forget('home_galeris'));
        \App\Models\Galeri::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('home_galeris'));

        SiteSetting::saved(fn () => \Illuminate\Support\Facades\Cache::forget('home_settings'));
        SiteSetting::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('home_settings'));

        RateLimiter::for('api-login', function (Request $request) {
            $email = (string) $request->input('email', '');
            $key = strtolower($email) . '|' . $request->ip();
            return Limit::perMinute(10)->by($key);
        });

        RateLimiter::for('api-user-management', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();
            return Limit::perMinute(60)->by($key);
        });

        // 2. DECOUPLED MIDDLEWARE RATE LIMITER
        // Memisahkan jalur pengunjung web (CMS) dan pengumpul tugas CBT (LMS)
        RateLimiter::for('cms-public', function (Request $request) {
            return Limit::perMinute(300)->by($request->ip()); // Sangat longgar untuk masyarakat
        });

        RateLimiter::for('lms-submit', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();
            return Limit::perMinute(20)->by($key); // Sangat ketat agar DDoS submission tidak membunuh server Database
        });
    }
}
