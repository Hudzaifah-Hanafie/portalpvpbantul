<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificationScheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ActivityLogger;

class CertificationSchemeController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $statusOptions = CertificationScheme::statuses();
        $statusFilter = $request->input('status');

        $query = CertificationScheme::orderBy('category')->orderBy('urutan');
        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }

        $items = $query->get();
        return view('admin.certification_scheme.index', [
            'items' => $items,
            'statusOptions' => $statusOptions,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function create()
    {
        return view('admin.certification_scheme.form', [
            'item' => new CertificationScheme(),
            'action' => route('admin.certification-scheme.store'),
            'method' => 'POST',
            'categories' => $this->categories(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('certification/schemes', 'public');
            $data['image_path'] = '/storage/' . $path;
        }
        $this->applyWorkflow($request, $data);
        $scheme = CertificationScheme::create($data);

        $this->logger->log(
            $request->user(),
            'certification.scheme.created',
            "Skema sertifikasi '{$scheme->title}' ditambahkan",
            $scheme
        );
        return redirect()->route('admin.certification-scheme.index')->with('success', 'Skema sertifikasi ditambahkan.');
    }

    public function edit(CertificationScheme $certification_scheme)
    {
        return view('admin.certification_scheme.form', [
            'item' => $certification_scheme,
            'action' => route('admin.certification-scheme.update', $certification_scheme->id),
            'method' => 'PUT',
            'categories' => $this->categories(),
        ]);
    }

    public function update(Request $request, CertificationScheme $certification_scheme)
    {
        $data = $this->validatedData($request);
        if ($request->hasFile('gambar')) {
            if ($certification_scheme->image_path) {
                $old = str_replace('/storage/', '', $certification_scheme->image_path);
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('gambar')->store('certification/schemes', 'public');
            $data['image_path'] = '/storage/' . $path;
        }
        $this->applyWorkflow($request, $data, $certification_scheme);
        $certification_scheme->update($data);

        $this->logger->log(
            $request->user(),
            'certification.scheme.updated',
            "Skema sertifikasi '{$certification_scheme->title}' diperbarui",
            $certification_scheme
        );
        return redirect()->route('admin.certification-scheme.index')->with('success', 'Skema sertifikasi diperbarui.');
    }

    public function destroy(CertificationScheme $certification_scheme)
    {
        if ($certification_scheme->image_path) {
            $old = str_replace('/storage/', '', $certification_scheme->image_path);
            Storage::disk('public')->delete($old);
        }
        $certification_scheme->delete();

        $this->logger->log(
            request()->user(),
            'certification.scheme.deleted',
            "Skema sertifikasi '{$certification_scheme->title}' dihapus",
            $certification_scheme
        );
        return redirect()->route('admin.certification-scheme.index')->with('success', 'Skema sertifikasi dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'cta_text' => 'nullable|string|max:150',
            'cta_url' => 'nullable|string|max:255',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'nullable|in:' . implode(',', array_keys(CertificationScheme::statuses())),
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['urutan'] = $data['urutan'] ?? 0;
        unset($data['gambar']);

        return $data;
    }

    private function categories(): array
    {
        return [
            'kluster' => 'Kluster',
            'okupasi' => 'Okupasi',
        ];
    }

    private function applyWorkflow(Request $request, array &$data, ?CertificationScheme $scheme = null): void
    {
        $defaultStatus = $request->user()->hasPermission('approve-content') ? 'published' : 'draft';
        $currentStatus = $scheme ? $scheme->status : $defaultStatus;
        $requestedStatus = $data['status'] ?? $currentStatus;

        if (! $request->user()->hasPermission('approve-content')) {
            $requestedStatus = $requestedStatus === 'published' ? 'pending' : $requestedStatus;
        }

        $data['status'] = $requestedStatus ?: $currentStatus;

        if ($data['status'] === 'published') {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
            $data['published_at'] = $scheme?->published_at ?? now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
    }
}
