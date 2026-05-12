<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\TrainingDocumentation;
use App\Models\TrainingSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TrainingDocumentationReportController extends Controller
{
    public function index(Request $request)
    {
        $programFilter = $request->input('program_id');
        $scheduleFilter = $request->input('schedule_id');

        $scheduleQuery = TrainingSchedule::with('program')->orderByDesc('mulai');
        if ($programFilter) {
            $scheduleQuery->where('program_id', $programFilter);
        }
        if ($scheduleFilter) {
            $scheduleQuery->where('id', $scheduleFilter);
        }
        $schedules = $scheduleQuery->get();

        $scheduleOptions = TrainingSchedule::with('program')
            ->when($programFilter, fn ($q) => $q->where('program_id', $programFilter))
            ->orderByDesc('mulai')
            ->get()
            ->mapWithKeys(function (TrainingSchedule $schedule) {
                $label = $schedule->judul;
                if ($schedule->program?->judul) {
                    $label = $schedule->program->judul . ' • ' . $label;
                }
                if ($schedule->batch_id) {
                    $label .= ' • ' . $schedule->batch_id;
                }
                return [$schedule->id => $label];
            });

        $documentations = TrainingDocumentation::with(['trainingSchedule.program', 'owner'])
            ->when($programFilter, function ($q) use ($programFilter) {
                $q->whereHas('trainingSchedule', fn ($s) => $s->where('program_id', $programFilter));
            })
            ->when($scheduleFilter, fn ($q) => $q->where('training_schedule_id', $scheduleFilter))
            ->orderByDesc('documented_at')
            ->orderByDesc('updated_at')
            ->get();

        $docsBySchedule = $documentations->groupBy('training_schedule_id');
        $groups = $this->buildGroups($schedules, $docsBySchedule);
        $missingItems = $schedules->filter(function (TrainingSchedule $schedule) use ($docsBySchedule) {
            return $docsBySchedule->get($schedule->id, collect())->isEmpty();
        });
        if ($missingItems->isNotEmpty()) {
            $count = $missingItems->count();
            $label = $count === 1 ? '1 program' : $count . ' program';
            session()->now('warning', "{$label} pelatihan belum memiliki dokumentasi.");
        }

        return view('admin.reports.training_documentations', [
            'groups' => $groups,
            'programOptions' => Program::orderBy('judul')->pluck('judul', 'id'),
            'scheduleOptions' => $scheduleOptions,
            'programFilter' => $programFilter,
            'scheduleFilter' => $scheduleFilter,
            'missingItems' => $missingItems,
        ]);
    }

    private function buildGroups(Collection $schedules, Collection $docsBySchedule): Collection
    {
        if ($schedules->isEmpty()) {
            return collect();
        }

        return $schedules
            ->groupBy(fn (TrainingSchedule $schedule) => $schedule->program?->judul ?? 'LAINNYA')
            ->map(function (Collection $scheduleItems) use ($docsBySchedule) {
                return $scheduleItems->map(function (TrainingSchedule $schedule) use ($docsBySchedule) {
                    $startAt = $schedule->mulai ? Carbon::parse($schedule->mulai) : null;
                    $endAt = $schedule->selesai ? Carbon::parse($schedule->selesai) : null;
                    $dateRange = $startAt && $endAt
                        ? $startAt->translatedFormat('d M Y') . ' - ' . $endAt->translatedFormat('d M Y')
                        : ($startAt?->translatedFormat('d M Y') ?? '-');

                    return [
                        'id' => $schedule->id,
                        'title' => $schedule->judul ?? '-',
                        'batch' => $schedule->batch_id ?: '-',
                        'date_range' => $dateRange,
                        'docs' => $docsBySchedule->get($schedule->id, collect()),
                    ];
                })->values();
            });
    }
}
