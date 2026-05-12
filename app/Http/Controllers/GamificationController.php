<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GamificationController extends Controller
{
    public function leaderboard(Request $request)
    {
        // Ambil top 20 user berdasarkan total poin
        $leaderboard = User::query()
            ->select('users.id', 'users.name', DB::raw('SUM(gamification_points.points) as total_points'))
            ->join('gamification_points', 'users.id', '=', 'gamification_points.user_id')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_points')
            ->limit(20)
            ->get();

        $userRank = null;
        $userTotalPoints = null;

        if (auth()->check()) {
            $userTotalPointsQuery = DB::table('gamification_points')
                ->where('user_id', auth()->id())
                ->sum('points');
            
            $userTotalPoints = $userTotalPointsQuery;

            // Hitung Rank kasarnya
            $userRank = User::query()
                ->select(DB::raw('SUM(gamification_points.points) as total'))
                ->join('gamification_points', 'users.id', '=', 'gamification_points.user_id')
                ->groupBy('users.id')
                ->having('total', '>', $userTotalPoints)
                ->count() + 1;
        }

        return view('participant.gamification.leaderboard', compact('leaderboard', 'userRank', 'userTotalPoints'));
    }
}
