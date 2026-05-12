<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RestrictsToInstructorClasses;
use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\TaskLetter;
use App\Models\TrainingSchedule;
use App\Models\UserEducation;
use App\Models\UserProfile;
use App\Models\User;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CourseNominativeExport;

class CourseNominativeController extends Controller
{
    use RestrictsToInstructorClasses;

    public function index(Request $request)
    {
        $statusFilter = $request->input('status');
        if ($statusFilter === '') {
            $statusFilter = null;
        }
        $adminStatusFilter = $request->input('admin_status');
        $kejuruanFilter = $request->input('kejuruan_id');
        $batchFilter = $request->input('batch_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $instructorFilter = $request->input('instructor_id');

        $classQuery = $this->scopeClassesForUser(CourseClass::query(), $request->user());

        if ($instructorFilter) {
            $classQuery->where('instructor_id', $instructorFilter);
        }
        $allowedClassIds = $classQuery->pluck('course_classes.id');

        $scheduleQuery = TrainingSchedule::with('program')->orderBy('mulai');
        if ($this->isInstructorUser($request->user()) || $instructorFilter) {
            if ($allowedClassIds->isEmpty()) {
                $scheduleQuery->whereRaw('1 = 0');
            } else {
                $scheduleQuery->whereIn('id', $allowedClassIds);
            }
        }
        if ($kejuruanFilter) {
            $scheduleQuery->where('program_id', $kejuruanFilter);
        }
        if ($batchFilter) {
            $scheduleQuery->where('batch_id', $batchFilter);
        }
        if ($dateFrom) {
            $scheduleQuery->whereDate('mulai', '>=', $dateFrom);
        }
        if ($dateTo) {
            $scheduleQuery->whereDate('selesai', '<=', $dateTo);
        }
        $schedules = $scheduleQuery->get();

        $programOptions = Program::orderBy('judul')->pluck('judul', 'id');
        $programs = Program::orderBy('judul')
            ->when($kejuruanFilter, fn ($query) => $query->where('id', $kejuruanFilter))
            ->get();
        $kejuruanGroups = $this->buildKejuruanGroups(
            $programs,
            $schedules,
            $statusFilter,
            $adminStatusFilter
        );

        $instructors = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['instructor', 'instruktur']);
            })
            ->orderBy('name')
            ->pluck('name', 'id');

        $batchOptions = TrainingSchedule::query()
            ->whereNotNull('batch_id')
            ->distinct()
            ->orderBy('batch_id')
            ->pluck('batch_id', 'batch_id');

        return view('admin.course_nominative.index', [
            'statusFilter' => $statusFilter,
            'adminStatusFilter' => $adminStatusFilter,
            'kejuruanFilter' => $kejuruanFilter,
            'programOptions' => $programOptions,
            'batchFilter' => $batchFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'instructorFilter' => $instructorFilter,
            'instructors' => $instructors,
            'batchOptions' => $batchOptions,
            'adminStatuses' => CourseEnrollment::adminStatuses(),
            'kejuruanGroups' => $kejuruanGroups,
        ]);
    }

    public function print(Request $request)
    {
        $payload = $this->buildPayload($request);
        return view('admin.course_nominative.print', $payload);
    }

    public function export(Request $request)
    {
        $payload = $this->buildPayload($request);
        $title = $payload['programPelatihan'] ?? $payload['course']->title ?? 'nominatif';
        $fileName = 'nominatif-' . Str::slug($title) . '.xlsx';

        return Excel::download(new CourseNominativeExport($payload['rows'], $fileName), $fileName);
    }

    public function pdf(Request $request)
    {
        $payload = $this->buildPayload($request);

        if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return back()->with('error', 'Library PDF belum terpasang. Jalankan composer require barryvdh/laravel-dompdf lalu coba lagi.');
        }

        $title = $payload['programPelatihan'] ?? $payload['course']->title ?? 'nominatif';
        $fileName = 'nominatif-' . Str::slug($title) . '-' . time() . '.pdf';

        // Utus pekerja (Worker) untuk memproses PDF berat di latar belakang
        \App\Jobs\GenerateNominativePdfJob::dispatch($payload, $fileName, request()->user()->id);

        return back()->with('success', 'File PDF Daftar Nominatif sedang diproses di belakang layar oleh peladen asinkronus. Silakan tunggu beberapa saat dan periksa menu Notifikasi Anda (pastikan proses Worker menyala).');
    }

    private function buildPayload(Request $request): array
    {
        $classId = $request->input('class_id');
        $status = $request->input('status');
        if ($status === '') {
            $status = null;
        }
        $adminStatus = $request->input('admin_status');

        $this->ensureInstructorOwnsClassId($request->user(), $classId);

        $course = CourseClass::with('sessions', 'instructor')->find($classId);
        if (! $course) {
            $schedule = TrainingSchedule::with('program')->find($classId);
            if ($schedule) {
                $course = $this->ensureCourseClassForSchedule($schedule);
            } else {
                abort(404);
            }
        }
        $participantsQuery = CourseEnrollment::with('user')
            ->where('course_class_id', $classId);

        if ($status) {
            $participantsQuery->where('status', $status);
        }
        if ($adminStatus) {
            $participantsQuery->where('admin_status', $adminStatus);
        }

        $participants = $participantsQuery->get()
            ->sortBy(fn ($row) => $row->user?->name ?? '');

        $profiles = UserProfile::whereIn('user_id', $participants->pluck('user_id'))->get()->keyBy('user_id');
        $educations = UserEducation::whereIn('user_id', $participants->pluck('user_id'))
            ->orderByDesc('finish_year')
            ->orderByDesc('start_year')
            ->get()
            ->groupBy('user_id');

        $rows = $this->buildRows($participants, $profiles, $educations);

        $schedule = $this->resolveSchedule($course);
        $program = $schedule?->program;

        $startAt = $schedule?->mulai ? Carbon::parse($schedule->mulai) : null;
        $endAt = $schedule?->selesai ? Carbon::parse($schedule->selesai) : null;

        if (! $startAt && ! $endAt) {
            $sessionStart = $course->sessions?->min('start_at');
            $sessionEnd = $course->sessions?->max('end_at');
            $startAt = $sessionStart ? Carbon::parse($sessionStart) : null;
            $endAt = $sessionEnd ? Carbon::parse($sessionEnd) : null;
        }

        $kejuruan = $program?->judul ?? '-';
        $programPelatihan = $schedule?->judul ?? $course->title ?? '-';
        $lokasiPelatihan = $schedule?->lokasi ?: ($course->format === 'luring' ? 'BPVP SURAKARTA' : 'DARING');
        $jenisPelatihan = $this->formatJenisPelatihan($course->format);
        $periodeTahun = $schedule?->tahun ?? ($startAt?->year ?? now()->year);
        $periodeWeek = $this->formatWeekLabel($schedule?->batch_id ?? $request->input('batch_id'));

        $taskLetter = TaskLetter::where('course_class_id', $course->id)->latest()->first();
        $signedCity = $taskLetter?->signed_city ?? 'Surakarta';
        $signedAt = $taskLetter?->signed_at ?? ($endAt ?? now());
        $signatoryPosition = $taskLetter?->signatory_position ?? 'KEPALA';
        $signatoryName = $taskLetter?->signatory_name ?? '';
        $signatoryNip = $taskLetter?->signatory_nip ?? '';

        return [
            'course' => $course,
            'schedule' => $schedule,
            'rows' => $rows,
            'jumlahPeserta' => $rows->count(),
            'tanggalPelaksanaan' => $this->formatDateRange($startAt, $endAt),
            'kejuruan' => $kejuruan,
            'programPelatihan' => $programPelatihan,
            'lokasiPelatihan' => $lokasiPelatihan,
            'jenisPelatihan' => $jenisPelatihan,
            'periodeTahun' => $periodeTahun,
            'periodeWeek' => $periodeWeek,
            'signedCity' => $signedCity,
            'signedAt' => $signedAt,
            'signatoryPosition' => $signatoryPosition,
            'signatoryName' => $signatoryName,
            'signatoryNip' => $signatoryNip,
            'status' => $status,
            'adminStatus' => $adminStatus,
        ];
    }

    private function buildRows(Collection $participants, Collection $profiles, Collection $educations): Collection
    {
        return $participants->values()->map(function ($enroll, $idx) use ($profiles, $educations) {
            $profile = $profiles->get($enroll->user_id);
            $education = $educations->get($enroll->user_id)?->first();

            return [
                'no' => $idx + 1,
                'no_induk' => $profile?->identity_number ?: ($enroll->user?->nik ?? '-'),
                'nama' => $enroll->user?->name ?? '-',
                'ttl' => $this->formatTtl($profile?->birth_place, $profile?->birth_date),
                'gender' => $this->formatGender($profile?->gender),
                'education' => $education?->graduate_name ?? '-',
                'address' => $profile?->address ?: ($profile?->domicile_address ?? '-'),
                'phone' => $profile?->phone ?? '-',
                'note' => '',
            ];
        });
    }

    private function resolveSchedule(CourseClass $course): ?TrainingSchedule
    {
        $schedule = TrainingSchedule::with('program')->find($course->id);
        if ($schedule) {
            return $schedule;
        }

        return TrainingSchedule::with('program')
            ->where('judul', $course->title)
            ->orderByDesc('mulai')
            ->first();
    }

    private function ensureCourseClassForSchedule(TrainingSchedule $schedule): CourseClass
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

        return $class;
    }

    private function buildKejuruanGroups(
        Collection $programs,
        Collection $schedules,
        ?string $statusFilter,
        ?string $adminStatusFilter
    ): Collection {
        $classMap = CourseClass::with('instructor')
            ->whereIn('id', $schedules->pluck('id'))
            ->get()
            ->keyBy('id');

        $baseGroups = $programs->mapWithKeys(fn ($program) => [$program->judul => collect()]);
        $grouped = $schedules->groupBy(fn ($schedule) => $schedule->program?->judul ?? 'LAINNYA');
        $merged = $baseGroups->merge($grouped);

        return $merged->map(function ($items, $kejuruan) use ($classMap, $statusFilter, $adminStatusFilter) {
            if ($items->isEmpty()) {
                return collect();
            }

            return $items->map(function (TrainingSchedule $schedule) use ($classMap, $statusFilter, $adminStatusFilter) {
                $class = $classMap->get($schedule->id);
                $instructor = $class?->instructor?->name ?? '-';
                $startAt = $schedule->mulai ? Carbon::parse($schedule->mulai) : null;
                $endAt = $schedule->selesai ? Carbon::parse($schedule->selesai) : null;
                $query = array_filter([
                    'class_id' => $schedule->id,
                    'status' => $statusFilter,
                    'admin_status' => $adminStatusFilter,
                ], fn ($value) => $value !== null && $value !== '');

                return [
                    'id' => $schedule->id,
                    'title' => $schedule->judul ?? '-',
                    'batch' => $schedule->batch_id ?: '-',
                    'tanggal' => $this->formatDateRange($startAt, $endAt),
                    'instructor' => $instructor,
                    'query' => $query,
                ];
            });
        });
    }

    private function formatTtl(?string $place, ?string $date): string
    {
        if (!$place && !$date) {
            return '-';
        }
        $formatted = '-';
        if ($date) {
            try {
                $formatted = Carbon::parse($date)->locale('id')->translatedFormat('d F Y');
            } catch (\Exception $e) {
                $formatted = $date;
            }
        }

        return trim(($place ?: '-') . ', ' . $formatted);
    }

    private function formatGender(?string $value): string
    {
        if ($value === null) {
            return '-';
        }
        $normalized = strtolower(trim($value));
        if (in_array($normalized, ['10', 'l', 'laki-laki', 'laki', 'male', 'm'], true)) {
            return 'Laki-laki';
        }
        if (in_array($normalized, ['20', 'p', 'perempuan', 'female', 'f'], true)) {
            return 'Perempuan';
        }

        return $value;
    }

    private function formatDateRange(?Carbon $start, ?Carbon $end): string
    {
        if (!$start && !$end) {
            return '-';
        }
        if ($start && !$end) {
            return $start->locale('id')->translatedFormat('d F Y');
        }
        if ($start && $end) {
            return $start->locale('id')->translatedFormat('d') . ' S/D ' . $end->locale('id')->translatedFormat('d F Y');
        }

        return $end->locale('id')->translatedFormat('d F Y');
    }

    private function formatJenisPelatihan(?string $format): string
    {
        return match ($format) {
            'luring' => 'INSTITUTIONAL (OFFLINE)',
            'daring' => 'INSTITUTIONAL (ONLINE)',
            'blended' => 'INSTITUTIONAL (BLENDED)',
            default => 'INSTITUTIONAL',
        };
    }

    private function formatWeekLabel(?string $batchId): string
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
}
