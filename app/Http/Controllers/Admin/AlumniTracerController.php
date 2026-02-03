<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AlumniTracerVerified;
use App\Models\AlumniTracer;
use App\Support\EmailSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Collection;

class AlumniTracerController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $year = $request->input('year');

        $query = AlumniTracer::query()->orderByDesc('created_at');
        if ($status) {
            $query->where('status', $status);
        }
        if ($year) {
            $query->where('graduation_year', $year);
        }

        $responses = $query->paginate(15)->withQueryString();

        $metrics = [
            'total' => AlumniTracer::count(),
            'employed' => AlumniTracer::where('status', 'employed')->count(),
            'entrepreneur' => AlumniTracer::where('status', 'entrepreneur')->count(),
            'studying' => AlumniTracer::where('status', 'studying')->count(),
        ];

        $years = AlumniTracer::select('graduation_year')->whereNotNull('graduation_year')->distinct()->orderByDesc('graduation_year')->pluck('graduation_year');

        return view('admin.alumni_tracer.index', compact('responses', 'metrics', 'status', 'year', 'years'));
    }

    public function dashboard()
    {
        $statusLabels = [
            'employed' => 'Bekerja',
            'entrepreneur' => 'Wirausaha',
            'studying' => 'Melanjutkan Studi',
            'seeking' => 'Mencari Kerja',
            'other' => 'Lainnya',
        ];
        $genderLabels = [
            'male' => 'Laki-laki',
            'female' => 'Perempuan',
            'other' => 'Lainnya/Tidak sebut',
            null => '(blank)',
        ];
        $educationLabels = [
            'sd' => 'SD',
            'smp' => 'SMP',
            'sma' => 'SMA',
            'smk' => 'SMK',
            'd1' => 'D1',
            'd2' => 'D2',
            'd3' => 'D3',
            'd4' => 'D4',
            's1' => 'S1',
            's2' => 'S2',
            's3' => 'S3',
            'other' => 'Lainnya',
            null => '(blank)',
        ];

        $statusCounts = AlumniTracer::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $educationByGender = AlumniTracer::selectRaw('education_level, gender, count(*) as total')
            ->groupBy('education_level', 'gender')
            ->get()
            ->groupBy('gender');

        $statusChart = [
            'labels' => collect($statusLabels)->keys(),
            'data' => collect($statusLabels)->map(fn($label, $key) => $statusCounts[$key] ?? 0)->values(),
            'displayLabels' => array_values($statusLabels),
        ];

        $educationChart = $this->buildEducationChart($educationByGender, $educationLabels, $genderLabels);

        return view('admin.alumni_tracer.dashboard', [
            'statusChart' => $statusChart,
            'educationChart' => $educationChart,
            'statusLabels' => $statusLabels,
            'genderLabels' => $genderLabels,
            'educationLabels' => $educationLabels,
        ]);
    }

    private function buildEducationChart(Collection $educationByGender, array $educationLabels, array $genderLabels): array
    {
        $educationKeys = collect($educationLabels)->keys()->values();
        $datasets = [];

        foreach ($genderLabels as $genderKey => $genderTitle) {
            $data = $educationKeys->map(function ($eduKey) use ($educationByGender, $genderKey) {
                /** @var Collection $group */
                $group = $educationByGender->get($genderKey, collect());
                return $group->firstWhere('education_level', $eduKey)->total ?? 0;
            })->values();

            $datasets[] = [
                'label' => $genderTitle,
                'data' => $data,
            ];
        }

        return [
            'labels' => $educationKeys,
            'displayLabels' => array_values($educationLabels),
            'datasets' => $datasets,
        ];
    }

    public function show(AlumniTracer $alumni_tracer)
    {
        return view('admin.alumni_tracer.show', compact('alumni_tracer'));
    }

    public function verify(AlumniTracer $alumni_tracer)
    {
        $alumni_tracer->update([
            'is_verified' => true,
            'verified_at' => Carbon::now(),
        ]);

        if ($alumni_tracer->email && EmailSettings::notificationsEnabled()) {
            Mail::to($alumni_tracer->email)->send(new AlumniTracerVerified($alumni_tracer));
        }

        return redirect()->route('admin.alumni-tracer.index')->with('success', 'Data alumni berhasil diverifikasi.');
    }

    public function destroy(AlumniTracer $alumni_tracer)
    {
        $alumni_tracer->delete();
        return redirect()->route('admin.alumni-tracer.index')->with('success', 'Data tracer dihapus.');
    }

    public function export(): StreamedResponse
    {
        $filename = 'alumni-tracers-' . now()->format('YmdHis') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = [
            'Nomor Alumni',
            'Nama',
            'Email',
            'Program',
            'Status',
            'Perusahaan',
            'Gaji',
            'Lulus',
            'Terverifikasi',
            'Tanggal Input',
        ];

        $response = response()->streamDownload(function () use ($columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            AlumniTracer::orderByDesc('created_at')->chunk(200, function ($tracers) use ($handle) {
                foreach ($tracers as $tracer) {
                    fputcsv($handle, [
                        $tracer->alumni_number,
                        $tracer->full_name,
                        $tracer->email,
                        $tracer->program_name,
                        ucfirst($tracer->status),
                        $tracer->company_name,
                        $tracer->salary_range,
                        $tracer->graduation_year,
                        $tracer->is_verified ? 'Ya' : 'Belum',
                        $tracer->created_at->toDateTimeString(),
                    ]);
                }
            });
            fclose($handle);
        }, $filename, $headers);

        return $response;
    }
}
