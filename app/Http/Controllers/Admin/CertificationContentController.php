<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificationContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ActivityLogger;
use App\Support\HtmlSanitizer;

class CertificationContentController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $statusOptions = CertificationContent::statuses();
        $statusFilter = $request->input('status');

        $query = CertificationContent::orderBy('section')->orderBy('urutan');
        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }

        $items = $query->get();
        return view('admin.certification_content.index', [
            'items' => $items,
            'statusOptions' => $statusOptions,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function create()
    {
        return view('admin.certification_content.form', [
            'item' => new CertificationContent(),
            'action' => route('admin.certification-content.store'),
            'method' => 'POST',
            'sections' => $this->sections(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('certification/sections', 'public');
            $data['image_path'] = '/storage/' . $path;
        }
        $this->applyWorkflow($request, $data);
        $content = CertificationContent::create($data);

        $this->logger->log(
            $request->user(),
            'certification.content.created',
            "Konten sertifikasi '{$content->title}' ditambahkan",
            $content
        );
        return redirect()->route('admin.certification-content.index')->with('success', 'Konten sertifikasi ditambahkan.');
    }

    public function edit(CertificationContent $certification_content)
    {
        $certification_content->list_items_text = $certification_content->list_items ? implode("\n", $certification_content->list_items) : '';
        return view('admin.certification_content.form', [
            'item' => $certification_content,
            'action' => route('admin.certification-content.update', $certification_content->id),
            'method' => 'PUT',
            'sections' => $this->sections(),
        ]);
    }

    public function update(Request $request, CertificationContent $certification_content)
    {
        $data = $this->validatedData($request);
        if ($request->hasFile('gambar')) {
            if ($certification_content->image_path && Storage::exists(str_replace('/storage/', 'public/', $certification_content->image_path))) {
                Storage::delete(str_replace('/storage/', 'public/', $certification_content->image_path));
            }
            $path = $request->file('gambar')->store('certification/sections', 'public');
            $data['image_path'] = '/storage/' . $path;
        }
        $this->applyWorkflow($request, $data, $certification_content);
        $certification_content->update($data);

        $this->logger->log(
            $request->user(),
            'certification.content.updated',
            "Konten sertifikasi '{$certification_content->title}' diperbarui",
            $certification_content
        );
        return redirect()->route('admin.certification-content.index')->with('success', 'Konten sertifikasi diperbarui.');
    }

    public function destroy(CertificationContent $certification_content)
    {
        if ($certification_content->image_path) {
            $storedPath = str_replace('/storage/', 'public/', $certification_content->image_path);
            if (Storage::exists($storedPath)) {
                Storage::delete($storedPath);
            }
        }
        $certification_content->delete();

        $this->logger->log(
            request()->user(),
            'certification.content.deleted',
            "Konten sertifikasi '{$certification_content->title}' dihapus",
            $certification_content
        );
        return redirect()->route('admin.certification-content.index')->with('success', 'Konten sertifikasi dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'section' => 'required|string|max:100',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'badge' => 'nullable|string|max:100',
            'button_text' => 'nullable|string|max:150',
            'button_url' => 'nullable|string|max:255',
            'background' => 'nullable|string|max:255',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'list_items' => 'nullable|string|max:2000',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'nullable|in:' . implode(',', array_keys(CertificationContent::statuses())),
        ]);

        $listInput = $request->input('list_items');
        $listItems = [];
        if (!empty($listInput)) {
            $listItems = array_values(array_filter(array_map('trim', preg_split("/(\r?\n)+/", $listInput))));
        }

        unset($data['list_items']);
        $data['title'] = $data['title'] ? strip_tags($data['title']) : null;
        $data['subtitle'] = $data['subtitle'] ? strip_tags($data['subtitle']) : null;
        $data['description'] = $data['description'] ? HtmlSanitizer::clean($data['description']) : null;
        $data['badge'] = $data['badge'] ? strip_tags($data['badge']) : null;
        $data['button_text'] = $data['button_text'] ? strip_tags($data['button_text']) : null;
        $data['background'] = $data['background'] ? strip_tags($data['background']) : null;
        $data['list_items'] = $listItems ?: null;
        $data['is_active'] = $request->boolean('is_active');
        $data['urutan'] = $data['urutan'] ?? 0;
        unset($data['gambar']);

        return $data;
    }

    private function sections(): array
    {
        return [
            'hero' => 'Hero Sertifikasi',
            'intro' => 'Intro LSP',
            'visi' => 'Visi',
            'misi' => 'Misi',
            'tujuan' => 'Tujuan',
            'highlight' => 'Highlight CTA',
        ];
    }

    private function applyWorkflow(Request $request, array &$data, ?CertificationContent $content = null): void
    {
        $defaultStatus = $request->user()->hasPermission('approve-content') ? 'published' : 'draft';
        $currentStatus = $content ? $content->status : $defaultStatus;
        $requestedStatus = $data['status'] ?? $currentStatus;

        if (! $request->user()->hasPermission('approve-content')) {
            $requestedStatus = $requestedStatus === 'published' ? 'pending' : $requestedStatus;
        }

        $data['status'] = $requestedStatus ?: $currentStatus;

        if ($data['status'] === 'published') {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
            $data['published_at'] = $content?->published_at ?? now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
    }
}
