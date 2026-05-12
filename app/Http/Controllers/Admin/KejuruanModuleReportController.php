<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\KejuruanModule;
use App\Models\Program;
use App\Models\TrainingSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KejuruanModuleReportController extends Controller
{
    public function index(Request $request)
    {
        $programFilter = $request->input('program_id');
        $missingOnly = $request->boolean('missing_only');

        $programOptions = Program::orderBy('judul')->pluck('judul', 'id');

        $classes = CourseClass::query()
            ->whereNotNull('instructor_id')
            ->get(['id']);
        $classIds = $classes->pluck('id');

        $scheduleQuery = TrainingSchedule::with('program')->whereIn('id', $classIds);
        if ($programFilter) {
            $scheduleQuery->where('program_id', $programFilter);
        }
        $schedulePrograms = $scheduleQuery->get()
            ->pluck('program_id')
            ->filter()
            ->unique()
            ->values();

        $moduleProgramIds = KejuruanModule::query()
            ->when($programFilter, fn ($q) => $q->where('program_id', $programFilter))
            ->pluck('program_id')
            ->filter()
            ->unique()
            ->values();

        $programIds = $schedulePrograms->merge($moduleProgramIds)->unique()->values();
        $programNames = Program::whereIn('id', $programIds)->pluck('judul', 'id');

        $moduleStats = KejuruanModule::select('program_id', DB::raw('count(*) as total'), DB::raw('max(updated_at) as last_updated'))
            ->when($programFilter, fn ($q) => $q->where('program_id', $programFilter))
            ->groupBy('program_id')
            ->get()
            ->keyBy('program_id');

        $modules = KejuruanModule::with(['program', 'owner'])
            ->when($programFilter, fn ($q) => $q->where('program_id', $programFilter))
            ->orderByDesc('updated_at')
            ->get();
        $modulesByKejuruan = $modules->groupBy(fn ($module) => $module->program?->judul ?? 'LAINNYA');

        $rows = $programIds->map(function ($programId) use ($moduleStats, $programNames) {
            $stat = $moduleStats->get($programId);

            return [
                'program_id' => $programId,
                'program_name' => $programNames[$programId] ?? '-',
                'total' => (int) ($stat->total ?? 0),
                'last_updated' => $stat->last_updated ?? null,
            ];
        })->filter(function ($row) use ($missingOnly) {
            return ! $missingOnly || $row['total'] === 0;
        })->values();

        return view('admin.reports.kejuruan_modules', [
            'rows' => $rows,
            'programOptions' => $programOptions,
            'programFilter' => $programFilter,
            'missingOnly' => $missingOnly,
            'modules' => $modules,
            'modulesByKejuruan' => $modulesByKejuruan,
        ]);
    }

}
