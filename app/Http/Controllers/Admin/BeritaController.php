<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Berita;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\ActivityLogger;
use App\Support\HtmlSanitizer;

class BeritaController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $statusOptions = Berita::statuses();
        $statusFilter = $request->input('status');

        // Tampilkan yang terbaru berdasarkan tanggal terbit (fallback ke created_at)
        $query = Berita::orderByDesc('published_at')->orderByDesc('created_at');
        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }

        $berita = $query->paginate(10)->withQueryString();
        return view('admin.berita.index', [
            'berita' => $berita,
            'statusOptions' => $statusOptions,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function create()
    {
        return view('admin.berita.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        if ($request->hasFile('gambar_utama')) {
            $data['gambar_utama'] = '/storage/' . $request->file('gambar_utama')->store('berita', 'public');
        }

        $data['slug'] = Str::slug($request->judul) . '-' . time();
        $this->applyWorkflow($request, $data);
        $this->prepareSeo($request, $data);

        $berita = Berita::create($data);

        $this->logger->log(
            $request->user(),
            'berita.created',
            "Berita '{$berita->judul}' ditambahkan",
            $berita,
            ['status' => $berita->status]
        );

        return redirect()->route('admin.berita.index')->with('success', 'Berita berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $berita = Berita::findOrFail($id);
        if ($berita->gambar_utama) {
            $storedPath = str_replace('/storage/', '', $berita->gambar_utama);
            Storage::disk('public')->delete($storedPath);
        }
        $this->logger->log(
            request()->user(),
            'berita.deleted',
            "Berita '{$berita->judul}' dihapus",
            $berita
        );
        $berita->delete();
        return redirect()->route('admin.berita.index')->with('success', 'Berita berhasil dihapus.');
    }

    public function edit($id)
    {
        $berita = Berita::findOrFail($id);
        return view('admin.berita.edit', compact('berita'));
    }

    public function update(Request $request, $id)
    {
        $berita = Berita::findOrFail($id);
        $data = $this->validatePayload($request, false);

        if ($request->hasFile('gambar_utama')) {
            if ($berita->gambar_utama) {
                $storedPath = str_replace('/storage/', '', $berita->gambar_utama);
                Storage::disk('public')->delete($storedPath);
            }
            $path = $request->file('gambar_utama')->store('berita', 'public');
            $data['gambar_utama'] = '/storage/' . $path;
        }

        $data['slug'] = Str::slug($request->judul) . '-' . $berita->id;
        $this->applyWorkflow($request, $data, $berita);
        $this->prepareSeo($request, $data);

        $berita->update($data);

        $this->logger->log(
            $request->user(),
            'berita.updated',
            "Berita '{$berita->judul}' diperbarui",
            $berita,
            ['status' => $berita->status]
        );

        return redirect()->route('admin.berita.index')->with('success', 'Berita berhasil diperbarui!');
    }

    public function submit(Request $request, Berita $berita)
    {
        $berita->update([
            'status' => Berita::STATUS_PENDING,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $this->logger->log(
            $request->user(),
            'berita.submitted',
            "Berita '{$berita->judul}' diajukan",
            $berita,
            ['status' => $berita->status]
        );

        return back()->with('success', 'Berita diajukan untuk persetujuan.');
    }

    public function approve(Request $request, Berita $berita)
    {
        abort_unless($request->user()->hasPermission('approve-content'), 403);

        $berita->update([
            'status' => Berita::STATUS_PUBLISHED,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'published_at' => $berita->published_at ?? now(),
        ]);

        $this->logger->log(
            $request->user(),
            'berita.approved',
            "Berita '{$berita->judul}' disetujui",
            $berita,
            ['status' => $berita->status]
        );

        return back()->with('success', 'Berita telah disetujui dan dipublikasikan.');
    }

    private function validatePayload(Request $request, bool $isCreate = true): array
    {
        $imageRule = $isCreate ? 'required|image|mimes:jpg,jpeg,png|max:2048' : 'nullable|image|mimes:jpg,jpeg,png|max:2048';

        $rules = [
            'judul' => 'required|string|max:150',
            'kategori' => 'required|string|in:' . implode(',', array_keys(Berita::categories())),
            'author' => 'nullable|string|max:100',
            'konten' => 'required|string',
            'excerpt' => 'nullable|string',
            'published_at' => 'nullable|date',
            'gambar_utama' => $imageRule,
            'status' => 'nullable|in:' . implode(',', array_keys(Berita::statuses())),
            'meta_title' => 'nullable|string|max:160',
            'meta_description' => 'nullable|string|max:320',
            'focus_keyword' => 'nullable|string|max:100',
        ];

        $messages = [
            'judul.max' => 'Judul maksimal 150 karakter.',
            'author.max' => 'Nama penulis maksimal 100 karakter.',
            'gambar_utama.mimes' => 'Gambar utama wajib berformat JPG atau PNG.',
            'gambar_utama.max' => 'Ukuran gambar maksimal 2MB.',
        ];

        $data = $request->validate($rules, $messages);
        $data['konten'] = HtmlSanitizer::clean($data['konten']);
        $data['excerpt'] = $data['excerpt'] ? strip_tags($data['excerpt']) : null;

        return $data;
    }

    private function applyWorkflow(Request $request, array &$data, ?Berita $berita = null): void
    {
        $currentStatus = $berita ? $berita->status : Berita::STATUS_DRAFT;
        $requestedStatus = $data['status'] ?? $currentStatus;
        if ($requestedStatus === Berita::STATUS_PUBLISHED && !$request->user()->hasPermission('approve-content')) {
            $requestedStatus = Berita::STATUS_PENDING;
        }

        $data['status'] = $requestedStatus;
        if ($requestedStatus === Berita::STATUS_PUBLISHED) {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
            $data['published_at'] = $data['published_at'] ?? now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
    }

    private function prepareSeo(Request $request, array &$data): void
    {
        $data['meta_title'] = $request->input('meta_title') ?: Str::limit($request->input('judul'), 60);
        $data['meta_description'] = $request->input('meta_description') ?: Str::limit(strip_tags($request->input('excerpt') ?: $request->input('konten')), 155);
        $data['focus_keyword'] = $request->input('focus_keyword');
    }
}
