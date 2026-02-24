<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrainingServiceController extends Controller
{
    public function index(Request $request)
    {
        $statusOptions = TrainingService::statuses();
        $statusFilter = $request->input('status');

        $query = TrainingService::orderBy('urutan');
        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }

        $services = $query->get();
        return view('admin.training_service.index', [
            'services' => $services,
            'statusOptions' => $statusOptions,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function create()
    {
        return view('admin.training_service.form', [
            'service' => new TrainingService(),
            'action' => route('admin.training-service.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('training_services', 'public');
            $data['gambar'] = '/storage/' . $path;
        }
        $data['is_active'] = $request->boolean('is_active');
        $this->applyWorkflow($request, $data);
        TrainingService::create($data);
        return redirect()->route('admin.training-service.index')->with('success', 'Layanan pelatihan ditambahkan.');
    }

    public function edit(TrainingService $training_service)
    {
        return view('admin.training_service.form', [
            'service' => $training_service,
            'action' => route('admin.training-service.update', $training_service->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, TrainingService $training_service)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('gambar')) {
            if ($training_service->gambar && Storage::exists(str_replace('/storage/', 'public/', $training_service->gambar))) {
                Storage::delete(str_replace('/storage/', 'public/', $training_service->gambar));
            }
            $path = $request->file('gambar')->store('training_services', 'public');
            $data['gambar'] = '/storage/' . $path;
        }
        $data['is_active'] = $request->boolean('is_active');
        $this->applyWorkflow($request, $data, $training_service);
        $training_service->update($data);
        return redirect()->route('admin.training-service.index')->with('success', 'Layanan pelatihan diperbarui.');
    }

    public function destroy(TrainingService $training_service)
    {
        if ($training_service->gambar && Storage::exists(str_replace('/storage/', 'public/', $training_service->gambar))) {
            Storage::delete(str_replace('/storage/', 'public/', $training_service->gambar));
        }
        $training_service->delete();
        return redirect()->route('admin.training-service.index')->with('success', 'Layanan pelatihan dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'judul' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:5000',
            'fasilitas' => 'nullable|string|max:5000',
            'urutan' => 'required|integer|min:0|max:999',
            'is_active' => 'nullable|boolean',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'nullable|in:' . implode(',', array_keys(\App\Models\TrainingService::statuses())),
        ], [
            'judul.max' => 'Judul maksimal 100 karakter.',
            'urutan.max' => 'Urutan maksimal 999.',
            'gambar.image' => 'File harus berupa gambar.',
            'gambar.mimes' => 'Format gambar harus JPG, JPEG, atau PNG.',
            'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
        ]);
    }

    private function applyWorkflow(Request $request, array &$data, ?TrainingService $service = null): void
    {
        $currentStatus = $service ? $service->status : ($request->user()->hasPermission('approve-content') ? 'published' : 'draft');
        $requestedStatus = $data['status'] ?? $currentStatus;

        if (! $request->user()->hasPermission('approve-content') && $requestedStatus === 'published') {
            $requestedStatus = 'pending';
        }

        $data['status'] = $requestedStatus ?: $currentStatus;

        if ($data['status'] === 'published') {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
            $data['published_at'] = $service?->published_at ?? now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
    }
}
