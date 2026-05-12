<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicServiceFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ActivityLogger;
use App\Support\HtmlSanitizer;

class PublicServiceFlowController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $statusFilter = $request->input('status');
        $query = PublicServiceFlow::orderBy('category')->orderBy('urutan');

        if ($statusFilter && array_key_exists($statusFilter, PublicServiceFlow::statuses())) {
            $query->where('status', $statusFilter);
        }

        $flows = $query->get();

        return view('admin.pelayanan.flows.index', [
            'flows' => $flows,
            'statusFilter' => $statusFilter,
            'statusOptions' => PublicServiceFlow::statuses(),
        ]);
    }

    public function create()
    {
        $flow = new PublicServiceFlow(['is_active' => true]);
        return view('admin.pelayanan.flows.form', compact('flow'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('image')) {
            $data['image'] = '/storage/' . $request->file('image')->store('pelayanan', 'public');
        }

        $this->applyWorkflow($request, $data);
        $flow = PublicServiceFlow::create($data);

        $this->logger->log(
            $request->user(),
            'pelayanan.flow.created',
            "Alur '{$flow->title}' ditambahkan",
            $flow
        );

        return redirect()->route('admin.public-service-flow.index')->with('success', 'Alur pelayanan ditambahkan.');
    }

    public function edit(PublicServiceFlow $public_service_flow)
    {
        return view('admin.pelayanan.flows.form', ['flow' => $public_service_flow]);
    }

    public function update(Request $request, PublicServiceFlow $public_service_flow)
    {
        $data = $this->validateData($request);

        if ($request->hasFile('image')) {
            if ($public_service_flow->image) {
                $old = str_replace('/storage/', '', $public_service_flow->image);
                Storage::disk('public')->delete($old);
            }
            $data['image'] = '/storage/' . $request->file('image')->store('pelayanan', 'public');
        }

        $this->applyWorkflow($request, $data, $public_service_flow);
        $public_service_flow->update($data);

        $this->logger->log(
            $request->user(),
            'pelayanan.flow.updated',
            "Alur '{$public_service_flow->title}' diperbarui",
            $public_service_flow
        );

        return redirect()->route('admin.public-service-flow.index')->with('success', 'Alur pelayanan diperbarui.');
    }

    public function destroy(PublicServiceFlow $public_service_flow)
    {
        if ($public_service_flow->image) {
            $old = str_replace('/storage/', '', $public_service_flow->image);
            Storage::disk('public')->delete($old);
        }
        $public_service_flow->delete();

        $this->logger->log(
            request()->user(),
            'pelayanan.flow.deleted',
            "Alur '{$public_service_flow->title}' dihapus",
            $public_service_flow
        );

        return redirect()->route('admin.public-service-flow.index')->with('success', 'Alur pelayanan dihapus.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'category' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'steps' => 'nullable|string',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'status' => 'nullable|in:' . implode(',', array_keys(PublicServiceFlow::statuses())),
        ]);

        $data['category'] = $data['category'] ?? 'pelayanan';
        $data['is_active'] = $request->boolean('is_active');
        if (! empty($data['subtitle'])) {
            $data['subtitle'] = HtmlSanitizer::clean($data['subtitle']);
        }
        if (! empty($data['steps'])) {
            $data['steps'] = HtmlSanitizer::clean($data['steps']);
        }

        return $data;
    }

    private function applyWorkflow(Request $request, array &$data, ?PublicServiceFlow $flow = null): void
    {
        $defaultStatus = $request->user()->hasPermission('approve-content') ? 'published' : 'draft';
        $currentStatus = $flow ? $flow->status : $defaultStatus;
        $requestedStatus = $data['status'] ?? $currentStatus;

        if (! $request->user()->hasPermission('approve-content')) {
            $requestedStatus = $requestedStatus === 'published' ? 'pending' : $requestedStatus;
        }

        $data['status'] = $requestedStatus ?: $currentStatus;

        if ($data['status'] === 'published') {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
            $data['published_at'] = $flow?->published_at ?? now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
    }
}
