<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load([
            'roles',
            'profile',
            'educations',
            'experiences',
            'trainings',
            'certifications',
            'skills',
            'languages',
        ]);

        if ($request->is('my/*')) {
            if ($user->hasAnyRole(['instructor', 'instruktur'])) {
                return view('instructor.profile.show', compact('user'));
            }

            if ($user->hasAnyRole(['superadmin', 'admin']) || $user->hasPermission('access-admin')) {
                return view('admin.profile.show', compact('user'));
            }

            return view('participant.profile.show', compact('user'));
        }

        return view('profile.show', compact('user'));
    }

    public function updatePhone(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:32'],
        ]);

        UserProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            ['phone' => $data['phone']]
        );

        return redirect()
            ->route('profile.show')
            ->with('status', 'Nomor HP berhasil disimpan. Silakan klik Sinkronkan untuk menarik data profil.');
    }
}
