<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RestrictsToInstructorClasses;
use App\Models\CourseAssignment;
use App\Models\CourseClass;
use App\Models\CourseSubmission;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseAssignmentController extends Controller
{
    use RestrictsToInstructorClasses;

    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index()
    {
        $statusOptions = CourseAssignment::statuses();
        $assessmentOptions = $this->assessmentOptions();
        $statusFilter = request('status');
        $classFilter = request('class_id');

        $query = CourseAssignment::with(['course', 'module'])->orderBy('due_at');
        if ($this->isInstructorUser(request()->user())) {
            $classIds = CourseClass::where('instructor_id', request()->user()->id)->pluck('id');
            $query->whereIn('course_class_id', $classIds);
        }
        if ($statusFilter && array_key_exists($statusFilter, $statusOptions)) {
            $query->where('status', $statusFilter);
        }
        if ($classFilter) {
            $query->where('course_class_id', $classFilter);
        }

        $assignments = $query->paginate(20)->withQueryString();
        $classes = $this->scopedClassOptions(request()->user());

        return view('admin.course_assignment.index', compact('assignments', 'statusOptions', 'statusFilter', 'classes', 'classFilter', 'assessmentOptions'));
    }

    public function create()
    {
        $classes = $this->scopedClassOptions(request()->user());
        $modules = $this->scopedModuleOptions(request()->user());
        $assessmentOptions = $this->assessmentOptions();

        return view('admin.course_assignment.form', [
            'assignment' => new CourseAssignment([
                'type' => 'essay',
                'quiz_scope' => 'class',
                'assessment_type' => 'regular',
                'is_active' => true,
            ]),
            'classes' => $classes,
            'modules' => $modules,
            'assessmentOptions' => $assessmentOptions,
            'action' => route($this->getRoutePrefix() . 'course-assignment.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->ensureInstructorOwnsClassId($request->user(), $data['course_class_id']);
        $data['created_by'] = $request->user()->id;
        $this->applyWorkflow($request, $data);
        $assignment = CourseAssignment::create($data);

        $this->logger->log(
            $request->user(),
            'course.assignment.created',
            "Tugas '{$assignment->title}' ditambahkan",
            $assignment
        );

        return redirect()->route($this->getRoutePrefix() . 'course-assignment.index')->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function edit(CourseAssignment $course_assignment)
    {
        $this->ensureInstructorOwnsClassId(request()->user(), $course_assignment->course_class_id);
        $classes = $this->scopedClassOptions(request()->user());
        $modules = $this->scopedModuleOptions(request()->user());
        $assessmentOptions = $this->assessmentOptions();
        return view('admin.course_assignment.form', [
            'assignment' => $course_assignment,
            'classes' => $classes,
            'modules' => $modules,
            'assessmentOptions' => $assessmentOptions,
            'action' => route($this->getRoutePrefix() . 'course-assignment.update', $course_assignment->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, CourseAssignment $course_assignment)
    {
        $data = $this->validateData($request);
        $this->ensureInstructorOwnsClassId($request->user(), $course_assignment->course_class_id);
        $this->ensureInstructorOwnsClassId($request->user(), $data['course_class_id']);
        $this->applyWorkflow($request, $data, $course_assignment);
        $course_assignment->update($data);

        $this->logger->log(
            $request->user(),
            'course.assignment.updated',
            "Tugas '{$course_assignment->title}' diperbarui",
            $course_assignment
        );

        return redirect()->route($this->getRoutePrefix() . 'course-assignment.index')->with('success', 'Tugas diperbarui.');
    }

    public function destroy(CourseAssignment $course_assignment)
    {
        $this->ensureInstructorOwnsClassId(request()->user(), $course_assignment->course_class_id);
        $this->logger->log(
            request()->user(),
            'course.assignment.deleted',
            "Tugas '{$course_assignment->title}' dihapus",
            $course_assignment
        );
        $course_assignment->delete();

        return redirect()->route($this->getRoutePrefix() . 'course-assignment.index')->with('success', 'Tugas dihapus.');
    }

    public function exportScores(CourseAssignment $course_assignment): StreamedResponse
    {
        $this->ensureInstructorOwnsClassId(request()->user(), $course_assignment->course_class_id);
        $filename = 'scores-' . $course_assignment->id . '.csv';
        $submissions = CourseSubmission::with('user')
            ->where('course_assignment_id', $course_assignment->id)
            ->orderBy('user_id')
            ->orderByDesc('submitted_at')
            ->get();

        return response()->streamDownload(function () use ($submissions, $course_assignment) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Assignment', $course_assignment->title]);
            fputcsv($out, ['Kelas', optional($course_assignment->course)->title]);
            fputcsv($out, []);
            fputcsv($out, ['Nama', 'Email', 'Versi', 'Total Skor', 'Status', 'Terlambat', 'Menit Telat', 'Dikirim Pada']);
            foreach ($submissions as $submission) {
                fputcsv($out, [
                    $submission->user->name ?? '-',
                    $submission->user->email ?? '-',
                    $submission->version,
                    $submission->total_score,
                    $submission->status,
                    $submission->late ? 'Ya' : 'Tidak',
                    $submission->late_minutes,
                    optional($submission->submitted_at)->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'course_class_id' => 'required|exists:course_classes,id',
            'course_module_id' => 'nullable|exists:course_modules,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:essay,file,quiz',
            'quiz_scope' => 'nullable|in:class,selection',
            'assessment_type' => 'nullable|in:regular,module_quiz,final_exam,final_project',
            'due_at' => 'nullable|date',
            'weight' => 'nullable|integer|min:0|max:100',
            'max_score' => 'nullable|integer|min:1|max:1000',
            'rubric_id' => 'nullable|uuid',
            'rubric' => 'nullable',
            'late_policy' => 'nullable|in:no-accept,penalty,allow',
            'penalty_percent' => 'nullable|integer|min:0|max:100',
            'is_active' => 'nullable|boolean',
            'status' => 'nullable|in:' . implode(',', array_keys(CourseAssignment::statuses())),
            'quiz_schema' => 'nullable|string',
            'quiz_settings' => 'nullable|string',
            'require_token' => 'nullable|boolean',
            'exam_token' => 'nullable|string|max:50',
            'exam_start_at' => 'nullable|date',
            'exam_end_at' => 'nullable|date|after:exam_start_at',
            'auto_submit' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['weight'] = $data['weight'] ?? 0;
        $data['max_score'] = $data['max_score'] ?? 100;

        // Parse rubric JSON if provided
        if (! empty($data['rubric'])) {
            $parsed = json_decode($data['rubric'], true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($parsed)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'rubric' => 'Format rubric harus JSON array: [{"criterion":"Kualitas","weight":50,"max_score":50,"description":"..."}]',
                ]);
            }
            $data['rubric'] = collect($parsed)->map(function ($item) {
                return [
                    'criterion' => $item['criterion'] ?? '',
                    'weight' => (float) ($item['weight'] ?? 0),
                    'max_score' => (float) ($item['max_score'] ?? 0),
                    'description' => $item['description'] ?? null,
                ];
            })->filter(fn ($c) => $c['criterion'] !== '')->values()->all();
        } else {
            $data['rubric'] = null;
        }

        $data['assessment_type'] = $data['assessment_type'] ?? 'regular';

        if ($data['assessment_type'] === 'module_quiz' && empty($data['course_module_id'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'course_module_id' => 'Bab wajib dipilih untuk Quiz Akhir Bab.',
            ]);
        }
        if ($data['assessment_type'] !== 'module_quiz') {
            $data['course_module_id'] = null;
        }

        if (in_array($data['assessment_type'], ['module_quiz', 'final_exam'], true)) {
            $data['type'] = 'quiz';
        }
        if ($data['assessment_type'] === 'final_project') {
            $data['type'] = $data['type'] === 'essay' ? 'essay' : 'file';
        }

        if (($data['type'] ?? null) === 'quiz') {
            $data['quiz_scope'] = $data['quiz_scope'] ?? 'class';
            $data['quiz_schema'] = $this->parseQuizSchema($data['quiz_schema'] ?? '');
            $data['quiz_settings'] = $this->parseQuizSettings($data['quiz_settings'] ?? '');
            $data['max_score'] = collect($data['quiz_schema'])->sum(fn ($q) => (float) ($q['score'] ?? 0)) ?: $data['max_score'];
        } else {
            $data['quiz_scope'] = null;
            $data['quiz_schema'] = null;
            $data['quiz_settings'] = null;
        }

        $data['require_token'] = $request->boolean('require_token');
        $data['auto_submit'] = $request->boolean('auto_submit');

        return $data;
    }

    private function assessmentOptions(): array
    {
        return [
            'regular' => 'Tugas/Quiz Reguler',
            'module_quiz' => 'Quiz Akhir Bab',
            'final_exam' => 'Ujian Final',
            'final_project' => 'Proyek Akhir',
        ];
    }

    private function applyWorkflow(Request $request, array &$data, ?CourseAssignment $assignment = null): void
    {
        $defaultStatus = $request->user()->hasPermission('approve-content') ? 'published' : 'draft';
        $currentStatus = $assignment ? $assignment->status : $defaultStatus;
        $requestedStatus = $data['status'] ?? $currentStatus;

        if (! $request->user()->hasPermission('approve-content')) {
            $requestedStatus = $requestedStatus === 'published' ? 'pending' : $requestedStatus;
        }

        $data['status'] = $requestedStatus ?: $currentStatus;

        if ($data['status'] === 'published') {
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
            $data['published_at'] = $assignment?->published_at ?? now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }
    }

    private function parseQuizSchema(string $raw): array
    {
        if (blank($raw)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quiz_schema' => 'Quiz membutuhkan daftar pertanyaan dalam format JSON.',
            ]);
        }

        $parsed = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($parsed)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quiz_schema' => 'Format quiz_schema harus JSON array.',
            ]);
        }

        $questions = collect($parsed)->map(function ($item, $index) {
            $questionId = $item['id'] ?? ('q' . ($index + 1));
            $options = collect($item['options'] ?? [])->map(function ($opt, $optIndex) {
                return [
                    'id' => $opt['id'] ?? ('o' . ($optIndex + 1)),
                    'text' => $opt['text'] ?? '',
                    'is_correct' => (bool) ($opt['is_correct'] ?? false),
                    'score' => isset($opt['score']) ? (float) $opt['score'] : null,
                ];
            })->filter(fn ($opt) => $opt['text'] !== '')->values()->all();

            return [
                'id' => $questionId,
                'text' => $item['text'] ?? '',
                'type' => $item['type'] ?? 'single_choice',
                'score' => (float) ($item['score'] ?? 0),
                'options' => $options,
            ];
        })->filter(fn ($q) => $q['text'] !== '' && ! empty($q['options']))->values();

        if ($questions->isEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quiz_schema' => 'Quiz minimal memiliki 1 pertanyaan dengan opsi jawaban.',
            ]);
        }

        return $questions->all();
    }

    private function parseQuizSettings(string $raw): ?array
    {
        if (blank($raw)) {
            return null;
        }

        $parsed = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($parsed)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quiz_settings' => 'Format quiz_settings harus JSON object (misal {"time_limit_minutes":30,"max_attempts":1}).',
            ]);
        }

        $settings = [];
        if (isset($parsed['time_limit_minutes'])) {
            $settings['time_limit_minutes'] = max(1, (int) $parsed['time_limit_minutes']);
        }
        if (isset($parsed['max_attempts'])) {
            $settings['max_attempts'] = max(1, (int) $parsed['max_attempts']);
        }

        return empty($settings) ? null : $settings;
    }
}
