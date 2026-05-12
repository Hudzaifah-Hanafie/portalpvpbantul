<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\TrainingDocumentation;
use App\Models\TrainingSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class TrainingDocumentationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $schedules = $this->schedulesForInstructor($user->id);
        $scheduleOptions = $this->scheduleOptions($schedules);
        $programOptions = $this->programOptions($schedules);

        $programFilter = $request->input('program_id');
        $scheduleFilter = $request->input('schedule_id');
        $search = trim((string) $request->input('q', ''));

        $query = TrainingDocumentation::with(['trainingSchedule.program', 'owner'])
            ->where('owner_id', $user->id)
            ->orderByDesc('updated_at');

        if ($programFilter) {
            $query->whereHas('trainingSchedule', fn ($q) => $q->where('program_id', $programFilter));
        }
        if ($scheduleFilter) {
            $query->where('training_schedule_id', $scheduleFilter);
        }
        if ($search !== '') {
            $searchLower = function_exists('mb_strtolower') ? mb_strtolower($search) : strtolower($search);
            $query->whereRaw('LOWER(title) LIKE ?', ['%' . $searchLower . '%']);
        }

        $documentations = $query->get();
        $docsBySchedule = $documentations->groupBy('training_schedule_id');
        $groups = $this->buildGroups($schedules, $docsBySchedule);

        return view('training_documentations.index', [
            'groups' => $groups,
            'programOptions' => $programOptions,
            'scheduleOptions' => $scheduleOptions,
            'programFilter' => $programFilter,
            'scheduleFilter' => $scheduleFilter,
            'search' => $search,
            'routePrefix' => 'instructor.lms.training-documentations',
            'pageTitle' => 'Dokumentasi Pelatihan',
        ]);
    }

    public function create(Request $request)
    {
        $schedules = $this->schedulesForInstructor($request->user()->id);
        $scheduleOptions = $this->scheduleOptions($schedules);

        if ($scheduleOptions->isEmpty()) {
            return redirect()->route('instructor.lms.training-documentations.index')
                ->with('error', 'Belum ada program pelatihan yang ditugaskan untuk Anda.');
        }

        return view('training_documentations.form', [
            'documentation' => new TrainingDocumentation(),
            'scheduleOptions' => $scheduleOptions,
            'routePrefix' => 'instructor.lms.training-documentations',
            'pageTitle' => 'Tambah Dokumentasi Pelatihan',
            'action' => route('instructor.lms.training-documentations.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $schedules = $this->schedulesForInstructor($request->user()->id);
        $allowedScheduleIds = $schedules->pluck('id')->all();
        if (empty($allowedScheduleIds)) {
            return redirect()->route('instructor.lms.training-documentations.index')
                ->with('error', 'Belum ada program pelatihan yang ditugaskan untuk Anda.');
        }

        $data = $request->validate([
            'training_schedule_id' => ['required', Rule::in($allowedScheduleIds)],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link_url' => 'required|url|max:2048',
            'documented_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $data['owner_id'] = $request->user()->id;
        $data['created_by'] = $request->user()->id;
        $data['is_active'] = $request->boolean('is_active');

        TrainingDocumentation::create($data);

        return redirect()->route('instructor.lms.training-documentations.index')
            ->with('success', 'Dokumentasi berhasil ditambahkan.');
    }

    public function edit(Request $request, TrainingDocumentation $training_documentation)
    {
        $this->authorizeOwner($request, $training_documentation);

        $scheduleOptions = $this->scheduleOptions($this->schedulesForInstructor($request->user()->id));

        return view('training_documentations.form', [
            'documentation' => $training_documentation,
            'scheduleOptions' => $scheduleOptions,
            'routePrefix' => 'instructor.lms.training-documentations',
            'pageTitle' => 'Edit Dokumentasi Pelatihan',
            'action' => route('instructor.lms.training-documentations.update', $training_documentation->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, TrainingDocumentation $training_documentation)
    {
        $this->authorizeOwner($request, $training_documentation);

        $schedules = $this->schedulesForInstructor($request->user()->id);
        $allowedScheduleIds = $schedules->pluck('id')->all();

        $data = $request->validate([
            'training_schedule_id' => ['required', Rule::in($allowedScheduleIds)],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link_url' => 'required|url|max:2048',
            'documented_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $training_documentation->update($data);

        return redirect()->route('instructor.lms.training-documentations.index')
            ->with('success', 'Dokumentasi berhasil diperbarui.');
    }

    public function destroy(Request $request, TrainingDocumentation $training_documentation)
    {
        $this->authorizeOwner($request, $training_documentation);
        $training_documentation->delete();

        return redirect()->route('instructor.lms.training-documentations.index')
            ->with('success', 'Dokumentasi dihapus.');
    }

    private function schedulesForInstructor(string $instructorId): Collection
    {
        $classIds = CourseClass::where('instructor_id', $instructorId)->pluck('id');
        if ($classIds->isEmpty()) {
            return collect();
        }

        return TrainingSchedule::with('program')
            ->whereIn('id', $classIds)
            ->orderByDesc('mulai')
            ->get();
    }

    private function scheduleOptions(Collection $schedules): Collection
    {
        return $schedules->mapWithKeys(function (TrainingSchedule $schedule) {
            $label = $schedule->judul;
            if ($schedule->program?->judul) {
                $label = $schedule->program->judul . ' • ' . $label;
            }
            if ($schedule->batch_id) {
                $label .= ' • ' . $schedule->batch_id;
            }

            return [$schedule->id => $label];
        });
    }

    private function programOptions(Collection $schedules): Collection
    {
        return $schedules
            ->filter(fn (TrainingSchedule $schedule) => $schedule->program_id && $schedule->program)
            ->mapWithKeys(fn (TrainingSchedule $schedule) => [$schedule->program_id => $schedule->program->judul])
            ->sort();
    }

    private function buildGroups(Collection $schedules, Collection $docsBySchedule): Collection
    {
        if ($schedules->isEmpty()) {
            return collect();
        }

        return $schedules
            ->groupBy(fn (TrainingSchedule $schedule) => $schedule->program?->judul ?? 'LAINNYA')
            ->map(function (Collection $scheduleItems) use ($docsBySchedule) {
                return $scheduleItems->map(function (TrainingSchedule $schedule) use ($docsBySchedule) {
                    $startAt = $schedule->mulai ? Carbon::parse($schedule->mulai) : null;
                    $endAt = $schedule->selesai ? Carbon::parse($schedule->selesai) : null;
                    $dateRange = $startAt && $endAt
                        ? $startAt->translatedFormat('d M Y') . ' - ' . $endAt->translatedFormat('d M Y')
                        : ($startAt?->translatedFormat('d M Y') ?? '-');

                    return [
                        'id' => $schedule->id,
                        'title' => $schedule->judul ?? '-',
                        'batch' => $schedule->batch_id ?: '-',
                        'date_range' => $dateRange,
                        'docs' => $docsBySchedule->get($schedule->id, collect()),
                    ];
                })->values();
            });
    }

    private function authorizeOwner(Request $request, TrainingDocumentation $documentation): void
    {
        if ($documentation->owner_id !== $request->user()->id) {
            abort(403);
        }
    }
}
