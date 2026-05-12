<?php

namespace App\Http\Controllers;

use App\Models\CourseClass;
use App\Models\CourseMaterial;
use App\Models\CourseMaterialProgress;
use App\Models\CourseMaterialDiscussion;
// App\Models\GamificationPoint dihapus (Sudah dihandle Listener backend)
use Illuminate\Http\Request;
use App\Events\GamificationPointEarned;

class LearningPathController extends Controller
{
    public function showMaterial(Request $request, CourseClass $class, CourseMaterial $material)
    {
        // Pastikan material adalah bagian dari kelas ini
        abort_unless($material->module->course_class_id === $class->id, 404);

        $user = $request->user();

        // 1. Ambil seluruh modul & material untuk sidebar path
        $modules = $class->modules()
            ->with(['materials' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            }])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // 2. Cek/Buat data progres (Tracking waktu belum diimplement mulus, set true saat complete saja)
        $progress = CourseMaterialProgress::firstOrCreate(
            ['user_id' => $user->id, 'course_material_id' => $material->id],
            ['is_completed' => false, 'time_spent_seconds' => 0]
        );

        // 3. Ambil Diskusi untuk material ini
        $discussions = CourseMaterialDiscussion::with(['user', 'replies.user'])
            ->where('course_material_id', $material->id)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->get();

        // 4. Progress list untuk Sidebar Checklist Visual
        $allProgress = CourseMaterialProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('course_material_id');

        return view('participant.learning.material', compact('class', 'material', 'modules', 'progress', 'discussions', 'allProgress'));
    }

    public function markComplete(Request $request, CourseMaterial $material)
    {
        $user = $request->user();
        
        $progress = CourseMaterialProgress::firstOrCreate(
            ['user_id' => $user->id, 'course_material_id' => $material->id]
        );

        if (!$progress->is_completed) {
            $progress->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);

            // [Event Driven] Beri Poin secara Asinkron (Tidak memblokir respon HTTP)
            GamificationPointEarned::dispatch(
                $user->id,
                10,
                'material_completion',
                $material->id,
                'Menyelesaikan materi: ' . $material->title
            );
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Material completed', 'points' => 10]);
        }

        return redirect()->back()->with('success', 'Materi berhasil ditandai selesai. Anda mendapat 10 poin!');
    }

    public function storeDiscussion(Request $request, CourseMaterial $material)
    {
        $request->validate(['message' => 'required|string|max:1000']);
        $user = $request->user();

        $discussion = CourseMaterialDiscussion::create([
            'course_material_id' => $material->id,
            'user_id' => $user->id,
            'message' => $request->message,
            'parent_id' => $request->parent_id,
        ]);

        // [Event Driven] Beri poin forum partisipasi seketika di latar belakang
        GamificationPointEarned::dispatch(
            $user->id,
            5,
            'material_discussion',
            $discussion->id,
            'Berpartisipasi dalam diskusi materi: ' . $material->title
        );

        return redirect()->back()->with('success', 'Diskuisi berhasil dikirim. Anda mendapat 5 poin partisipasi!');
    }
}
