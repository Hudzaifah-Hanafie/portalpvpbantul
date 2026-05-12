<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LearningReportService;
use Illuminate\Http\Request;

class LearningReportController extends Controller
{
    public function __construct(private LearningReportService $reportService) {}

    public function index(Request $request)
    {
        $groupBy = $request->input('group_by', 'kejuruan');
        $classFilter = $request->input('class_id');
        $healthFilter = $request->input('health');
        $phaseFilter = $request->input('phase');
        $today = now()->startOfDay();

        $data = $this->reportService->generateReport($groupBy, $classFilter, $today);
        $rows = $data['rows'];
        $classes = $data['classes'];

        if ($phaseFilter) {
            $rows = $rows->where('phase', $phaseFilter)->values();
        }
        if ($healthFilter) {
            $rows = $rows->where('monitoring_status', $healthFilter)->values();
        }

        $overview = $this->reportService->buildOverview($rows);
        $alerts = $this->reportService->buildAlerts($rows);

        $summary = $groupBy === 'kelas'
            ? $this->reportService->normalizeClassRows($rows)
            : $this->reportService->normalizeGroupedRows($rows, 'kejuruan');
        $rowsByKejuruan = $rows->groupBy('kejuruan');
        $summaryByKejuruan = $groupBy === 'kejuruan' ? $summary->keyBy('label') : collect();

        return view('admin.reports.learning-summary', [
            'summary' => $summary,
            'groupBy' => $groupBy,
            'classes' => $classes->pluck('title', 'id'),
            'classFilter' => $classFilter,
            'healthFilter' => $healthFilter,
            'phaseFilter' => $phaseFilter,
            'overview' => $overview,
            'alerts' => $alerts,
            'rowsByKejuruan' => $rowsByKejuruan,
            'summaryByKejuruan' => $summaryByKejuruan,
        ]);
    }
}
