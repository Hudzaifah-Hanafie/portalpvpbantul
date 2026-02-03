<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncParticipantsJob;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

class SkillhubSyncController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $serviceToken = SiteSetting::valueOf('siapkerja_service_token') ?: config('services.siapkerja.service_token');
        $serviceToken = is_string($serviceToken) ? trim($serviceToken) : null;

        $adminClientId = SiteSetting::valueOf('siapkerja_admin_client_id') ?: config('services.siapkerja.admin_client_id');
        $adminClientSecret = SiteSetting::valueOf('siapkerja_admin_client_secret') ?: config('services.siapkerja.admin_client_secret');
        $adminClientId = is_string($adminClientId) ? trim($adminClientId) : null;
        $adminClientSecret = is_string($adminClientSecret) ? trim($adminClientSecret) : null;

        if (! $serviceToken && (! $adminClientId || ! $adminClientSecret)) {
            return redirect()->back()->with(
                'error',
                'Sinkronisasi Skillhub dilewati karena token layanan SIAP Kerja belum diatur.'
            );
        }

        try {
            Artisan::call('skillhub:sync');
            SyncParticipantsJob::dispatch();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Sinkronisasi gagal: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Sinkronisasi Skillhub berhasil dijalankan.');
    }
}
