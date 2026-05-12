<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empowerment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ActivityLogger;
use App\Support\HtmlSanitizer;

class EmpowermentController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $statusOptions = Empowerment::statuses();
        $statusFilter = $request->input('status');

        $query = Empowerment::orderBy('urutan');
        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }

        $items = $query->get();
        return view('admin.empowerment.index', [
            'items' => $items,
            'statusOptions' => $statusOptions,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function create()
    {
        return view('admin.empowerment.form', [
            'item' => new Empowerment(),
            'action' => route('admin.empowerment.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('empowerments', 'public');
            $data['gambar'] = '/storage/' . $path;
        }
        $this->applyWorkflow($request, $data);
        $item = Empowerment::create($data);

        $this->logger->log(
            $request->user(),
            'empowerment.created',
            "Konten pemberdayaan '{$item->judul}' ditambahkan",
            $item
        );
        return redirect()->route('admin.empowerment.index')->with('success', 'Konten pemberdayaan ditambahkan.');
    }

    public function edit(Empowerment $empowerment)
    {
        return view('admin.empowerment.form', [
            'item' => $empowerment,
            'action' => route('admin.empowerment.update', $empowerment->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Empowerment $empowerment)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('gambar')) {
            if ($empowerment->gambar && Storage::exists(str_replace('/storage/', 'public/', $empowerment->gambar))) {
                Storage::delete(str_replace('/storage/', 'public/', $empowerment->gambar));
            }
            $path = $request->file('gambar')->store('empowerments', 'public');
            $data['gambar'] = '/storage/' . $path;
        }
        $this->applyWorkflow($request, $data, $empowerment);
        $empowerment->update($data);

        $this->logger->log(
            $request->user(),
            'empowerment.updated',
            "Konten pemberdayaan '{$empowerment->judul}' diperbarui",
            $empowerment
        );
        return redirect()->route('admin.empowerment.index')->with('success', 'Konten pemberdayaan diperbarui.');
    }

    public function destroy(Empowerment $empowerment)
    {
        if ($empowerment->gambar && Storage::exists(str_replace('/storage/', 'public/', $empowerment->gambar))) {
            Storage::delete(str_replace('/storage/', 'public/', $empowerment->gambar));
        }
        $empowerment->delete();

        $this->logger->log(
            request()->user(),
            'empowerment.deleted',
            "Konten pemberdayaan '{$empowerment->judul}' dihapus",
            $empowerment
        );
        return redirect()->route('admin.empowerment.index')->with('success', 'Konten pemberdayaan dihapus.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'gambar' => 'nullable|image|max:2048',
            'status' => 'nullable|in:' . implode(',', array_keys(Empowerment::statuses())),
        ]);

        if (! empty($data['deskripsi'])) {
            $data['deskripsi'] = HtmlSanitizer::clean($data['deskripsi']);
        }

        return $data;
    }

    private function applyWorkflow(Request $request, array &$data, ?Empowerment $empowerment = null): void
    {
        $currentStatus = $empowerment ? $empowerment->status : ($request->user()->hasPermission('approve-content') ? 'published' : 'draft');
        $requestedStatus = $data['status'] ?? $currentStatus;

        if (! $request->user()->hasPermission('approve-content') && $requestedStatus === 'published') {
            $requestedStatus = 'pending';
        }

        $data['status'] = $requestedStatus ?: $currentStatus;

        if ($data['status'] === 'published') {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
            $data['published_at'] = $empowerment?->published_at ?? now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
    }
}
