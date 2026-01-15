<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesan;
use Illuminate\Http\Request;

class PesanController extends Controller
{
    public function index()
    {
        $query = Pesan::latest();
        $statusFilter = request('status', 'all');
        $search = request('q');

        if ($search) {
            $searchLower = mb_strtolower($search);
            $query->where(function ($q) use ($searchLower) {
                $q->whereRaw('LOWER(nama) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(subjek) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(pesan) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        if ($statusFilter === 'read') {
            $query->where('is_read', true);
        } elseif ($statusFilter === 'unread') {
            $query->where('is_read', false);
        }

        $pesan = $query->paginate(10)->withQueryString();
        $stats = [
            'total' => Pesan::count(),
            'read' => Pesan::where('is_read', true)->count(),
            'unread' => Pesan::where('is_read', false)->count(),
        ];

        $selectedMessage = null;
        if ($focus = request('focus')) {
            $selectedMessage = Pesan::find($focus);
        }

        return view('admin.pesan.index', compact('pesan', 'stats', 'statusFilter', 'selectedMessage'));
    }

    public function updateStatus(Request $request, Pesan $pesan)
    {
        $pesan->update([
            'is_read' => $request->boolean('is_read', true),
        ]);

        return redirect()->back()->with('status', $pesan->is_read ? 'Pesan ditandai sebagai sudah dibaca.' : 'Pesan ditandai belum dibaca.');
    }

    public function destroy(Pesan $pesan)
    {
        $pesan->delete();
        return redirect()->route('admin.pesan.index')->with('status', 'Pesan berhasil dihapus.');
    }
}