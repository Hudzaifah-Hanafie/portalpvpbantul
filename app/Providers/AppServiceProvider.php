<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Models\CourseEnrollment;
use App\Observers\CourseEnrollmentObserver;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

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
    }
}
