<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrgStructure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrgStructureController extends Controller
{
    public function index()
    {
        $roots = OrgStructure::with('children.children')->whereNull('parent_id')->orderBy('urutan')->get();
        return view('admin.struktur.index', compact('roots'));
    }

    public function create()
    {
        $nodes = OrgStructure::orderBy('nama')->get();
        return view('admin.struktur.form', [
            'struktur' => new OrgStructure(),
            'nodes' => $nodes,
            'action' => route('admin.struktur.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        OrgStructure::create($data);
        return redirect()->route('admin.struktur.index')->with('success', 'Struktur berhasil ditambahkan.');
    }

    public function edit(OrgStructure $struktur)
    {
        $nodes = OrgStructure::where('id', '!=', $struktur->id)->orderBy('nama')->get();
        return view('admin.struktur.form', [
            'struktur' => $struktur,
            'nodes' => $nodes,
            'action' => route('admin.struktur.update', $struktur->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, OrgStructure $struktur)
    {
        $data = $this->validateData($request, $struktur->id);
        $struktur->update($data);
        return redirect()->route('admin.struktur.index')->with('success', 'Struktur berhasil diperbarui.');
    }

    public function destroy(OrgStructure $struktur)
    {
        $struktur->delete();
        return redirect()->route('admin.struktur.index')->with('success', 'Struktur beserta anak-anaknya telah dihapus.');
    }

    private function validateData(Request $request, ?string $id = null): array
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'parent_id' => [
                'nullable',
                'exists:org_structures,id',
            ],
            'urutan' => 'nullable|integer',
        ];

        if ($id) {
            $rules['parent_id'][] = function ($attribute, $value, $fail) use ($id) {
                $currentId = $value;
                $visited = [];
                while ($currentId) {
                    if ($currentId === $id) {
                        $fail('Terjadi referensi melingkar (Circular Reference). Node yang dipilih adalah keturunan dari struktur ini.');
                        return;
                    }
                    if (in_array($currentId, $visited)) {
                        break;
                    }
                    $visited[] = $currentId;
                    
                    $parent = \App\Models\OrgStructure::find($currentId);
                    $currentId = $parent ? $parent->parent_id : null;
                }
            };
        }

        return $request->validate($rules);
    }
}
