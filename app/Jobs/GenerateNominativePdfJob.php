<?php

namespace App\Jobs;

use App\Models\AdminNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateNominativePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Set max timeout to 10 minutes for massive lists
     */
    public $timeout = 600;

    public $payload;
    public $fileName;
    public $adminId;

    public function __construct(array $payload, string $fileName, string|int $adminId)
    {
        $this->payload = $payload;
        $this->fileName = $fileName;
        $this->adminId = $adminId;
    }

    public function handle()
    {
        // 1. Render PDF Memory-Intensive Process in Background
        $pdf = Pdf::loadView('admin.course_nominative.print', $this->payload)
            ->setPaper('A4', 'landscape');
            
        // 2. Save it locally/publicly
        $directory = 'exports/pdf/nominatives';
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
        
        $path = $directory . '/' . $this->fileName;
        Storage::disk('public')->put($path, $pdf->output());

        // 3. Notify the Admin
        AdminNotification::create([
            'user_id' => $this->adminId,
            'title' => 'PDF Nominatif Siap Diunduh',
            'message' => 'Proses generasi Surat Daftar Nominatif telah selesai dan siap diunduh.',
            'action_url' => Storage::url($path),
            'type' => 'success',
            'is_read' => false
        ]);
    }
}
