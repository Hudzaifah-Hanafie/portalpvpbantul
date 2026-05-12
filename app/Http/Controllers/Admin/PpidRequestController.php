<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PpidRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class PpidRequestController extends Controller
{
    public function index()
    {
        $requests = PpidRequest::latest()->paginate(20);
        return view('admin.ppid.requests.index', compact('requests'));
    }

    public function show(PpidRequest $ppid_request)
    {
        return view('admin.ppid.requests.show', compact('ppid_request'));
    }

    public function destroy(PpidRequest $ppid_request)
    {
        if ($ppid_request->tanda_tangan) {
            $storedPath = $this->normalizePath($ppid_request->tanda_tangan);
            Storage::disk($storedPath['disk'])->delete($storedPath['path']);
        }
        $ppid_request->delete();
        return redirect()->route('admin.ppid-request.index')->with('success', 'Permohonan dihapus.');
    }

    public function download(PpidRequest $ppid_request)
    {
        if (! $ppid_request->tanda_tangan) {
            abort(404);
        }

        $target = $this->normalizePath($ppid_request->tanda_tangan);

        if (! Storage::disk($target['disk'])->exists($target['path'])) {
            abort(404);
        }

        $mimeName = basename($target['path']);
        return Storage::disk($target['disk'])->download($target['path'], $mimeName);
    }

    private function normalizePath(string $raw): array
    {
        $clean = ltrim($raw, '/');
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, strlen('storage/'));
        }

        // default baru: disimpan di disk lokal (non-public). Lama: di public.
        if (Storage::disk('local')->exists($clean)) {
            return ['disk' => 'local', 'path' => $clean];
        }

        if (Storage::disk('public')->exists($clean)) {
            return ['disk' => 'public', 'path' => $clean];
        }

        return ['disk' => config('filesystems.default', 'local'), 'path' => $clean];
    }
}
