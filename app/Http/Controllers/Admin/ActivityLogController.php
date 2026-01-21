<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ActivityLog::class);

        $query = ActivityLog::with(['user', 'subject'])->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->string('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->string('action'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->string('date_from')));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->string('date_to')));
        }

        $logs = $query->paginate(20)->withQueryString();

        $filters = $request->only(['user_id', 'action', 'date_from', 'date_to']);
        $users = User::orderBy('name')->get();
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.activity_logs.index', compact('logs', 'users', 'actions', 'filters'));
    }

    public function destroy(ActivityLog $activity_log)
    {
        $this->authorize('delete', $activity_log);
        $activity_log->delete();

        return redirect()->route('admin.activity-logs.index')
            ->with('success', 'Log berhasil dihapus.');
    }

    public function clear(Request $request)
    {
        $this->authorize('deleteAny', ActivityLog::class);

        $request->validate([
            'confirmation' => 'required|in:hapus data log',
        ], [
            'confirmation.required' => 'Silakan ketik kalimat konfirmasi.',
            'confirmation.in' => 'Kalimat konfirmasi salah. Ketik "hapus data log" (huruf kecil semua).',
        ]);

        ActivityLog::query()->delete();

        return redirect()->route('admin.activity-logs.index')
            ->with('success', 'Seluruh log sudah dibersihkan.');
    }
}
