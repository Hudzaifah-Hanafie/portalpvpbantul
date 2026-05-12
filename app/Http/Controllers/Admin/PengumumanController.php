<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\ActivityLogger;
use App\Support\HtmlSanitizer;

class PengumumanController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $statusOptions = Pengumuman::statuses();
        $statusFilter = $request->input('status');

        $query = Pengumuman::latest();
        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }

        $pengumuman = $query->paginate(10)->withQueryString();
        return view('admin.pengumuman.index', [
            'pengumuman' => $pengumuman,
            'statusOptions' => $statusOptions,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function create()
    {
        return view('admin.pengumuman.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        if ($request->hasFile('file_download')) {
            $path = $request->file('file_download')->store('files', 'public');
            $data['file_download'] = '/storage/' . $path;
        }

        $data['slug'] = Str::slug($request->judul) . '-' . time();
        $this->applyWorkflow($request, $data);
        $this->prepareSeo($request, $data);

        $pengumuman = Pengumuman::create($data);

        $this->logger->log(
            $request->user(),
            'pengumuman.created',
            "Pengumuman '{$pengumuman->judul}' ditambahkan",
            $pengumuman,
            ['status' => $pengumuman->status]
        );

        return redirect()->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        return view('admin.pengumuman.edit', compact('pengumuman'));
    }

    public function update(Request $request, $id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        $data = $this->validatePayload($request, false);

        $data['slug'] = Str::slug($request->judul) . '-' . $pengumuman->id;

        if ($request->hasFile('file_download')) {
            if ($pengumuman->file_download) {
                $oldPath = str_replace('/storage/', '', $pengumuman->file_download);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $path = $request->file('file_download')->store('files', 'public');
            $data['file_download'] = '/storage/' . $path;
        }

        $this->applyWorkflow($request, $data, $pengumuman);
        $this->prepareSeo($request, $data);

        $pengumuman->update($data);

        $this->logger->log(
            $request->user(),
            'pengumuman.updated',
            "Pengumuman '{$pengumuman->judul}' diperbarui",
            $pengumuman,
            ['status' => $pengumuman->status]
        );

        return redirect()->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        if ($pengumuman->file_download) {
            $path = str_replace('/storage/', '', $pengumuman->file_download);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $this->logger->log(
            request()->user(),
            'pengumuman.deleted',
            "Pengumuman '{$pengumuman->judul}' dihapus",
            $pengumuman
        );

        $pengumuman->delete();

        return redirect()->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }

    public function submit(Request $request, Pengumuman $pengumuman)
    {
        $pengumuman->update([
            'status' => Pengumuman::STATUS_PENDING,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $this->logger->log(
            $request->user(),
            'pengumuman.submitted',
            "Pengumuman '{$pengumuman->judul}' diajukan",
            $pengumuman,
            ['status' => $pengumuman->status]
        );

        return back()->with('success', 'Pengumuman diajukan untuk persetujuan.');
    }

    public function approve(Request $request, Pengumuman $pengumuman)
    {
        abort_unless($request->user()->hasPermission('approve-content'), 403);

        $pengumuman->update([
            'status' => Pengumuman::STATUS_PUBLISHED,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        $this->logger->log(
            $request->user(),
            'pengumuman.approved',
            "Pengumuman '{$pengumuman->judul}' disetujui",
            $pengumuman,
            ['status' => $pengumuman->status]
        );

        return back()->with('success', 'Pengumuman telah disetujui dan dipublikasikan.');
    }

    private function validatePayload(Request $request, bool $isCreate = true): array
    {
        $data = $request->validate([
            'judul' => 'required|string|max:160',
            'isi' => 'required|string',
            'file_download' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
            'status' => 'nullable|in:' . implode(',', array_keys(Pengumuman::statuses())),
            'meta_title' => 'nullable|string|max:160',
            'meta_description' => 'nullable|string|max:320',
            'focus_keyword' => 'nullable|string|max:100',
        ]);

        $data['isi'] = HtmlSanitizer::clean($data['isi']);

        return $data;
    }

    private function applyWorkflow(Request $request, array &$data, ?Pengumuman $pengumuman = null): void
    {
        $currentStatus = $pengumuman ? $pengumuman->status : Pengumuman::STATUS_DRAFT;
        $requestedStatus = $data['status'] ?? $currentStatus;
        if ($requestedStatus === Pengumuman::STATUS_PUBLISHED && !$request->user()->hasPermission('approve-content')) {
            $requestedStatus = Pengumuman::STATUS_PENDING;
        }

        $data['status'] = $requestedStatus;
        if ($requestedStatus === Pengumuman::STATUS_PUBLISHED) {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
    }

    private function prepareSeo(Request $request, array &$data): void
    {
        $data['meta_title'] = $request->input('meta_title') ?: Str::limit($request->input('judul'), 60);
        $data['meta_description'] = $request->input('meta_description') ?: Str::limit(strip_tags($request->input('isi')), 155);
        $data['focus_keyword'] = $request->input('focus_keyword');
    }
}
