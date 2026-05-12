<?php

namespace App\Console;

use App\Jobs\QueueHeartbeatJob;
use App\Jobs\SyncParticipantsJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Heartbeat untuk memastikan scheduler dan worker queue aktif
        $schedule->job(new QueueHeartbeatJob())->everyFiveMinutes();
        // Reminder course (tugas, sesi, grading, survey pasca kelas, rekaman)
        $schedule->command('course:reminders')->hourly();
        // Sinkronisasi data pusat Skillhub
        $schedule->command('skillhub:sync')->hourly();
        $schedule->job(new SyncParticipantsJob())->hourlyAt(15);
        // Sinkronisasi KPI Branding (Instagram) harian
        $schedule->command('branding:kpi-sync')->dailyAt('01:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
