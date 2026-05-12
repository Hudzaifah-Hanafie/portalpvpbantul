<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RestrictsToInstructorClasses;
use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;

class CourseGradebookController extends Controller
{
    use RestrictsToInstructorClasses;

    public function index(Request $request)
    {
        $classFilter = $request->input('class_id');
        $classes = $this->scopedClassOptions($request->user());

        $records = collect();
        $selectedClass = null;
        $summary = null;

        if ($classFilter) {
            $this->ensureInstructorOwnsClassId($request->user(), $classFilter);
            $selectedClass = CourseClass::find($classFilter);

            $records = CourseEnrollment::with('user')
                ->where('course_class_id', $classFilter)
                ->whereIn('status', ['active', 'approved', 'completed'])
                ->get()
                ->map(function ($enroll) {
                    return [
                        'id' => $enroll->id,
                        'user' => $enroll->user,
                        'pre_test_score' => $enroll->pre_test_score,
                        'post_test_score' => $enroll->post_test_score ?? $enroll->written_score,
                        'practice_score' => $enroll->practice_score,
                        'attitude_score' => $enroll->attitude_score,
                        'attendance_rate' => $enroll->attendance_rate,
                        'final_grade' => $enroll->final_grade,
                        'competency_status' => $enroll->competency_status,
                    ];
                });

            $summary = [
                'participants' => $records->count(),
                'pre_avg' => $records->pluck('pre_test_score')->filter(fn($v) => !is_null($v))->avg(),
                'post_avg' => $records->pluck('post_test_score')->filter(fn($v) => !is_null($v))->avg(),
                'practice_avg' => $records->pluck('practice_score')->filter(fn($v) => !is_null($v))->avg(),
                'attitude_avg' => $records->pluck('attitude_score')->filter(fn($v) => !is_null($v))->avg(),
                'attendance_avg' => $records->pluck('attendance_rate')->filter(fn($v) => !is_null($v))->avg(),
                'final_avg' => $records->pluck('final_grade')->filter(fn($v) => !is_null($v))->avg(),
                'competent' => $records->where('competency_status', 'competent')->count(),
                'not_competent' => $records->where('competency_status', 'not_competent')->count(),
            ];
        }

        return view('admin.gradebook.index', compact('classes', 'classFilter', 'selectedClass', 'records', 'summary'));
    }

    public function exportExcel(Request $request)
    {
        $classFilter = $request->input('class_id');
        if (!$classFilter) {
            return back()->with('error', 'Silakan pilih kelas terlebih dahulu.');
        }

        $this->ensureInstructorOwnsClassId($request->user(), $classFilter);
        $courseClass = CourseClass::findOrFail($classFilter);

        $className = \Illuminate\Support\Str::slug($courseClass->title);
        $fileName = "Rekap_Nilai_{$className}_" . date('Ymd_His') . ".xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\CourseGradebookExport($classFilter), $fileName);
    }

    public function updateInline(Request $request, string $id)
    {
        $data = $request->validate([
            'field' => 'required|in:practice_score,attitude_score',
            'value' => 'nullable|numeric|min:0|max:100',
        ]);

        $enrollment = CourseEnrollment::findOrFail($id);
        
        $this->ensureInstructorOwnsClassId($request->user(), $enrollment->course_class_id);

        $enrollment->update([
            $data['field'] => $data['value']
        ]);

        // Auto-recalculate final grade and competency status
        $enrollment->updateLearningOutcome();

        return response()->json([
            'success' => true,
            'message' => 'Nilai berhasil disimpan.',
            'new_final_grade' => $enrollment->final_grade,
            'new_status' => $enrollment->competency_status,
        ]);
    }
}
