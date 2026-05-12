<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RestrictsToInstructorClasses;
use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\TaskLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TaskLetterController extends Controller
{
    use RestrictsToInstructorClasses;

    public function index(Request $request)
    {
        $classFilter = $request->input('class_id');
        $query = TaskLetter::with('course')->orderByDesc('created_at');

        if ($this->isInstructorUser($request->user())) {
            $classIds = CourseClass::where('instructor_id', $request->user()->id)->pluck('id');
            $query->whereIn('course_class_id', $classIds);
        }
        if ($classFilter) {
            $query->where('course_class_id', $classFilter);
        }

        $letters = $query->paginate(20)->withQueryString();
        $classes = $this->scopedClassOptions($request->user());

        return view('admin.task_letter.index', compact('letters', 'classes', 'classFilter'));
    }

    public function create(Request $request)
    {
        $classes = $this->scopedClassOptions($request->user());
        $selectedClassId = $request->input('class_id');
        $selectedClass = null;
        if ($selectedClassId) {
            $selectedClass = CourseClass::with(['sessions', 'instructor'])
                ->whereKey($selectedClassId)
                ->first();
        }

        return view('admin.task_letter.form', [
            'letter' => new TaskLetter($this->buildDefaults($selectedClass)),
            'classes' => $classes,
            'selectedClass' => $selectedClass,
            'action' => route($this->routePrefix() . 'task-letter.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->ensureInstructorOwnsClassId($request->user(), $data['course_class_id']);
        $data['instructors'] = $this->normalizePeople($request->input('instructors', []), ['name', 'nip', 'position']);
        $data['committees'] = $this->normalizePeople($request->input('committees', []), ['name', 'role']);
        $data['participant_statuses'] = $request->input('participant_statuses', []);
        $data['created_by'] = $request->user()->id;

        $letter = TaskLetter::create($data);

        return redirect()
            ->route($this->routePrefix() . 'task-letter.show', $letter->id)
            ->with('success', 'Surat tugas berhasil dibuat.');
    }

    public function edit(TaskLetter $task_letter)
    {
        $this->ensureInstructorOwnsClassId(request()->user(), $task_letter->course_class_id);
        $classes = $this->scopedClassOptions(request()->user());

        return view('admin.task_letter.form', [
            'letter' => $task_letter,
            'classes' => $classes,
            'selectedClass' => $task_letter->course,
            'action' => route($this->routePrefix() . 'task-letter.update', $task_letter->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, TaskLetter $task_letter)
    {
        $data = $this->validateData($request);
        $this->ensureInstructorOwnsClassId($request->user(), $task_letter->course_class_id);
        $this->ensureInstructorOwnsClassId($request->user(), $data['course_class_id']);
        $data['instructors'] = $this->normalizePeople($request->input('instructors', []), ['name', 'nip', 'position']);
        $data['committees'] = $this->normalizePeople($request->input('committees', []), ['name', 'role']);
        $data['participant_statuses'] = $request->input('participant_statuses', []);

        $task_letter->update($data);

        return redirect()
            ->route($this->routePrefix() . 'task-letter.show', $task_letter->id)
            ->with('success', 'Surat tugas diperbarui.');
    }

    public function show(TaskLetter $task_letter)
    {
        $this->ensureInstructorOwnsClassId(request()->user(), $task_letter->course_class_id);
        $participants = $this->loadParticipants($task_letter);

        return view('admin.task_letter.show', [
            'letter' => $task_letter->load('course.instructor'),
            'participants' => $participants,
        ]);
    }

    public function print(TaskLetter $task_letter)
    {
        $this->ensureInstructorOwnsClassId(request()->user(), $task_letter->course_class_id);
        $participants = $this->loadParticipants($task_letter);

        return view('admin.task_letter.print', [
            'letter' => $task_letter->load('course.instructor'),
            'participants' => $participants,
        ]);
    }

    public function destroy(TaskLetter $task_letter)
    {
        $this->ensureInstructorOwnsClassId(request()->user(), $task_letter->course_class_id);
        $task_letter->delete();

        return redirect()
            ->route($this->routePrefix() . 'task-letter.index')
            ->with('success', 'Surat tugas dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'course_class_id' => 'required|exists:course_classes,id',
            'letter_number' => 'nullable|string|max:150',
            'legal_basis' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'location_name' => 'nullable|string|max:255',
            'location_link' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,published',
            'signed_city' => 'nullable|string|max:150',
            'signed_at' => 'nullable|date',
            'signatory_name' => 'nullable|string|max:150',
            'signatory_position' => 'nullable|string|max:150',
            'signatory_nip' => 'nullable|string|max:150',
        ]);
    }

    private function normalizePeople(array $payload, array $fields): array
    {
        $rows = [];
        $max = 0;
        foreach ($fields as $field) {
            $max = max($max, count($payload[$field] ?? []));
        }
        for ($i = 0; $i < $max; $i++) {
            $row = [];
            foreach ($fields as $field) {
                $row[$field] = trim($payload[$field][$i] ?? '');
            }
            if (!empty($row[$fields[0]])) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function loadParticipants(TaskLetter $task_letter)
    {
        $statuses = $task_letter->participant_statuses ?: ['approved', 'active', 'completed'];

        return CourseEnrollment::with('user')
            ->where('course_class_id', $task_letter->course_class_id)
            ->whereIn('status', $statuses)
            ->get()
            ->sortBy(fn ($row) => $row->user?->name ?? '');
    }

    private function buildDefaults(?CourseClass $class): array
    {
        if (!$class) {
            return ['status' => 'draft'];
        }

        $sessions = $class->sessions ?? collect();
        $startAt = $sessions->min('start_at');
        $endAt = $sessions->max('end_at');
        $firstLink = $sessions->pluck('meeting_link')->filter()->first();

        $startAt = $startAt ? Carbon::parse($startAt) : null;
        $endAt = $endAt ? Carbon::parse($endAt) : null;

        $instructors = [];
        if ($class->instructor) {
            $instructors[] = [
                'name' => $class->instructor->name,
                'nip' => '',
                'position' => 'Instruktur',
            ];
        }

        return [
            'course_class_id' => $class->id,
            'status' => 'draft',
            'start_date' => $startAt?->toDateString(),
            'end_date' => $endAt?->toDateString(),
            'start_time' => $startAt?->format('H:i'),
            'end_time' => $endAt?->format('H:i'),
            'location_name' => $class->format === 'daring' ? 'Daring (Zoom/Meet)' : 'Ruang/Workshop',
            'location_link' => $firstLink,
            'instructors' => $instructors,
            'participant_statuses' => ['approved', 'active', 'completed'],
        ];
    }

    private function routePrefix(): string
    {
        return request()->routeIs('instructor.*') ? 'instructor.lms.' : 'admin.';
    }
}
