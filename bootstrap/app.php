<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\EventServiceProvider;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\RecordVisit;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrustHosts;
use App\Http\Middleware\StoreAdminNotificationsFromSession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
            'permission' => \App\Http\Middleware\EnsurePermission::class,
        ]);
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }

            return route('login');
        });
        $middleware->append(TrustHosts::class);
        $middleware->append(ForceHttps::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->appendToGroup('web', RecordVisit::class);
        $middleware->appendToGroup('web', StoreAdminNotificationsFromSession::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
