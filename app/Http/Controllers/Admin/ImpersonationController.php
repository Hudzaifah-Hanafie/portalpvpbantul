<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function start(Request $request, User $user)
    {
        $currentUser = $request->user();

        if ($request->session()->has('impersonator_id')) {
            return back()->with('error', 'Selesaikan sesi impersonasi yang sedang berjalan terlebih dahulu.');
        }

        $this->authorize('impersonate', $user);

        if ($currentUser->is($user)) {
            return back()->with('error', 'Anda tidak dapat melakukan impersonasi pada akun sendiri.');
        }

        $request->session()->put([
            'impersonator_id' => $currentUser->id,
            'impersonator_name' => $currentUser->name,
            'impersonated_user_id' => $user->id,
            'impersonated_user_name' => $user->name,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $this->logger->log(
            user: $currentUser,
            action: 'impersonate.start',
            description: "Memulai impersonasi {$user->email}",
            subject: $user
        );

        $redirectTo = $this->defaultRedirect($user);

        return redirect()->to($redirectTo)->with('success', "Sekarang masuk sebagai {$user->name}.");
    }

    public function stop(Request $request)
    {
        $impersonatorId = $request->session()->get('impersonator_id');
        $impersonatedId = $request->session()->get('impersonated_user_id');

        if (! $impersonatorId) {
            return redirect()->route('login')->with('error', 'Tidak ada sesi impersonasi aktif.');
        }

        $originalUser = User::find($impersonatorId);
        $impersonatedUser = $impersonatedId ? User::find($impersonatedId) : null;

        $request->session()->forget([
            'impersonator_id',
            'impersonator_name',
            'impersonated_user_id',
            'impersonated_user_name',
        ]);

        if (! $originalUser) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Akun asal tidak ditemukan. Silakan login ulang.');
        }

        Auth::login($originalUser);
        $request->session()->regenerate();

        $this->logger->log(
            user: $originalUser,
            action: 'impersonate.stop',
            description: $impersonatedUser
                ? "Berhenti impersonasi {$impersonatedUser->email}"
                : 'Berhenti impersonasi',
            subject: $impersonatedUser
        );

        return redirect()->route('admin.users.index')->with('success', 'Keluar dari mode impersonasi.');
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
