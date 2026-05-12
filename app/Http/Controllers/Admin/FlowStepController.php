<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlowStep;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Support\HtmlSanitizer;

class FlowStepController extends Controller
{
    public function index()
    {
        $flows = FlowStep::orderBy('urutan')->get();
        return view('admin.flow.index', compact('flows'));
    }

    public function create()
    {
        return view('admin.flow.form', [
            'flow' => new FlowStep(),
            'action' => route('admin.flow.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active');
        $data['urutan'] = $data['urutan'] ?? ((FlowStep::max('urutan') ?? 0) + 1);
        $data['judul'] = $this->resolveTitle($data);
        FlowStep::create($data);
        return redirect()->route('admin.flow.index')->with('success', 'Langkah alur ditambahkan.');
    }

    public function edit(FlowStep $flow)
    {
        return view('admin.flow.form', [
            'flow' => $flow,
            'action' => route('admin.flow.update', $flow->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, FlowStep $flow)
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active');
        $data['urutan'] = $data['urutan'] ?? $flow->urutan ?? 0;
        $data['judul'] = $this->resolveTitle($data, $flow->judul);
        $flow->update($data);
        return redirect()->route('admin.flow.index')->with('success', 'Langkah alur diperbarui.');
    }

    public function destroy(FlowStep $flow)
    {
        $flow->delete();
        return redirect()->route('admin.flow.index')->with('success', 'Langkah alur dihapus.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'judul' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if (! empty($data['deskripsi'])) {
            $data['deskripsi'] = HtmlSanitizer::clean($data['deskripsi']);
        }

        return $data;
    }

    private function resolveTitle(array $data, ?string $fallback = null): string
    {
        $title = $data['judul'] ?? null;
        if ($title) {
            return $title;
        }

        $desc = trim(strip_tags($data['deskripsi'] ?? ''));
        if ($desc !== '') {
            return Str::limit($desc, 80, '');
        }

        $order = $data['urutan'] ?? 1;
        return 'Langkah ' . $order;
    }
}
