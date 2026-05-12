<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DecisionLetter;
use App\Models\Program;
use App\Services\Letter\DecisionLetterService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DecisionLetterController extends Controller
{
    public function __construct(private DecisionLetterService $letterService) {}

    public function index()
    {
        $letters = DecisionLetter::orderByDesc('created_at')->paginate(20);

        return view('admin.decision_letter.index', [
            'letters' => $letters,
        ]);
    }

    public function create(Request $request)
    {
        $filters = $this->letterService->filtersFromRequest($request);
        $schedules = $this->letterService->querySchedules($filters);
        $defaults = $this->letterService->buildDefaults($schedules);
        $missingCurricula = $this->letterService->validatePublishReadiness($schedules);

        return view('admin.decision_letter.form', [
            'letter' => new DecisionLetter($defaults),
            'filters' => $filters,
            'schedules' => $schedules,
            'missingCurricula' => $missingCurricula,
            'batchOptions' => $this->letterService->batchOptions(),
            'yearOptions' => $this->letterService->yearOptions(),
            'programOptions' => Program::orderBy('judul')->pluck('judul', 'id'),
            'action' => route('admin.decision-letter.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->letterService->validateData($request);
        $schedules = $this->letterService->querySchedules($data);
        if ($schedules->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['batch_id' => 'Tidak ada jadwal pelatihan yang cocok dengan filter yang dipilih.']);
        }
        if (($data['status'] ?? 'draft') === 'published') {
            $missing = $this->letterService->validatePublishReadiness($schedules);
            if (! empty($missing)) {
                return back()
                    ->withInput()
                    ->withErrors(['status' => 'Tidak bisa terbit. Kurikulum/unit kompetensi belum lengkap untuk: ' . implode(', ', $missing)]);
            }
        }

        $payload = $this->letterService->hydrateData($data, $schedules, $request->user()->id);
        $letter = DecisionLetter::create($payload);

        return redirect()
            ->route('admin.decision-letter.show', $letter->id)
            ->with('success', 'Surat Keputusan berhasil dibuat.');
    }

    public function show(DecisionLetter $decision_letter)
    {
        $schedules = $this->letterService->loadSchedules($decision_letter);

        return view('admin.decision_letter.show', [
            'letter' => $decision_letter,
            'schedules' => $schedules,
            'programList' => $this->letterService->buildProgramList($schedules),
            'periodWeek' => $this->letterService->formatWeekLabel($decision_letter->batch_id),
        ]);
    }

    public function edit(Request $request, DecisionLetter $decision_letter)
    {
        $filters = $this->letterService->filtersFromRequest($request);
        if (! array_filter($filters)) {
            $filters = [
                'batch_id' => $decision_letter->batch_id,
                'period_year' => $decision_letter->period_year,
            ];
        }

        $schedules = $this->letterService->querySchedules($filters);
        if ($schedules->isEmpty()) {
            $schedules = $this->letterService->loadSchedules($decision_letter);
        }
        $defaults = $this->letterService->buildDefaults($schedules, $decision_letter);
        $missingCurricula = $this->letterService->validatePublishReadiness($schedules);

        return view('admin.decision_letter.form', [
            'letter' => $decision_letter->fill($defaults),
            'filters' => $filters,
            'schedules' => $schedules,
            'missingCurricula' => $missingCurricula,
            'batchOptions' => $this->letterService->batchOptions(),
            'yearOptions' => $this->letterService->yearOptions(),
            'programOptions' => Program::orderBy('judul')->pluck('judul', 'id'),
            'action' => route('admin.decision-letter.update', $decision_letter->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, DecisionLetter $decision_letter)
    {
        $data = $this->letterService->validateData($request);
        $schedules = $this->letterService->querySchedules($data);
        $hasFilters = ! empty($data['batch_id'])
            || ! empty($data['period_year'])
            || ! empty($data['program_id'])
            || ! empty($data['date_from'])
            || ! empty($data['date_to']);

        if ($schedules->isEmpty() && ! $hasFilters) {
            $schedules = $this->letterService->loadSchedules($decision_letter);
        }

        if ($schedules->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['batch_id' => 'Tidak ada jadwal pelatihan yang cocok dengan filter yang dipilih.']);
        }
        if (($data['status'] ?? $decision_letter->status) === 'published') {
            $missing = $this->letterService->validatePublishReadiness($schedules);
            if (! empty($missing)) {
                return back()
                    ->withInput()
                    ->withErrors(['status' => 'Tidak bisa terbit. Kurikulum/unit kompetensi belum lengkap untuk: ' . implode(', ', $missing)]);
            }
        }

        $payload = $this->letterService->hydrateData($data, $schedules, $decision_letter->created_by);
        $decision_letter->update($payload);

        return redirect()
            ->route('admin.decision-letter.show', $decision_letter->id)
            ->with('success', 'Surat Keputusan berhasil diperbarui.');
    }

    public function print(DecisionLetter $decision_letter)
    {
        $payload = $this->letterService->buildPrintPayload($decision_letter);

        return view('admin.decision_letter.print', $payload);
    }

    public function preview(Request $request)
    {
        $filters = $this->letterService->filtersFromRequest($request);
        $schedules = $this->letterService->querySchedules($filters);
        $missing = $this->letterService->validatePublishReadiness($schedules);

        return response()->json([
            'count' => $schedules->count(),
            'missing' => $missing,
        ]);
    }

    public function pdf(DecisionLetter $decision_letter)
    {
        if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return back()->with('error', 'Library PDF belum terpasang. Jalankan composer require barryvdh/laravel-dompdf lalu coba lagi.');
        }

        // Utus pekerja (Worker) untuk memproses PDF berat di latar belakang
        \App\Jobs\GenerateDecisionLetterPdfJob::dispatch($decision_letter, request()->user()->id);

        return back()->with('success', 'File PDF Surat Keputusan sedang diproses di belakang layar oleh peladen asinkronus. Silakan tunggu dan periksa menu Notifikasi Anda sesaat lagi (pastikan proses Worker menyala).');
    }
}
