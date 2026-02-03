<?php

namespace App\Http\Controllers;

use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\TrainingSchedule;
use App\Support\EnrollmentPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TrainingRegistrationController extends Controller
{
    public function register(Request $request, TrainingSchedule $schedule): RedirectResponse
    {
        if (! $schedule->is_active) {
            return back()->with('error', 'Jadwal pelatihan sedang tidak aktif.');
        }

        $user = $request->user();
        $this->ensureCourseClass($schedule);

        $enrollment = CourseEnrollment::firstOrNew([
            'course_class_id' => $schedule->id,
            'user_id' => $user->id,
        ]);

        if ($enrollment->exists) {
            return redirect()->route('participant.applications')
                ->with('success', 'Anda sudah terdaftar pada jadwal ini.');
        }

        $adminStatus = EnrollmentPolicy::selectionMode() === EnrollmentPolicy::SELECTION_AUTO ? 'verified' : 'pending';
        $enrollment->fill([
            'status' => 'pending',
            'admin_status' => $adminStatus,
            'created_by' => $user->id,
        ]);
        $enrollment->save();

        return redirect()->route('participant.applications')
            ->with('success', 'Pendaftaran berhasil. Silakan pantau jadwal CBT dan wawancara.');
    }

    private function ensureCourseClass(TrainingSchedule $schedule): void
    {
        $class = CourseClass::firstOrNew(['id' => $schedule->id]);
        $class->fill([
            'title' => $schedule->judul,
            'description' => $schedule->catatan,
            'format' => 'sinkron',
            'is_active' => true,
            'status' => 'published',
            'published_at' => now(),
        ]);
        $class->id ??= $schedule->id;
        $class->save();
    }
}
