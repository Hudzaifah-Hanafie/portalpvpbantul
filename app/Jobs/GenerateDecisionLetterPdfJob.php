<?php

namespace App\Jobs;

use App\Models\DecisionLetter;
use App\Models\AdminNotification;
use App\Services\Letter\DecisionLetterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateDecisionLetterPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Timeout for the job inside the queue (seconds).
     * Set to 10 minutes (600) to ensure massive PDF generation completes.
     */
    public $timeout = 600;

    public function __construct(
        private DecisionLetter $decisionLetter,
        private string|int $adminId
    ) {}

    public function handle(DecisionLetterService $letterService)
    {
        // 1. Rebuild the payload directly inside the queue to avoid passing huge collections via Redis/DB
        $payload = $letterService->buildPrintPayload($this->decisionLetter);
        
        $title = $this->decisionLetter->letter_number ?: 'surat-keputusan';
        $fileName = 'sk-' . Str::slug($title) . '-' . time() . '.pdf';

        // 2. Load View and Render PDF
        $pdf = Pdf::loadView('admin.decision_letter.print', $payload)->setPaper('A4', 'portrait');

        // 3. Save to Local/Public Storage
        $directory = 'exports/pdf/decision-letters';
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
        
        $path = $directory . '/' . $fileName;
        Storage::disk('public')->put($path, $pdf->output());

        // 4. Create Notification
        AdminNotification::create([
            'user_id' => $this->adminId,
            'title' => 'PDF Surat Keputusan Siap Diunduh',
            'message' => 'Proses generasi Surat Keputusan ' . ($this->decisionLetter->letter_number ?: '') . ' telah selesai.',
            'action_url' => Storage::url($path),
            'type' => 'success',
            'is_read' => false
        ]);
    }
}
