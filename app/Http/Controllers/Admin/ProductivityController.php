<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Productivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Support\HtmlSanitizer;

class ProductivityController extends Controller
{
    public function index()
    {
        $items = Productivity::orderBy('urutan')->get();
        return view('admin.productivity.index', compact('items'));
    }

    public function create()
    {
        return view('admin.productivity.form', [
            'item' => new Productivity(),
            'action' => route('admin.productivity.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('productivities', 'public');
            $data['gambar'] = '/storage/' . $path;
        }
        Productivity::create($data);
        return redirect()->route('admin.productivity.index')->with('success', 'Konten produktivitas ditambahkan.');
    }

    public function edit(Productivity $productivity)
    {
        return view('admin.productivity.form', [
            'item' => $productivity,
            'action' => route('admin.productivity.update', $productivity->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Productivity $productivity)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('gambar')) {
            if ($productivity->gambar && Storage::exists(str_replace('/storage/', 'public/', $productivity->gambar))) {
                // Storage::delete(str_replace('/storage/', 'public/', $productivity->gambar));
            }
            $path = $request->file('gambar')->store('productivities', 'public');
            $data['gambar'] = '/storage/' . $path;
        }
        $productivity->update($data);
        return redirect()->route('admin.productivity.index')->with('success', 'Konten produktivitas diperbarui.');
    }

    public function destroy(Productivity $productivity)
    {
        $productivity->delete();
        return redirect()->route('admin.productivity.index')->with('success', 'Konten produktivitas dihapus.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'gambar' => 'nullable|image|max:2048',
        ]);

        if (! empty($data['deskripsi'])) {
            $data['deskripsi'] = HtmlSanitizer::clean($data['deskripsi']);
        }

        return $data;
    }
}
