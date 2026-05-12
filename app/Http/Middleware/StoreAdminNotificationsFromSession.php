<?php

namespace App\Http\Middleware;

use App\Models\AdminNotification;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreAdminNotificationsFromSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();
        if (! $user || ! $request->routeIs('admin.*')) {
            return $response;
        }

        $map = [
            'success' => 'Berhasil',
            'error' => 'Gagal',
            'warning' => 'Perhatian',
            'info' => 'Info',
        ];

        foreach ($map as $type => $title) {
            $message = $request->session()->get($type);
            if (! $message) {
                continue;
            }

            AdminNotification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => (string) $message,
            ]);
        }

        return $response;
    }
}
