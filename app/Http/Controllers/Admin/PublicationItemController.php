<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicationItem;
use App\Models\PublicationCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ActivityLogger;
use App\Support\HtmlSanitizer;

class PublicationItemController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $statusOptions = PublicationItem::statuses();
        $statusFilter = $request->input('status');

        $query = PublicationItem::with('category')->orderBy('publication_category_id')->orderBy('urutan');
        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }

        $items = $query->get();
        return view('admin.publication.item.index', [
            'items' => $items,
            'statusOptions' => $statusOptions,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function create()
    {
        return view('admin.publication.item.form', [
            'item' => new PublicationItem(),
            'categories' => PublicationCategory::orderBy('urutan')->pluck('name', 'id'),
            'action' => route('admin.publication-item.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('image')) {
            $data['image'] = '/storage/' . $request->file('image')->store('publications', 'public');
        }
        $this->applyWorkflow($request, $data);
        $item = PublicationItem::create($data);

        $this->logger->log(
            $request->user(),
            'publication_item.created',
            "Item publikasi '{$item->title}' ditambahkan",
            $item
        );
        return redirect()->route('admin.publication-item.index')->with('success', 'Item publikasi ditambahkan.');
    }

    public function edit(PublicationItem $publication_item)
    {
        return view('admin.publication.item.form', [
            'item' => $publication_item,
            'categories' => PublicationCategory::orderBy('urutan')->pluck('name', 'id'),
            'action' => route('admin.publication-item.update', $publication_item->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, PublicationItem $publication_item)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('image')) {
            if ($publication_item->image) {
                $oldPath = str_replace('/storage/', '', $publication_item->image);
                Storage::disk('public')->delete($oldPath);
            }
            $data['image'] = '/storage/' . $request->file('image')->store('publications', 'public');
        }
        $this->applyWorkflow($request, $data, $publication_item);
        $publication_item->update($data);

        $this->logger->log(
            $request->user(),
            'publication_item.updated',
            "Item publikasi '{$publication_item->title}' diperbarui",
            $publication_item
        );
        return redirect()->route('admin.publication-item.index')->with('success', 'Item publikasi diperbarui.');
    }

    public function destroy(PublicationItem $publication_item)
    {
        if ($publication_item->image) {
            $oldPath = str_replace('/storage/', '', $publication_item->image);
            Storage::disk('public')->delete($oldPath);
        }
        $this->logger->log(
            request()->user(),
            'publication_item.deleted',
            "Item publikasi '{$publication_item->title}' dihapus",
            $publication_item
        );
        $publication_item->delete();
        return redirect()->route('admin.publication-item.index')->with('success', 'Item publikasi dihapus.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'publication_category_id' => 'required|exists:publication_categories,id',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'badge' => 'nullable|string|max:50',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|url|max:255',
            'extra' => 'nullable|string|max:2000',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'nullable|in:' . implode(',', array_keys(PublicationItem::statuses())),
        ]);
        $data['title'] = strip_tags($data['title']);
        $data['subtitle'] = $data['subtitle'] ? strip_tags($data['subtitle']) : null;
        $data['description'] = $data['description'] ? HtmlSanitizer::clean($data['description']) : null;
        $data['badge'] = $data['badge'] ? strip_tags($data['badge']) : null;
        $data['button_text'] = $data['button_text'] ? strip_tags($data['button_text']) : null;
        $data['extra'] = $data['extra'] ? array_values(array_filter(array_map('trim', preg_split("/(\r?\n)+/", $data['extra'])))) : null;
        $data['urutan'] = $data['urutan'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }

    private function applyWorkflow(Request $request, array &$data, ?PublicationItem $item = null): void
    {
        $defaultStatus = $request->user()->hasPermission('approve-content') ? 'published' : 'draft';
        $currentStatus = $item ? $item->status : $defaultStatus;
        $requestedStatus = $data['status'] ?? $currentStatus;

        if (! $request->user()->hasPermission('approve-content')) {
            $requestedStatus = $requestedStatus === 'published' ? 'pending' : $requestedStatus;
        }

        $data['status'] = $requestedStatus ?: $currentStatus;

        if ($data['status'] === 'published') {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
            $data['published_at'] = $item?->published_at ?? now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
    }
}
