<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Services\ActivityLogger;
use App\Support\HtmlSanitizer;

class JobVacancyController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $statusOptions = JobVacancy::statuses();
        $statusFilter = $request->input('status');

        $query = JobVacancy::latest();
        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }

        $vacancies = $query->paginate(12)->withQueryString();
        return view('admin.lowongan.index', [
            'vacancies' => $vacancies,
            'statusOptions' => $statusOptions,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function create()
    {
        return view('admin.lowongan.form', [
            'vacancy' => new JobVacancy(),
            'action' => route('admin.lowongan.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        if ($request->hasFile('gambar')) {
            $data['gambar'] = '/storage/' . $request->file('gambar')->store('lowongan', 'public');
        }
        $this->applyWorkflow($request, $data);
        $vacancy = JobVacancy::create($data);

        $this->logger->log(
            $request->user(),
            'lowongan.created',
            "Lowongan '{$vacancy->judul}' ditambahkan",
            $vacancy,
            ['active' => $vacancy->is_active]
        );
        return redirect()->route('admin.lowongan.index')->with('success', 'Lowongan berhasil ditambahkan.');
    }

    public function edit(JobVacancy $lowongan)
    {
        return view('admin.lowongan.form', [
            'vacancy' => $lowongan,
            'action' => route('admin.lowongan.update', $lowongan->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, JobVacancy $lowongan)
    {
        $data = $this->validatedData($request);
        if ($request->hasFile('gambar')) {
            if ($lowongan->gambar) {
                $oldPath = str_replace('/storage/', '', $lowongan->gambar);
                Storage::disk('public')->delete($oldPath);
            }
            $data['gambar'] = '/storage/' . $request->file('gambar')->store('lowongan', 'public');
        }
        $this->applyWorkflow($request, $data, $lowongan);
        $lowongan->update($data);

        $this->logger->log(
            $request->user(),
            'lowongan.updated',
            "Lowongan '{$lowongan->judul}' diperbarui",
            $lowongan,
            ['active' => $lowongan->is_active]
        );
        return redirect()->route('admin.lowongan.index')->with('success', 'Lowongan diperbarui.');
    }

    public function destroy(JobVacancy $lowongan)
    {
        if ($lowongan->gambar) {
            $oldPath = str_replace('/storage/', '', $lowongan->gambar);
            Storage::disk('public')->delete($oldPath);
        }
        $lowongan->delete();

        $this->logger->log(
            request()->user(),
            'lowongan.deleted',
            "Lowongan '{$lowongan->judul}' dihapus",
            $lowongan
        );
        return redirect()->route('admin.lowongan.index')->with('success', 'Lowongan dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'perusahaan' => 'nullable|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'tipe_pekerjaan' => ['nullable', 'string', Rule::in(['Full Time', 'Part Time', 'Magang', 'Freelance', 'Kontrak'])],
            'deskripsi' => 'nullable|string',
            'kualifikasi' => 'nullable|string',
            'deadline' => 'nullable|date',
            'link_pendaftaran' => 'nullable|url',
            'is_active' => 'nullable|boolean',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'nullable|in:' . implode(',', array_keys(JobVacancy::statuses())),
        ]);

        $defaults = [
            'perusahaan' => 'Mitra Industri',
            'lokasi' => 'Lokasi fleksibel',
            'tipe_pekerjaan' => 'Full Time',
            'deskripsi' => 'Informasi lowongan akan diperbarui segera.',
            'kualifikasi' => "• Komunikatif dan proaktif\n• Bersedia ditempatkan sesuai kebutuhan",
            'link_pendaftaran' => 'https://siapkerja.kemnaker.go.id/app/lowongan',
        ];

        foreach ($defaults as $field => $value) {
            if (blank($data[$field] ?? null)) {
                $data[$field] = $value;
            }
        }

        if (! empty($data['deskripsi'])) {
            $data['deskripsi'] = HtmlSanitizer::clean($data['deskripsi']);
        }
        if (! empty($data['kualifikasi'])) {
            $data['kualifikasi'] = HtmlSanitizer::clean($data['kualifikasi']);
        }

        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }

    private function applyWorkflow(Request $request, array &$data, ?JobVacancy $vacancy = null): void
    {
        $currentStatus = $vacancy ? $vacancy->status : ($request->user()->hasPermission('approve-content') ? 'published' : 'draft');
        $requestedStatus = $data['status'] ?? $currentStatus;

        if (! $request->user()->hasPermission('approve-content') && $requestedStatus === 'published') {
            $requestedStatus = 'pending';
        }

        $data['status'] = $requestedStatus ?: $currentStatus;

        if ($data['status'] === 'published') {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
            $data['published_at'] = $vacancy?->published_at ?? now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
    }
}
