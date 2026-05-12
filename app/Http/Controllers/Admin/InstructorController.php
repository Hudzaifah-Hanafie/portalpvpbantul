<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ActivityLogger;
use App\Support\HtmlSanitizer;

class InstructorController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $statusOptions = Instructor::statuses();
        $statusFilter = $request->input('status');

        $query = Instructor::orderBy('urutan');
        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }

        $instructors = $query->get();
        return view('admin.instructor.index', [
            'instructors' => $instructors,
            'statusOptions' => $statusOptions,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function create()
    {
        return view('admin.instructor.form', [
            'instructor' => new Instructor(),
            'action' => route('admin.instructor.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('instructors', 'public');
            $data['foto'] = '/storage/' . $path;
        }
        $this->applyWorkflow($request, $data);
        $instructor = Instructor::create($data);

        $this->logger->log(
            $request->user(),
            'instructor.created',
            "Instruktur '{$instructor->nama}' ditambahkan",
            $instructor
        );
        return redirect()->route('admin.instructor.index')->with('success', 'Instruktur berhasil ditambahkan.');
    }

    public function edit(Instructor $instructor)
    {
        return view('admin.instructor.form', [
            'instructor' => $instructor,
            'action' => route('admin.instructor.update', $instructor->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Instructor $instructor)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('foto')) {
            if ($instructor->foto && Storage::exists(str_replace('/storage/', 'public/', $instructor->foto))) {
                Storage::delete(str_replace('/storage/', 'public/', $instructor->foto));
            }
            $path = $request->file('foto')->store('instructors', 'public');
            $data['foto'] = '/storage/' . $path;
        }
        $this->applyWorkflow($request, $data, $instructor);
        $instructor->update($data);

        $this->logger->log(
            $request->user(),
            'instructor.updated',
            "Instruktur '{$instructor->nama}' diperbarui",
            $instructor
        );
        return redirect()->route('admin.instructor.index')->with('success', 'Instruktur berhasil diperbarui.');
    }

    public function destroy(Instructor $instructor)
    {
        if ($instructor->foto && Storage::exists(str_replace('/storage/', 'public/', $instructor->foto))) {
            Storage::delete(str_replace('/storage/', 'public/', $instructor->foto));
        }
        $instructor->delete();

        $this->logger->log(
            request()->user(),
            'instructor.deleted',
            "Instruktur '{$instructor->nama}' dihapus",
            $instructor
        );
        return redirect()->route('admin.instructor.index')->with('success', 'Instruktur berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'keahlian' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'linkedin' => 'nullable|url',
            'whatsapp' => 'nullable|string|max:30',
            'email' => 'nullable|email',
            'urutan' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'foto' => 'nullable|image|max:2048',
            'status' => 'nullable|in:' . implode(',', array_keys(Instructor::statuses())),
        ]);

        if (! empty($data['deskripsi'])) {
            $data['deskripsi'] = HtmlSanitizer::clean($data['deskripsi']);
        }

        return $data;
    }

    private function applyWorkflow(Request $request, array &$data, ?Instructor $instructor = null): void
    {
        $defaultStatus = $request->user()->hasPermission('approve-content') ? 'published' : 'draft';
        $currentStatus = $instructor ? $instructor->status : $defaultStatus;
        $requestedStatus = $data['status'] ?? $currentStatus;

        if (! $request->user()->hasPermission('approve-content') && $requestedStatus === 'published') {
            $requestedStatus = 'pending';
        }

        $data['status'] = $requestedStatus ?: $currentStatus;

        if ($data['status'] === 'published') {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
            $data['published_at'] = $instructor?->published_at ?? now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
    }
}
