<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;
use App\Support\HtmlSanitizer;

class TestimonialController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index()
    {
        $statusOptions = Testimonial::statuses();
        $statusFilter = request('status');

        $query = Testimonial::orderBy('urutan');
        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }

        $testimonials = $query->get();
        return view('admin.testimonial.index', compact('testimonials', 'statusOptions', 'statusFilter'));
    }

    public function create()
    {
        return view('admin.testimonial.form', [
            'testimonial' => new Testimonial(),
            'action' => route('admin.testimonial.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->applyWorkflow($request, $data);
        $testimonial = Testimonial::create($data);

        $this->logger->log(
            $request->user(),
            'testimonial.created',
            "Testimoni '{$testimonial->nama}' ditambahkan",
            $testimonial
        );
        return redirect()->route('admin.testimonial.index')->with('success', 'Testimoni berhasil ditambahkan.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonial.form', [
            'testimonial' => $testimonial,
            'action' => route('admin.testimonial.update', $testimonial->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $data = $this->validateData($request);
        $this->applyWorkflow($request, $data, $testimonial);
        $testimonial->update($data);

        $this->logger->log(
            $request->user(),
            'testimonial.updated',
            "Testimoni '{$testimonial->nama}' diperbarui",
            $testimonial
        );
        return redirect()->route('admin.testimonial.index')->with('success', 'Testimoni diperbarui.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $this->logger->log(
            request()->user(),
            'testimonial.deleted',
            "Testimoni '{$testimonial->nama}' dihapus",
            $testimonial
        );
        $testimonial->delete();
        return redirect()->route('admin.testimonial.index')->with('success', 'Testimoni dihapus.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'pesan' => 'nullable|string|max:2000',
            'video_url' => ['nullable', 'url', 'regex:/^(https?:\\/\\/)?(www\\.)?(youtube\\.com|youtu\\.be)\\/.+$/i'],
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'status' => 'nullable|in:' . implode(',', array_keys(Testimonial::statuses())),
        ], [
            'video_url.regex' => 'Video URL harus berasal dari YouTube.',
        ]);

        $data['urutan'] = $data['urutan'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');
        if (! empty($data['pesan'])) {
            $data['pesan'] = HtmlSanitizer::clean($data['pesan']);
        }

        return $data;
    }

    private function applyWorkflow(Request $request, array &$data, ?Testimonial $testimonial = null): void
    {
        $defaultStatus = $request->user()->hasPermission('approve-content') ? 'published' : 'draft';
        $currentStatus = $testimonial ? $testimonial->status : $defaultStatus;
        $requestedStatus = $data['status'] ?? $currentStatus;

        if (! $request->user()->hasPermission('approve-content') && $requestedStatus === 'published') {
            $requestedStatus = 'pending';
        }

        $data['status'] = $requestedStatus ?: $currentStatus;

        if ($data['status'] === 'published') {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
            $data['published_at'] = $testimonial?->published_at ?? now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
    }
}
