<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Benefit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ActivityLogger;

class BenefitController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $statusOptions = Benefit::statuses();
        $statusFilter = $request->input('status');

        $query = Benefit::orderBy('urutan');
        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }

        $benefits = $query->get();
        return view('admin.benefit.index', [
            'benefits' => $benefits,
            'statusOptions' => $statusOptions,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function create()
    {
        return view('admin.benefit.form', [
            'benefit' => new Benefit(),
            'action' => route('admin.benefit.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('ikon_file')) {
            $path = $request->file('ikon_file')->store('benefits', 'public');
            $data['ikon'] = '/storage/' . $path;
        }
        $this->applyWorkflow($request, $data);
        $benefit = Benefit::create($data);

        $this->logger->log(
            $request->user(),
            'benefit.created',
            "Benefit '{$benefit->judul}' ditambahkan",
            $benefit
        );
        return redirect()->route('admin.benefit.index')->with('success', 'Benefit berhasil ditambahkan.');
    }

    public function edit(Benefit $benefit)
    {
        return view('admin.benefit.form', [
            'benefit' => $benefit,
            'action' => route('admin.benefit.update', $benefit->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Benefit $benefit)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('ikon_file')) {
            if ($benefit->ikon && Storage::exists(str_replace('/storage/', 'public/', $benefit->ikon))) {
                Storage::delete(str_replace('/storage/', 'public/', $benefit->ikon));
            }
            $path = $request->file('ikon_file')->store('benefits', 'public');
            $data['ikon'] = '/storage/' . $path;
        }
        $this->applyWorkflow($request, $data, $benefit);
        $benefit->update($data);

        $this->logger->log(
            $request->user(),
            'benefit.updated',
            "Benefit '{$benefit->judul}' diperbarui",
            $benefit
        );
        return redirect()->route('admin.benefit.index')->with('success', 'Benefit berhasil diperbarui.');
    }

    public function destroy(Benefit $benefit)
    {
        if ($benefit->ikon && Storage::exists(str_replace('/storage/', 'public/', $benefit->ikon))) {
            Storage::delete(str_replace('/storage/', 'public/', $benefit->ikon));
        }
        $benefit->delete();

        $this->logger->log(
            request()->user(),
            'benefit.deleted',
            "Benefit '{$benefit->judul}' dihapus",
            $benefit
        );
        return redirect()->route('admin.benefit.index')->with('success', 'Benefit berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'ikon' => 'nullable|string|max:255',
            'ikon_file' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'status' => 'nullable|in:' . implode(',', array_keys(\App\Models\Benefit::statuses())),
        ]);

        $data['urutan'] = $data['urutan'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function applyWorkflow(Request $request, array &$data, ?Benefit $benefit = null): void
    {
        $currentStatus = $benefit ? $benefit->status : ($request->user()->hasPermission('approve-content') ? 'published' : 'draft');
        $requestedStatus = $data['status'] ?? $currentStatus;

        if (! $request->user()->hasPermission('approve-content') && $requestedStatus === 'published') {
            $requestedStatus = 'pending';
        }

        $data['status'] = $requestedStatus ?: $currentStatus;

        if ($data['status'] === 'published') {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
            $data['published_at'] = $benefit?->published_at ?? now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
    }
}
