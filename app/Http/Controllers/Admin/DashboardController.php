<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniTracer;
use App\Models\Berita;
use App\Models\BrandingKpiReport;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\CourseSession;
use App\Models\CourseSubmission;
use App\Models\JobVacancy;
use App\Models\Pengumuman;
use App\Models\Pesan;
use App\Models\Program;
use App\Models\PpidRequest;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $metrics = [
            'berita' => Berita::count(),
            'program' => Program::count(),
            'pengumuman' => Pengumuman::count(),
            'lowongan' => JobVacancy::count(),
            'pesan' => Pesan::count(),
            'visits' => Visit::count(),
            'alumniTracer' => AlumniTracer::count(),
        ];

        $recentActivities = DB::table('activity_logs')
            ->select('action', 'description', 'created_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $beritaTrend = Berita::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        $visitTrend = Visit::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $trendLabels = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->toDateString());
        $beritaSeries = $trendLabels->map(fn ($date) => optional($beritaTrend->firstWhere('date', $date))->total ?? 0);
        $visitSeries = $trendLabels->map(fn ($date) => optional($visitTrend->firstWhere('date', $date))->total ?? 0);

        $pesanUnread = Pesan::where('is_read', false)->count();

        $funnelWindow = now()->subDays(30);
        $funnelStages = [
            'Kunjungan Portal' => Visit::where('created_at', '>=', $funnelWindow)->count(),
            'Kontak & Pesan' => Pesan::where('created_at', '>=', $funnelWindow)->count(),
            'Permohonan Informasi' => PpidRequest::where('created_at', '>=', $funnelWindow)->count(),
            'Tracer Alumni' => AlumniTracer::where('created_at', '>=', $funnelWindow)->count(),
        ];
        $funnelMax = max($funnelStages) ?: 1;

        $currentYear = now()->year;
        $minYear = $currentYear - 4;
        $cohortRaw = AlumniTracer::selectRaw('graduation_year, status, COUNT(*) as total')
            ->whereBetween('graduation_year', [$minYear, $currentYear])
            ->groupBy('graduation_year', 'status')
            ->get();

        $statusMap = [
            'employed' => 'Bekerja',
            'entrepreneur' => 'Wirausaha',
            'studying' => 'Studi',
            'seeking' => 'Mencari Kerja',
            'other' => 'Lainnya',
        ];
        $cohortYears = collect(range($minYear, $currentYear));
        $cohortDatasets = [];
        foreach ($statusMap as $key => $label) {
            $cohortDatasets[$key] = $cohortYears->map(function ($year) use ($cohortRaw, $key) {
                return (int) optional($cohortRaw->firstWhere(function ($row) use ($year, $key) {
                    return (int) $row->graduation_year === $year && $row->status === $key;
                }))->total;
            });
        }

        $latestBrandingKpi = BrandingKpiReport::orderByDesc('year')->orderByDesc('month')->first();
        $brandingIndicators = config('branding_kpi.indicators');

        $trainingMetrics = [
            'classes_total' => CourseClass::count(),
            'classes_active' => CourseClass::where('is_active', true)->count(),
            'enrollments_active' => CourseEnrollment::whereIn('status', ['approved', 'active'])->count(),
            'enrollments_pending' => CourseEnrollment::where('admin_status', 'pending')->count(),
            'pending_submissions' => CourseSubmission::where('status', 'submitted')->count(),
        ];
        $upcomingSessions = CourseSession::where('start_at', '>=', now())
            ->orderBy('start_at')
            ->limit(3)
            ->get();

        return view('admin.dashboard', [
            'metrics' => $metrics,
            'recentActivities' => $recentActivities,
            'trendLabels' => $trendLabels,
            'beritaSeries' => $beritaSeries,
            'visitSeries' => $visitSeries,
            'visitTrend' => $visitTrend,
            'pesanUnread' => $pesanUnread,
            'funnelStages' => $funnelStages,
            'funnelMax' => $funnelMax,
            'cohortYears' => $cohortYears,
            'cohortDatasets' => $cohortDatasets,
            'cohortStatusMap' => $statusMap,
            'latestBrandingKpi' => $latestBrandingKpi,
            'brandingIndicators' => $brandingIndicators,
            'trainingMetrics' => $trainingMetrics,
            'upcomingSessions' => $upcomingSessions,
        ]);
    }
}
