<?php

namespace App\Services\Letter;

use App\Models\CourseClass;
use App\Models\CourseCurriculum;
use App\Models\CourseCurriculumUnit;
use App\Models\DecisionLetter;
use App\Models\TaskLetter;
use App\Models\TrainingSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DecisionLetterService
{
    public function validateData(Request $request): array
    {
        return $request->validate([
            'letter_number' => 'nullable|string|max:150',
            'batch_id' => 'nullable|string|max:50',
            'period_year' => 'nullable|integer|min:2000|max:2100',
            'program_id' => 'nullable|exists:programs,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'subject' => 'nullable|string',
            'considerations_text' => 'nullable|string',
            'legal_basis_text' => 'nullable|string',
            'decisions' => 'nullable|array',
            'decisions.kesatu' => 'nullable|string',
            'decisions.kedua' => 'nullable|string',
            'decisions.ketiga' => 'nullable|string',
            'decisions.keempat' => 'nullable|string',
            'decisions.kelima' => 'nullable|string',
            'location_name' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,published',
            'signed_city' => 'nullable|string|max:150',
            'signed_at' => 'nullable|date',
            'signatory_name' => 'nullable|string|max:150',
            'signatory_position' => 'nullable|string|max:150',
            'signatory_nip' => 'nullable|string|max:150',
            'approval_left_name' => 'nullable|string|max:150',
            'approval_left_position' => 'nullable|string|max:150',
            'approval_left_nip' => 'nullable|string|max:150',
            'approval_right_name' => 'nullable|string|max:150',
            'approval_right_position' => 'nullable|string|max:150',
            'approval_right_nip' => 'nullable|string|max:150',
            'instructor_team' => 'nullable|array',
            'recruitment_team' => 'nullable|array',
            'management_team' => 'nullable|array',
        ]);
    }

    public function hydrateData(array $data, Collection $schedules, ?string $createdBy): array
    {
        $batchId = $data['batch_id'] ?? $schedules->first()?->batch_id;
        $periodYear = $data['period_year'] ?? ($schedules->first()?->tahun ? (int) $schedules->first()->tahun : now()->year);
        $subject = $data['subject'] ?? $this->buildSubject($batchId, $periodYear);
        $considerations = $this->normalizeLines($data['considerations_text'] ?? null);
        $legalBasis = $this->normalizeLines($data['legal_basis_text'] ?? null);
        $decisions = $data['decisions'] ?? [];
        if (empty(array_filter($decisions))) {
            $decisions = $this->buildDecisionDefaults($schedules);
        }

        $payload = [
            'letter_number' => $data['letter_number'] ?? null,
            'batch_id' => $batchId,
            'period_year' => $periodYear,
            'schedule_ids' => $schedules->pluck('id')->values()->all(),
            'subject' => $subject,
            'considerations' => $considerations,
            'legal_basis' => $legalBasis,
            'decisions' => $decisions,
            'location_name' => $data['location_name'] ?? 'BPVP SURAKARTA',
            'status' => $data['status'] ?? 'draft',
            'signed_city' => $data['signed_city'] ?? 'Surakarta',
            'signed_at' => $data['signed_at'] ?? $this->defaultSignedAt($schedules),
            'signatory_name' => $data['signatory_name'] ?? null,
            'signatory_position' => $data['signatory_position'] ?? null,
            'signatory_nip' => $data['signatory_nip'] ?? null,
            'approval_left_name' => $data['approval_left_name'] ?? null,
            'approval_left_position' => $data['approval_left_position'] ?? null,
            'approval_left_nip' => $data['approval_left_nip'] ?? null,
            'approval_right_name' => $data['approval_right_name'] ?? null,
            'approval_right_position' => $data['approval_right_position'] ?? null,
            'approval_right_nip' => $data['approval_right_nip'] ?? null,
            'instructor_team' => $this->normalizeTeam($data['instructor_team'] ?? []),
            'recruitment_team' => $this->normalizeTeam($data['recruitment_team'] ?? []),
            'management_team' => $this->normalizeTeam($data['management_team'] ?? []),
            'created_by' => $createdBy,
        ];

        return $payload;
    }

    public function buildDefaults(Collection $schedules, ?DecisionLetter $letter = null): array
    {
        $taskLetter = TaskLetter::orderByDesc('created_at')->first();
        $fallbackSignatoryName = $taskLetter?->signatory_name ?? 'Verry Fahrudin, S.E., M.M.';
        $fallbackSignatoryNip = $taskLetter?->signatory_nip ?? '19861105 201503 1 004';
        $batchId = $letter?->batch_id ?? $schedules->first()?->batch_id;
        $periodYear = $letter?->period_year ?? ($schedules->first()?->tahun ? (int) $schedules->first()->tahun : now()->year);
        $subject = $letter?->subject ?? $this->buildSubject($batchId, $periodYear);

        $signedAt = $letter?->signed_at ?? $this->defaultSignedAt($schedules);

        $leftName = $letter?->approval_left_name ?? $fallbackSignatoryName;
        $leftPosition = $letter?->approval_left_position ?? 'Kepala BPVP Surakarta';
        $leftNip = $letter?->approval_left_nip ?? $fallbackSignatoryNip;

        return [
            'batch_id' => $batchId,
            'period_year' => $periodYear,
            'subject' => $subject,
            'considerations' => $letter?->considerations ?? $this->defaultConsiderations(),
            'legal_basis' => $letter?->legal_basis ?? $this->defaultLegalBasis(),
            'decisions' => $letter?->decisions ?? $this->buildDecisionDefaults($schedules),
            'location_name' => $letter?->location_name ?? 'BPVP SURAKARTA',
            'status' => $letter?->status ?? 'draft',
            'signed_city' => $letter?->signed_city ?? 'Surakarta',
            'signed_at' => $signedAt,
            'signatory_name' => $letter?->signatory_name ?? $fallbackSignatoryName,
            'signatory_position' => $letter?->signatory_position ?? 'KEPALA BPVP SURAKARTA',
            'signatory_nip' => $letter?->signatory_nip ?? $fallbackSignatoryNip,
            'approval_left_name' => $leftName,
            'approval_left_position' => $leftPosition,
            'approval_left_nip' => $leftNip,
            'approval_right_name' => $letter?->approval_right_name ?? 'Azof Ghazali Sujono, S.T., M.Eng',
            'approval_right_position' => $letter?->approval_right_position ?? 'Subkoordinator Bidang Pengukuran Peningkatan Produktivitas dan Pemantauan Pelatihan Vokasi',
            'approval_right_nip' => $letter?->approval_right_nip ?? '19820710 200604 1 001',
            'instructor_team' => $letter?->instructor_team ?? $this->buildInstructorTeamDefaults($schedules),
            'recruitment_team' => $letter?->recruitment_team ?? [],
            'management_team' => $letter?->management_team ?? [],
        ];
    }

    public function buildPrintPayload(DecisionLetter $letter): array
    {
        $schedules = $this->loadSchedules($letter);
        $programList = $this->buildProgramList($schedules);
        $periodWeek = $this->formatWeekLabel($letter->batch_id);
        $subjectLines = preg_split("/\\r\\n|\\r|\\n/", trim((string) $letter->subject));
        $dateRange = $this->formatRangeSentence($schedules);
        $curricula = $this->buildCurriculumPages($schedules);

        return [
            'letter' => $letter,
            'schedules' => $schedules,
            'programList' => $programList,
            'periodWeek' => $periodWeek,
            'subjectLines' => array_filter($subjectLines ?: []),
            'dateRange' => $dateRange,
            'curricula' => $curricula,
            'teams' => [
                'instruktur' => $letter->instructor_team ?? [],
                'rekrutmen' => $letter->recruitment_team ?? [],
                'pengelola' => $letter->management_team ?? [],
            ],
        ];
    }

    public function filtersFromRequest(Request $request): array
    {
        return [
            'batch_id' => $request->input('batch_id'),
            'period_year' => $request->input('period_year'),
            'program_id' => $request->input('program_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];
    }

    public function querySchedules(array $filters): Collection
    {
        $query = TrainingSchedule::with('program')->orderBy('mulai');
        if (! empty($filters['batch_id'])) {
            $query->where('batch_id', $filters['batch_id']);
        }
        if (! empty($filters['period_year'])) {
            $query->where('tahun', (string) $filters['period_year']);
        }
        if (! empty($filters['program_id'])) {
            $query->where('program_id', $filters['program_id']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('mulai', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('selesai', '<=', $filters['date_to']);
        }

        return $query->get();
    }

    public function loadSchedules(DecisionLetter $letter): Collection
    {
        $ids = collect($letter->schedule_ids ?? [])->filter()->values();
        if ($ids->isEmpty()) {
            return $this->querySchedules([
                'batch_id' => $letter->batch_id,
                'period_year' => $letter->period_year,
            ]);
        }

        return TrainingSchedule::with('program')
            ->whereIn('id', $ids)
            ->orderBy('mulai')
            ->get();
    }

    private function normalizeLines(?string $text): array
    {
        if (! $text) {
            return [];
        }
        $lines = preg_split("/\\r\\n|\\r|\\n/", $text);
        $lines = array_map(fn ($line) => trim($line), $lines);
        $lines = array_filter($lines, fn ($line) => $line !== '');

        return array_values($lines);
    }

    private function normalizeTeam(array $payload): array
    {
        $fields = ['name', 'nip', 'position', 'role'];
        $max = 0;
        foreach ($fields as $field) {
            $max = max($max, count($payload[$field] ?? []));
        }
        $rows = [];
        for ($i = 0; $i < $max; $i++) {
            $row = [];
            foreach ($fields as $field) {
                $row[$field] = trim($payload[$field][$i] ?? '');
            }
            if (! empty($row['name'])) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function buildSubject(?string $batchId, ?int $year): string
    {
        $week = $this->formatWeekLabel($batchId);
        $year = $year ?: now()->year;

        return implode("\n", [
            'PENYELENGGARAAN PELATIHAN BERBASIS KOMPETENSI',
            'MELALUI PELATIHAN DURASI SINGKAT PERIODE (WEEK) ' . $week,
            'PADA PENINGKATAN KOMPETENSI TENAGA KERJA DAN PRODUKTIVITAS',
            'DI BPVP SURAKARTA',
            'TAHUN ANGGARAN ' . $year,
        ]);
    }

    private function defaultConsiderations(): array
    {
        return [
            'Bahwa dalam rangka meningkatkan kualitas peserta pelatihan guna memastikan minat, bakat, dan kemampuannya perlu diselenggarakan PBK melalui Pelatihan Durasi Singkat;',
            'Untuk itu perlu dikeluarkan Surat Keputusan Kepala BPVP Surakarta tentang penyelenggaraan Pelatihan Berbasis Kompetensi Melalui Pelatihan Durasi Singkat di BPVP Surakarta.',
        ];
    }

    private function defaultLegalBasis(): array
    {
        return [
            'Undang-Undang Nomor 13 Tahun 2003, tentang Ketenagakerjaan (Lembaran Negara Republik Indonesia Tahun 2003 Nomor 39 Tambahan Lembaran Negara Republik Indonesia Nomor 4279);',
            'Peraturan Pemerintah Nomor 31 Tahun 2006 tentang Sistem Pelatihan Kerja Nasional (Lembaran Negara Republik Indonesia Tahun 2006 Nomor 67, Tambahan Lembaran Negara Republik Indonesia Nomor 4637);',
            'Peraturan Presiden Nomor 68 Tahun 2022 tentang Revitalisasi Pendidikan Vokasi dan Pelatihan Vokasi (Lembaran Negara Republik Indonesia Tahun 2022 Nomor 108);',
            'Peraturan Menteri Keuangan Nomor 190/PMK.05/2012, tentang Cara Pembayaran Dalam Pelaksanaan Anggaran Pendapatan dan Belanja Negara;',
            'Peraturan Menteri Ketenagakerjaan Nomor 1 Tahun 2022 tentang Organisasi dan Tata Kerja Unit Pelaksana Teknis di Kementerian Ketenagakerjaan (Berita Negara Republik Indonesia Tahun 2022 Peraturan Nomor 142) sebagaimana telah diubah dengan Peraturan Menteri Ketenagakerjaan Nomor 20 Tahun 2024 tentang Organisasi dan Tata Kerja Kementerian Ketenagakerjaan (Berita Negara Republik Indonesia Tahun 2024 Nomor 1038);',
            'Peraturan Menteri Tenaga Kerja dan Transmigrasi Nomor 8 Tahun 2014 tentang Pedoman Penyelenggaraan Pelatihan Berbasis Kompetensi (Berita Negara Republik Indonesia Tahun 2014 Nomor 586);',
            'Peraturan Menteri Ketenagakerjaan Nomor 20 Tahun 2024 tentang Organisasi dan Tata Kerja Kementerian Ketenagakerjaan (Berita Negara Republik Indonesia Tahun 2024 Nomor 1038);',
            'Peraturan Menteri Ketenagakerjaan Nomor 6 Tahun 2025 tentang Penyelenggaraan Pelatihan Vokasi (Berita Negara Republik Indonesia Tahun 2025 Nomor 448);',
        ];
    }

    private function buildDecisionDefaults(Collection $schedules): array
    {
        $programList = $this->buildProgramList($schedules);
        $dateRange = $this->formatRangeSentence($schedules);
        $count = $schedules->count();

        return [
            'kesatu' => trim("Menyelenggarakan Program Pelatihan Durasi Singkat untuk {$count} program pelatihan meliputi: {$programList} dimulai dari tanggal {$dateRange}."),
            'kedua' => 'Lokasi pelatihan di BPVP Surakarta, Jl. Bhayangkara No. 38 Surakarta.',
            'ketiga' => 'Menunjuk nama-nama yang tercantum dalam Lampiran Keputusan Kepala BPVP Surakarta ini sebagai panitia penyelenggaraan dan instruktur kegiatan.',
            'keempat' => 'Bagi peserta pelatihan yang dinyatakan kompeten sesuai dengan unit kompetensinya akan diberikan e-sertifikat.',
            'kelima' => 'Surat keputusan ini berlaku sejak tanggal ditetapkan dengan ketentuan apabila di kemudian hari terdapat kekeliruan dalam penetapan ini akan diperbaiki sebagaimana mestinya.',
        ];
    }

    public function buildProgramList(Collection $schedules): string
    {
        if ($schedules->isEmpty()) {
            return '-';
        }

        $courseMap = CourseClass::whereIn('id', $schedules->pluck('id'))->get()->keyBy('id');

        $programs = $schedules->map(function (TrainingSchedule $schedule) use ($courseMap) {
            $course = $courseMap->get($schedule->id);
            $curriculum = $this->resolveCurriculum($schedule->id);
            $totalJp = $this->resolveTotalJp($curriculum);
            $mode = $this->formatModeLabel($course?->format ?? $curriculum?->method);
            $name = $schedule->judul ?? '-';

            return trim($name . ' ' . $totalJp . ' JP (' . $mode . ')');
        })->values();

        return $programs->implode(', ');
    }

    private function buildCurriculumPages(Collection $schedules): Collection
    {
        if ($schedules->isEmpty()) {
            return collect();
        }

        return $schedules->map(function (TrainingSchedule $schedule) {
            $curriculum = $this->resolveCurriculum($schedule->id);
            $units = $curriculum
                ? CourseCurriculumUnit::where('course_curriculum_id', $curriculum->id)->orderBy('sort_order')->get()
                : collect();

            $totals = $this->resolveTotalsFromUnits($curriculum, $units);

            return [
                'kejuruan' => $schedule->program?->judul ?? '-',
                'program' => $schedule->judul ?? '-',
                'tahun' => $schedule->tahun ?: ($schedule->mulai ? Carbon::parse($schedule->mulai)->year : now()->year),
                'units' => $units,
                'total_theory' => $totals['theory'],
                'total_practice' => $totals['practice'],
                'total_all' => $totals['total'],
            ];
        });
    }

    private function resolveCurriculum(string $courseClassId): ?CourseCurriculum
    {
        $curriculum = CourseCurriculum::where('course_class_id', $courseClassId)
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->first();

        if ($curriculum) {
            return $curriculum;
        }

        return CourseCurriculum::where('course_class_id', $courseClassId)->orderByDesc('created_at')->first();
    }

    private function resolveTotalJp(?CourseCurriculum $curriculum): int
    {
        if (! $curriculum) {
            return 0;
        }
        $total = (int) $curriculum->total_jp_theory + (int) $curriculum->total_jp_practice;
        if ($total > 0) {
            return $total;
        }

        $units = CourseCurriculumUnit::where('course_curriculum_id', $curriculum->id)->get();

        return $units->sum(fn ($unit) => (int) $unit->jp_theory + (int) $unit->jp_practice);
    }

    private function resolveTotalsFromUnits(?CourseCurriculum $curriculum, Collection $units): array
    {
        $theory = $units->sum(fn ($unit) => (int) $unit->jp_theory);
        $practice = $units->sum(fn ($unit) => (int) $unit->jp_practice);
        $total = $theory + $practice;

        if ($curriculum && $total === 0) {
            $theory = (int) $curriculum->total_jp_theory;
            $practice = (int) $curriculum->total_jp_practice;
            $total = $theory + $practice;
        }

        return [
            'theory' => $theory,
            'practice' => $practice,
            'total' => $total,
        ];
    }

    private function formatModeLabel(?string $format): string
    {
        return match ($format) {
            'daring' => 'Online',
            'blended' => 'Blended',
            'luring' => 'Offline',
            default => 'Offline',
        };
    }

    public function formatWeekLabel(?string $batchId): string
    {
        if (! $batchId) {
            return '-';
        }
        $raw = strtoupper(trim($batchId));
        if (preg_match('/\\b[IVXLCDM]+\\b/', $raw, $match)) {
            return $match[0];
        }
        if (preg_match('/(\\d+)/', $raw, $match)) {
            return $this->toRoman((int) $match[1]);
        }

        return $raw;
    }

    private function toRoman(int $number): string
    {
        $map = [
            10 => 'X',
            9 => 'IX',
            5 => 'V',
            4 => 'IV',
            1 => 'I',
        ];
        if ($number <= 0) {
            return '-';
        }
        $result = '';
        foreach ($map as $value => $symbol) {
            while ($number >= $value) {
                $result .= $symbol;
                $number -= $value;
            }
        }

        return $result ?: '-';
    }

    private function formatRangeSentence(Collection $schedules): string
    {
        $start = $schedules->min('mulai');
        $end = $schedules->max('selesai');

        $start = $start ? Carbon::parse($start) : null;
        $end = $end ? Carbon::parse($end) : null;

        if (! $start && ! $end) {
            return '-';
        }
        if ($start && ! $end) {
            return $start->locale('id')->translatedFormat('d F Y');
        }
        if ($start && $end) {
            if ($start->isSameDay($end)) {
                return $start->locale('id')->translatedFormat('d F Y');
            }

            if ($start->year === $end->year && $start->month === $end->month) {
                return $start->locale('id')->translatedFormat('d') . ' ' . $start->locale('id')->translatedFormat('F') .
                    ' – ' . $end->locale('id')->translatedFormat('d F Y');
            }

            return $start->locale('id')->translatedFormat('d F Y') . ' – ' . $end->locale('id')->translatedFormat('d F Y');
        }

        return $end->locale('id')->translatedFormat('d F Y');
    }

    private function defaultSignedAt(Collection $schedules): ?string
    {
        $end = $schedules->max('selesai');
        if (! $end) {
            return now()->toDateString();
        }

        return Carbon::parse($end)->toDateString();
    }

    private function buildInstructorTeamDefaults(Collection $schedules): array
    {
        if ($schedules->isEmpty()) {
            return [];
        }
        $courseIds = $schedules->pluck('id');
        $courses = CourseClass::with('instructor')->whereIn('id', $courseIds)->get();

        return $courses
            ->pluck('instructor')
            ->filter()
            ->unique('id')
            ->map(fn ($instructor) => [
                'name' => $instructor->name,
                'nip' => '',
                'position' => 'Instruktur',
                'role' => 'Pengajar',
            ])
            ->values()
            ->all();
    }

    public function batchOptions(): Collection
    {
        return TrainingSchedule::query()
            ->whereNotNull('batch_id')
            ->distinct()
            ->orderBy('batch_id')
            ->pluck('batch_id');
    }

    public function yearOptions(): Collection
    {
        return TrainingSchedule::query()
            ->whereNotNull('tahun')
            ->distinct()
            ->orderBy('tahun')
            ->pluck('tahun');
    }

    public function validatePublishReadiness(Collection $schedules): array
    {
        $missing = [];
        foreach ($schedules as $schedule) {
            $curriculum = $this->resolveCurriculum($schedule->id);
            if (! $curriculum) {
                $missing[] = $schedule->judul ?? ($schedule->program?->judul ?? 'Program');
                continue;
            }
            $units = CourseCurriculumUnit::where('course_curriculum_id', $curriculum->id)->count();
            if ($units === 0) {
                $missing[] = $schedule->judul ?? ($schedule->program?->judul ?? 'Program');
            }
        }

        return array_values(array_unique($missing));
    }
}
