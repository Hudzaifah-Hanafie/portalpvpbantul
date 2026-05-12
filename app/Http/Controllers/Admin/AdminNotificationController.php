<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function markRead(AdminNotification $notification)
    {
        $user = request()->user();
        if (! $user || $notification->user_id !== $user->id) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        return back();
    }

    public function markAllRead(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        AdminNotification::forUser($user->id)->whereNull('read_at')->update(['read_at' => now()]);

        return back();
    }
}
