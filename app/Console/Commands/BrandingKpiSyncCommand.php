<?php

namespace App\Console\Commands;

use App\Services\BrandingKpiSyncService;
use Illuminate\Console\Command;

class BrandingKpiSyncCommand extends Command
{
    protected $signature = 'branding:kpi-sync';
    protected $description = 'Sinkronisasi KPI Branding otomatis dari Instagram.';

    public function handle(BrandingKpiSyncService $syncService): int
    {
        $result = $syncService->syncAll();

        $this->info($result['message']);

        return $result['status'] === 'success' ? self::SUCCESS : self::FAILURE;
    }
}
