<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        if (is_array($request->query('q'))) {
            abort(400, 'Parameter pencarian tidak valid.');
        }
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
        ]);
        $search = trim((string) ($validated['q'] ?? ''));
        $alumni = Alumni::query()
            ->when($search, fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%")
                    ->orWhere('field_of_study', 'ilike', "%{$search}%");
            }))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Alumni::count(),
            'active' => Alumni::where('is_active', true)->count(),
            'inactive' => Alumni::where('is_active', false)->count(),
        ];

        return view('admin.alumni.index', compact('alumni', 'search', 'stats'));
    }

    public function create()
    {
        return view('admin.alumni.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:alumni,email',
            'phone' => 'nullable|string|max:32',
            'field_of_study' => 'nullable|string|max:255',
            'graduation_year' => 'nullable|digits:4',
            'employment_status' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        Alumni::create($data);

        return redirect()->route('admin.alumni.index')->with('success', 'Data alumni berhasil disimpan.');
    }

    public function edit(Alumni $alumni)
    {
        return view('admin.alumni.edit', compact('alumni'));
    }

    public function update(Request $request, Alumni $alumni)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:alumni,email,{$alumni->id}",
            'phone' => 'nullable|string|max:32',
            'field_of_study' => 'nullable|string|max:255',
            'graduation_year' => 'nullable|digits:4',
            'employment_status' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $alumni->update($data);

        return redirect()->route('admin.alumni.index')->with('success', 'Data alumni berhasil diperbarui.');
    }

    public function destroy(Alumni $alumni)
    {
        $alumni->delete();
        return redirect()->route('admin.alumni.index')->with('success', 'Data alumni berhasil dihapus.');
    }
}
