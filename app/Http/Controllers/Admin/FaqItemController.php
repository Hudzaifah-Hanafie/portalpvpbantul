<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqCategory;
use App\Models\FaqItem;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;
use App\Support\HtmlSanitizer;

class FaqItemController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index()
    {
        $statusOptions = FaqItem::statuses();
        $statusFilter = request('status');

        $query = FaqItem::with('category')->orderBy('faq_category_id')->orderBy('urutan');
        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }

        $items = $query->get();
        return view('admin.faq.items.index', compact('items', 'statusOptions', 'statusFilter'));
    }

    public function create()
    {
        $item = new FaqItem(['is_active' => true]);
        $categories = FaqCategory::orderBy('title')->pluck('title', 'id');
        return view('admin.faq.items.form', compact('item', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->applyWorkflow($request, $data);
        $faq = FaqItem::create($data);

        $this->logger->log(
            $request->user(),
            'faq.created',
            "FAQ '{$faq->question}' ditambahkan",
            $faq
        );

        return redirect()->route('admin.faq-item.index')->with('success', 'FAQ berhasil ditambahkan.');
    }

    public function edit(FaqItem $faq_item)
    {
        $categories = FaqCategory::orderBy('title')->pluck('title', 'id');
        return view('admin.faq.items.form', ['item' => $faq_item, 'categories' => $categories]);
    }

    public function update(Request $request, FaqItem $faq_item)
    {
        $data = $this->validateData($request);
        $this->applyWorkflow($request, $data, $faq_item);
        $faq_item->update($data);

        $this->logger->log(
            $request->user(),
            'faq.updated',
            "FAQ '{$faq_item->question}' diperbarui",
            $faq_item
        );

        return redirect()->route('admin.faq-item.index')->with('success', 'FAQ diperbarui.');
    }

    public function destroy(FaqItem $faq_item)
    {
        $this->logger->log(
            request()->user(),
            'faq.deleted',
            "FAQ '{$faq_item->question}' dihapus",
            $faq_item
        );
        $faq_item->delete();

        return redirect()->route('admin.faq-item.index')->with('success', 'FAQ dihapus.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'faq_category_id' => 'required|exists:faq_categories,id',
            'question' => 'required|string|max:255',
            'answer' => 'nullable|string',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'status' => 'nullable|in:' . implode(',', array_keys(FaqItem::statuses())),
        ]);

        $data['urutan'] = $data['urutan'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');
        if (! empty($data['answer'])) {
            $data['answer'] = HtmlSanitizer::clean($data['answer']);
        }

        return $data;
    }

    private function applyWorkflow(Request $request, array &$data, ?FaqItem $faq = null): void
    {
        $defaultStatus = $request->user()->hasPermission('approve-content') ? 'published' : 'draft';
        $currentStatus = $faq ? $faq->status : $defaultStatus;
        $requestedStatus = $data['status'] ?? $currentStatus;

        if (! $request->user()->hasPermission('approve-content')) {
            $requestedStatus = $requestedStatus === 'published' ? 'pending' : $requestedStatus;
        }

        $data['status'] = $requestedStatus ?: $currentStatus;

        if ($data['status'] === 'published') {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
            $data['published_at'] = $faq?->published_at ?? now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
    }
}
