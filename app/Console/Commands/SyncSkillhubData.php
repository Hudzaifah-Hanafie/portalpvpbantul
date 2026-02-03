<?php

namespace App\Console\Commands;

use App\Models\CourseClass;
use App\Models\Instructor;
use App\Models\Program;
use App\Models\SiteSetting;
use App\Models\TrainingSchedule;
use App\Services\SiapKerjaService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SyncSkillhubData extends Command
{
    protected $signature = 'skillhub:sync';

    protected $description = 'Sinkronisasi program, jadwal, dan instruktur dari Skillhub (SIAP Kerja).';

    public function __construct(private SiapKerjaService $siapKerja)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Memulai sinkronisasi Skillhub...');

        $serviceToken = SiteSetting::valueOf('siapkerja_service_token') ?: config('services.siapkerja.service_token');
        $serviceToken = is_string($serviceToken) ? trim($serviceToken) : null;

        $adminClientId = SiteSetting::valueOf('siapkerja_admin_client_id') ?: config('services.siapkerja.admin_client_id');
        $adminClientSecret = SiteSetting::valueOf('siapkerja_admin_client_secret') ?: config('services.siapkerja.admin_client_secret');
        $adminClientId = is_string($adminClientId) ? trim($adminClientId) : null;
        $adminClientSecret = is_string($adminClientSecret) ? trim($adminClientSecret) : null;

        if (! $serviceToken && (! $adminClientId || ! $adminClientSecret)) {
            $this->warn('Token layanan SIAP Kerja belum diatur. Sinkronisasi Skillhub dilewati.');
            return self::SUCCESS;
        }

        $programs = $this->pullPrograms();
        $this->pullSchedules($programs);
        $this->pullInstructors();

        $this->info('Sinkronisasi Skillhub selesai.');

        return self::SUCCESS;
    }

    private function pullPrograms(): array
    {
        $this->line('- Sinkronisasi program...');

        try {
            $programs = $this->siapKerja->fetchPrograms();
        } catch (\Throwable $e) {
            $this->error('Gagal mengambil data program: ' . $e->getMessage());
            return [];
        }

        $programMap = [];

        foreach ($programs as $programData) {
            $externalId = $this->extractExternalId($programData);
            if (! $externalId) {
                $this->warn('  > Program dilewati karena tidak memiliki external_id.');
                continue;
            }

            $program = Program::updateOrCreate(
                ['external_id' => $externalId],
                $this->buildProgramAttributes($programData, $externalId)
            );

            $programMap[$externalId] = $program;
        }

        $this->info('  > ' . count($programMap) . ' program diperbarui.');

        return $programMap;
    }

    private function pullSchedules(array $programMap): void
    {
        $this->line('- Sinkronisasi jadwal pelatihan...');

        foreach ($programMap as $externalId => $program) {
            try {
                $schedules = $this->siapKerja->fetchSchedules($externalId);
            } catch (\Throwable $e) {
                $this->warn("  > Gagal mengambil jadwal untuk program {$externalId}: {$e->getMessage()}");
                continue;
            }

            foreach ($schedules as $scheduleData) {
                $batchId = $this->extractBatchId($scheduleData);
                $scheduleExternalId = $this->extractExternalId($scheduleData);

                if (! $batchId && ! $scheduleExternalId) {
                    $this->warn('    - Jadwal dilewati karena tidak memiliki batch_id / external_id.');
                    continue;
                }

                $lookup = $batchId ? ['batch_id' => $batchId] : ['external_id' => $scheduleExternalId];
                $schedule = TrainingSchedule::updateOrCreate(
                    $lookup,
                    $this->buildScheduleAttributes($scheduleData, $program, $scheduleExternalId, $batchId)
                );

                $this->syncCourseClassForSchedule($schedule);
            }
        }
    }

    private function pullInstructors(): void
    {
        $this->line('- Sinkronisasi instruktur...');

        try {
            $instructors = $this->siapKerja->fetchInstructors();
        } catch (\Throwable $e) {
            $this->warn('  > Gagal mengambil data instruktur: ' . $e->getMessage());
            return;
        }

        foreach ($instructors as $instructor) {
            $externalId = $this->extractExternalId($instructor) ?? Arr::get($instructor, 'nik');
            $email = Arr::get($instructor, 'email');

            if (! $externalId && ! $email) {
                $this->warn('    - Instruktur dilewati karena tidak ada external_id/email.');
                continue;
            }

            Instructor::updateOrCreate(
                $externalId ? ['external_id' => $externalId] : ['email' => $email],
                [
                    'external_id' => $externalId,
                    'nama' => Arr::get($instructor, 'name') ?? Arr::get($instructor, 'nama') ?? 'Instruktur',
                    'keahlian' => Arr::get($instructor, 'expertise') ?? Arr::get($instructor, 'keahlian'),
                    'deskripsi' => Arr::get($instructor, 'bio') ?? Arr::get($instructor, 'deskripsi'),
                    'email' => $email,
                    'is_active' => (bool) Arr::get($instructor, 'is_active', true),
                    'status' => 'published',
                    'published_at' => now(),
                ]
            );
        }
    }

    private function buildProgramAttributes(array $programData, string $externalId): array
    {
        $infoTambahan = Arr::get($programData, 'info') ?? Arr::get($programData, 'notes');
        $duration = Arr::get($programData, 'duration');
        if ($duration) {
            $infoTambahan = trim(($infoTambahan ? $infoTambahan . PHP_EOL : '') . "Durasi: {$duration}");
        }

        return [
            'external_id' => $externalId,
            'judul' => $this->resolveText($programData, ['title', 'name', 'judul']) ?? 'Program',
            'deskripsi' => $this->resolveText($programData, ['description', 'deskripsi']) ?? '-',
            'info_tambahan' => $infoTambahan,
            'pendaftaran_link' => $this->buildRegistrationUrl($externalId),
            'status' => 'published',
            'published_at' => now(),
        ];
    }

    private function buildScheduleAttributes(array $data, Program $program, ?string $externalId, ?string $batchId): array
    {
        $startDate = $this->parseDate(
            Arr::get($data, 'start_date') ??
            Arr::get($data, 'mulai') ??
            Arr::get($data, 'startAt')
        );
        $endDate = $this->parseDate(
            Arr::get($data, 'end_date') ??
            Arr::get($data, 'selesai') ??
            Arr::get($data, 'endAt')
        );

        return [
            'program_id' => $program->id,
            'external_id' => $externalId,
            'batch_id' => $batchId,
            'judul' => $this->resolveText($data, ['title', 'name', 'judul']) ?? $program->judul,
            'penyelenggara' => Arr::get($data, 'organizer') ?? Arr::get($data, 'penyelenggara'),
            'lokasi' => Arr::get($data, 'location') ?? Arr::get($data, 'lokasi'),
            'mulai' => $startDate,
            'selesai' => $endDate,
            'kuota' => (string) (Arr::get($data, 'quota') ?? Arr::get($data, 'kuota') ?? ''),
            'bulan' => Arr::get($data, 'bulan') ?? $this->formatMonth($startDate),
            'tahun' => Arr::get($data, 'tahun') ?? ($startDate?->format('Y')),
            'pendaftaran_link' => $this->buildRegistrationUrl($externalId ?? $program->external_id),
            'catatan' => Arr::get($data, 'notes') ?? Arr::get($data, 'catatan'),
            'is_active' => (bool) Arr::get($data, 'is_active', true),
        ];
    }

    private function syncCourseClassForSchedule(TrainingSchedule $schedule): void
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

    private function extractExternalId(array $data): ?string
    {
        return Arr::get($data, 'external_id') ?? Arr::get($data, 'id') ?? Arr::get($data, 'uuid');
    }

    private function extractBatchId(array $data): ?string
    {
        return Arr::get($data, 'batch_id') ?? Arr::get($data, 'batchId') ?? Arr::get($data, 'batch');
    }

    private function buildRegistrationUrl(?string $externalId): ?string
    {
        if (! $externalId) {
            return null;
        }

        return "https://skillhub.kemnaker.go.id/pelatihan/{$externalId}/daftar";
    }

    private function parseDate(mixed $date): ?Carbon
    {
        if (! $date) {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatMonth(?Carbon $date): ?string
    {
        if (! $date) {
            return null;
        }

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $months[(int) $date->format('n')] ?? null;
    }

    private function resolveText(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = Arr::get($data, $key);
            if ($value) {
                return $value;
            }
        }

        return null;
    }
}
