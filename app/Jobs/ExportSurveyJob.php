<?php

namespace App\Jobs;

use App\Exports\SurveyResponsesExport;
use App\Mail\SurveyExportReadyMail;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ExportSurveyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; 

    public function __construct(
        public Survey $survey,
        public ?User $user
    ) {}

    public function handle(): void
    {
        try {
            $fileName = 'exports/' . Str::uuid() . '-survey-' . $this->survey->slug . '.xlsx';

            Excel::store(new SurveyResponsesExport($this->survey), $fileName, 'local');

            if ($this->user->email) {
                Mail::to($this->user->email)->send(new SurveyExportReadyMail($this->survey, $fileName));
            }

            Storage::disk('local')->delete($fileName);

        } catch (\Exception $e) {
            Log::error('Gagal mengekspor survei: ' . $e->getMessage(), [
                'survey_id' => $this->survey->id,
                'user_id' => $this->user->id
            ]);
        }
    }
}
