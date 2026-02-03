<?php

namespace App\Jobs;

use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\SiteSetting;
use App\Models\TrainingSchedule;
use App\Models\User;
use App\Services\SiapKerjaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncParticipantsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param list<string> $batchIds
     */
    public function __construct(private ?array $batchIds = null)
    {
    }

    public function handle(SiapKerjaService $siapKerja): void
    {
        $serviceToken = SiteSetting::valueOf('siapkerja_service_token') ?: config('services.siapkerja.service_token');
        $serviceToken = is_string($serviceToken) ? trim($serviceToken) : null;

        $adminClientId = SiteSetting::valueOf('siapkerja_admin_client_id') ?: config('services.siapkerja.admin_client_id');
        $adminClientSecret = SiteSetting::valueOf('siapkerja_admin_client_secret') ?: config('services.siapkerja.admin_client_secret');
        $adminClientId = is_string($adminClientId) ? trim($adminClientId) : null;
        $adminClientSecret = is_string($adminClientSecret) ? trim($adminClientSecret) : null;

        if (! $serviceToken && (! $adminClientId || ! $adminClientSecret)) {
            Log::warning('Sinkronisasi peserta Skillhub dilewati karena token layanan SIAP Kerja belum diatur.');
            return;
        }

        $schedules = TrainingSchedule::whereNotNull('batch_id')
            ->when($this->batchIds, fn ($query) => $query->whereIn('batch_id', $this->batchIds))
            ->get();

        foreach ($schedules as $schedule) {
            try {
                $participants = $siapKerja->fetchParticipants($schedule->batch_id);
            } catch (\Throwable $e) {
                Log::warning('Gagal sinkronisasi peserta Skillhub', [
                    'batch_id' => $schedule->batch_id,
                    'message' => $e->getMessage(),
                ]);
                continue;
            }

            $this->ensureCourseClass($schedule);

            foreach ($participants as $participant) {
                $user = $this->findOrCreateUser($participant);
                if (! $user) {
                    continue;
                }

                $status = $this->mapStatus(Arr::get($participant, 'status'));
                $enrollment = CourseEnrollment::firstOrNew([
                    'course_class_id' => $schedule->id,
                    'user_id' => $user->id,
                ]);

                if (! $enrollment->exists) {
                    $enrollment->status = $status;
                } else {
                    if ($status === 'completed') {
                        $enrollment->status = 'completed';
                    } elseif (in_array($enrollment->status, ['pending', 'active'], true)) {
                        $enrollment->status = $status;
                    }
                }

                if ($status === 'completed' && ! $enrollment->completed_at) {
                    $enrollment->completed_at = now();
                }

                $enrollment->save();
            }
        }
    }

    private function findOrCreateUser(array $participant): ?User
    {
        $nik = Arr::get($participant, 'nik') ?? Arr::get($participant, 'identity_number');
        $siapKerjaId = Arr::get($participant, 'id') ?? Arr::get($participant, 'user_id');
        $email = Arr::get($participant, 'email');
        $name = Arr::get($participant, 'name') ?? Arr::get($participant, 'full_name') ?? 'Peserta Skillhub';

        if (! $nik && ! $email && ! $siapKerjaId) {
            return null;
        }

        $existingQuery = User::query();
        if ($nik) {
            $existingQuery->where('nik', $nik);
        }
        if ($siapKerjaId) {
            $existingQuery->{$nik ? 'orWhere' : 'where'}('siap_kerja_id', $siapKerjaId);
        }
        if ($email) {
            $existingQuery->orWhere('email', $email);
        }
        $user = $existingQuery->first();

        if ($user) {
            $user->fill([
                'nik' => $nik ?: $user->nik,
                'siap_kerja_id' => $siapKerjaId ?: $user->siap_kerja_id,
                'email' => $email ?: $user->email,
                'name' => $name ?: $user->name,
            ])->save();

            return $user;
        }

        if (! $email) {
            return null;
        }

        return User::create([
            'name' => $name,
            'nik' => $nik,
            'email' => $email,
            'siap_kerja_id' => $siapKerjaId,
            'password' => Str::random(32),
            'email_verified_at' => now(),
        ]);
    }

    private function mapStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'completed', 'completed_training' => 'completed',
            'selected', 'approved' => 'approved',
            'rejected' => 'rejected',
            default => 'pending',
        };
    }

    private function ensureCourseClass(TrainingSchedule $schedule): void
    {
        $class = CourseClass::firstOrNew(['id' => $schedule->id]);
        $class->fill([
            'title' => $schedule->judul,
            'description' => $schedule->catatan,
            'format' => 'sinkron',
            'is_active' => true,
            'status' => 'published',
            'published_at' => now(),
        ]);
        $class->id ??= $schedule->id;
        $class->save();
    }
}
