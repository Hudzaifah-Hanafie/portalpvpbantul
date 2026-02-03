<?php

namespace App\Http\Controllers;

use App\Models\CourseAssignment;
use App\Models\CourseClass;
use App\Models\CourseSession;
use App\Models\CourseSubmission;
use App\Models\CourseAttendance;
use App\Models\CourseEnrollment;
use App\Support\EnrollmentPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CourseParticipantController extends Controller
{
    public function assignments(Request $request)
    {
        $classId = $request->input('class_id');
        $approvedIds = $this->enrolledClassIds($request->user());
        $pendingIds = CourseEnrollment::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->pluck('course_class_id')
            ->toArray();
        $availableIds = array_values(array_unique(array_merge($approvedIds, $pendingIds)));

        if ($classId && ! in_array($classId, $availableIds)) {
            abort(403, 'Anda tidak terdaftar pada kelas ini.');
        }

        $query = CourseAssignment::with('course')
            ->where('status', 'published')
            ->where('is_active', true)
            ->orderBy('due_at');

        if ($classId) {
            $query->where('course_class_id', $classId);
            if (in_array($classId, $pendingIds, true)) {
                $query->where('type', 'quiz');
            }
        } elseif (! $approvedIds && ! $pendingIds) {
            $query->whereRaw('1 = 0');
        } else {
            $query->where(function ($builder) use ($approvedIds, $pendingIds) {
                if ($approvedIds) {
                    $builder->whereIn('course_class_id', $approvedIds);
                }
                if ($pendingIds) {
                    $builder->orWhere(function ($subQuery) use ($pendingIds) {
                        $subQuery->whereIn('course_class_id', $pendingIds)
                            ->where('type', 'quiz');
                    });
                }
            });
        }

        $assignments = $query->paginate(15)->withQueryString();
        $classes = CourseClass::whereIn('id', $availableIds)->orderBy('title')->pluck('title', 'id');

        return view('participant.assignments.index', compact('assignments', 'classes', 'classId'));
    }

    public function myApplications(Request $request)
    {
        $user = $request->user();
        $enrollments = CourseEnrollment::with([
            'course',
            'trainingSchedule',
            'interviewAllocations.session',
            'interviewAllocations.score',
        ])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $classIds = $enrollments->pluck('course_class_id')->filter()->unique()->values();
        $quizAssignments = CourseAssignment::whereIn('course_class_id', $classIds)
            ->where('type', 'quiz')
            ->where('status', 'published')
            ->where('is_active', true)
            ->get();

        $quizAssignmentsByClass = $quizAssignments->groupBy('course_class_id');
        $cbtInfoByClass = [];
        foreach ($classIds as $classId) {
            $cbtInfoByClass[$classId] = $this->describeCbtWindow(
                $quizAssignmentsByClass->get($classId, collect())
            );
        }

        $statusOptions = CourseEnrollment::statuses();
        $adminStatuses = CourseEnrollment::adminStatuses();

        return view('participant.applications.index', compact(
            'enrollments',
            'cbtInfoByClass',
            'statusOptions',
            'adminStatuses'
        ));
    }

    public function myClasses(Request $request)
    {
        $enrolledIds = $this->enrolledClassIds($request->user());
        $classes = CourseClass::whereIn('id', $enrolledIds)
            ->withCount(['assignments' => fn ($q) => $q->where('status', 'published'), 'sessions'])
            ->orderBy('title')
            ->get();

        $this->shareConsentBanner($request, $classes);

        return view('participant.classes.index', compact('classes'));
    }

    public function classAnnouncements(Request $request, CourseClass $class)
    {
        $this->enforceEnrollment($request->user(), $class->id);
        $announcements = \App\Models\CourseAnnouncement::where('course_class_id', $class->id)
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('participant.announcements.index', compact('class', 'announcements'));
    }

    public function showAnnouncement(Request $request, CourseClass $class, \App\Models\CourseAnnouncement $announcement)
    {
        $this->enforceEnrollment($request->user(), $class->id);
        abort_unless($announcement->course_class_id === $class->id && $announcement->status === 'published', 404);

        return view('participant.announcements.show', compact('class', 'announcement'));
    }

    public function showAssignment(CourseAssignment $assignment)
    {
        abort_unless($assignment->status === 'published' && $assignment->is_active, 404);
        $enrollment = CourseEnrollment::where('user_id', auth()->id())
            ->where('course_class_id', $assignment->course_class_id)
            ->first();
        abort_unless($enrollment && in_array($enrollment->status, ['active', 'approved', 'completed', 'pending'], true), 403);
        if ($enrollment->status === 'pending' && $assignment->type !== 'quiz') {
            abort(403, 'Tugas ini hanya tersedia setelah Anda dinyatakan lolos seleksi.');
        }

        $submission = CourseSubmission::where('course_assignment_id', $assignment->id)
            ->where('user_id', auth()->id())
            ->latest('version')
            ->first();

        $quizAttempt = null;
        $quizQuestions = null;
        $quizExpiresAt = null;
        $quizMaxAttempts = null;
        $quizAttemptsLeft = null;
        $quizAttemptsDone = null;

        if ($assignment->type === 'quiz') {
            $quizMaxAttempts = $assignment->quiz_settings['max_attempts'] ?? 1;
            $quizAttemptsDone = CourseSubmission::where('course_assignment_id', $assignment->id)
                ->where('user_id', auth()->id())
                ->count();
            $quizAttemptsLeft = $quizMaxAttempts ? max(0, $quizMaxAttempts - $quizAttemptsDone) : null;
            $quizAttempt = $this->getQuizAttemptData(request(), $assignment);
            $quizQuestions = $quizAttempt['questions'];
            $quizExpiresAt = $quizAttempt['expires_at'] ?? null;
        }

        return view('participant.assignments.show', compact('assignment', 'submission', 'quizQuestions', 'quizExpiresAt', 'quizMaxAttempts', 'quizAttemptsLeft', 'quizAttemptsDone'));
    }

    public function submitAssignment(Request $request, CourseAssignment $assignment)
    {
        abort_unless($assignment->status === 'published' && $assignment->is_active, 404);
        $enrollment = CourseEnrollment::where('user_id', $request->user()->id)
            ->where('course_class_id', $assignment->course_class_id)
            ->first();
        abort_unless($enrollment && in_array($enrollment->status, ['active', 'approved', 'completed', 'pending'], true), 403);
        if ($enrollment->status === 'pending' && $assignment->type !== 'quiz') {
            abort(403, 'Tugas ini hanya tersedia setelah Anda dinyatakan lolos seleksi.');
        }

        if ($assignment->type === 'quiz') {
            return $this->submitQuiz($request, $assignment);
        }

        if ($assignment->due_at && $assignment->late_policy === 'no-accept' && now()->greaterThan($assignment->due_at)) {
            return back()->with('error', 'Batas waktu telah lewat. Tugas tidak dapat dikumpulkan.');
        }

        $data = $request->validate([
            'content_text' => 'nullable|string',
            'link_url' => 'nullable|url|max:255',
            'file_upload' => 'nullable|file|max:10240|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/zip,application/x-rar-compressed,image/jpeg,image/png',
        ]);

        if (blank($data['content_text'] ?? null) && blank($data['link_url'] ?? null) && ! $request->hasFile('file_upload')) {
            throw ValidationException::withMessages([
                'content_text' => 'Isi teks, tautan, atau unggah file.',
            ]);
        }

        $filePath = null;
        if ($request->hasFile('file_upload')) {
            // simpan privat untuk keamanan; akses via route terproteksi
            $path = $request->file('file_upload')->store('submissions');
            $filePath = $path;
        }

        $existing = CourseSubmission::where('course_assignment_id', $assignment->id)
            ->where('user_id', $request->user()->id)
            ->latest('version')
            ->first();

        $version = $existing ? $existing->version + 1 : 1;
        $now = now();
        $late = false;
        $lateMinutes = null;
        if ($assignment->due_at && $now->greaterThan($assignment->due_at)) {
            $late = true;
            $lateMinutes = $assignment->due_at->diffInMinutes($now);
        }

        $submission = CourseSubmission::create([
            'course_assignment_id' => $assignment->id,
            'user_id' => $request->user()->id,
            'content_text' => $data['content_text'] ?? null,
            'file_url' => $filePath,
            'link_url' => $data['link_url'] ?? null,
            'version' => $version,
            'late' => $late,
            'late_minutes' => $lateMinutes,
            'status' => 'submitted',
            'submitted_at' => $now,
        ]);

        return redirect()->route('participant.assignments.show', $assignment)->with('success', 'Submission terkirim.');
    }

    public function markAttendance(Request $request, CourseSession $session)
    {
        abort_unless($session->status === 'published' && $session->is_active, 404);
        $enrolledIds = $this->enrolledClassIds($request->user());
        abort_unless(in_array($session->course_class_id, $enrolledIds), 403);

        $data = $request->validate([
            'status' => 'nullable|in:' . implode(',', array_keys(CourseAttendance::statuses())),
            'reason' => 'nullable|string',
            'proof_url' => 'nullable|string|max:255',
            'attendance_code' => 'required|string',
        ]);

        if ($session->attendance_code) {
            $expires = $session->attendance_code_expires_at;
            $codeValid = hash_equals($session->attendance_code, $data['attendance_code']);
            if (! $codeValid || ($expires && $expires->isPast())) {
                throw ValidationException::withMessages(['attendance_code' => 'Kode presensi tidak valid atau sudah kadaluarsa.']);
            }
        }

        $status = $data['status'] ?? 'hadir';
        $attendance = CourseAttendance::updateOrCreate(
            [
                'course_session_id' => $session->id,
                'user_id' => $request->user()->id,
            ],
            [
                'status' => $status,
                'reason' => $data['reason'] ?? null,
                'proof_url' => $data['proof_url'] ?? null,
                'checked_at' => now(),
                'created_by' => $request->user()->id,
                'recorded_by' => $request->user()->id,
                'recorded_source' => 'self',
                'meta' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            ]
        );

        return back()->with('success', 'Presensi dicatat sebagai ' . (CourseAttendance::statuses()[$status] ?? $status));
    }

    private function submitQuiz(Request $request, CourseAssignment $assignment)
    {
        if (empty($assignment->quiz_schema)) {
            abort(404, 'Quiz belum dikonfigurasi.');
        }

        // window pemeriksaan
        if ($assignment->exam_start_at && now()->lt($assignment->exam_start_at)) {
            return back()->with('error', 'Ujian belum dibuka.');
        }
        if ($assignment->exam_end_at && now()->gt($assignment->exam_end_at) && ! $assignment->auto_submit) {
            return back()->with('error', 'Waktu ujian sudah ditutup.');
        }

        // token
        if ($assignment->require_token) {
            $request->validate(['exam_token' => 'required|string']);
            if (! hash_equals($assignment->exam_token ?? '', $request->input('exam_token'))) {
                return back()->with('error', 'Token ujian tidak valid.')->withInput();
            }
        }

        $maxAttempts = $assignment->quiz_settings['max_attempts'] ?? 1;
        $attemptCount = CourseSubmission::where('course_assignment_id', $assignment->id)
            ->where('user_id', $request->user()->id)
            ->count();

        if ($maxAttempts && $attemptCount >= $maxAttempts) {
            return back()->with('error', 'Batas percobaan quiz sudah tercapai.');
        }

        $attemptData = $this->getQuizAttemptData($request, $assignment);
        $expiresAt = $attemptData['expires_at'] ?? null;
        if ($expiresAt && now()->greaterThan($expiresAt) && ! $assignment->auto_submit) {
            return back()->with('error', 'Waktu quiz sudah habis.')->withInput();
        }
        if ($assignment->due_at && $assignment->late_policy === 'no-accept' && now()->greaterThan($assignment->due_at)) {
            return back()->with('error', 'Batas waktu telah lewat. Quiz tidak dapat dikumpulkan.');
        }

        $questionsForGrading = collect($attemptData['questions']);
        $answers = $request->validate([
            'answers' => 'required|array',
        ])['answers'];

        $score = 0;
        $maxScore = 0;
        $normalized = [];

        foreach ($questionsForGrading as $question) {
            $qid = $question['id'];
            if (! array_key_exists($qid, $answers)) {
                throw ValidationException::withMessages([
                    'answers' => "Pertanyaan {$qid} wajib dijawab.",
                ]);
            }

            $userAnswer = $answers[$qid];
            $options = collect($question['options'] ?? []);
            $correctOption = $options->firstWhere('is_correct', true);
            $questionScore = (float) ($question['score'] ?? 0);
            $maxScore += $questionScore;

            $isCorrect = $correctOption && $userAnswer === ($correctOption['id'] ?? null);
            $awarded = $isCorrect ? ($correctOption['score'] ?? $questionScore) : 0;
            $score += $awarded;

            $normalized[] = [
                'question_id' => $qid,
                'question' => $question['text'] ?? '',
                'selected' => $userAnswer,
                'correct' => $correctOption['id'] ?? null,
                'is_correct' => $isCorrect,
                'score' => $awarded,
            ];
        }

        $now = now();
        $late = false;
        $lateMinutes = null;
        if ($assignment->due_at && $now->greaterThan($assignment->due_at)) {
            $late = true;
            $lateMinutes = $assignment->due_at->diffInMinutes($now);
            if ($assignment->late_policy === 'penalty' && $assignment->penalty_percent) {
                $score = (int) round($score * (1 - ($assignment->penalty_percent / 100)));
            }
        }

        CourseSubmission::create([
            'course_assignment_id' => $assignment->id,
            'user_id' => $request->user()->id,
            'version' => $attemptCount + 1,
            'late' => $late,
            'late_minutes' => $lateMinutes,
            'status' => 'graded',
            'submitted_at' => $now,
            'graded_at' => $now,
            'total_score' => $score,
            'quiz_score' => $score,
            'quiz_answers' => $normalized,
        ]);

        $this->clearQuizAttemptData($request, $assignment);

        // simpan skor ke enrollment sebagai written_score (skala 0-100)
        $enrollment = CourseEnrollment::where('user_id', $request->user()->id)
            ->where('course_class_id', $assignment->course_class_id)
            ->first();
        if ($enrollment) {
            $percentScore = $maxScore > 0 ? round(($score / $maxScore) * 100, 2) : $score;
            $enrollment->written_score = $percentScore;
            $enrollment->save();
            $enrollment->updateFinalScore();

            if (EnrollmentPolicy::selectionMode() === EnrollmentPolicy::SELECTION_AUTO) {
                $passScore = EnrollmentPolicy::cbtPassScore();
                if ($percentScore < $passScore && $enrollment->status !== 'rejected' && $enrollment->status !== 'approved') {
                    $enrollment->status = 'rejected';
                    $enrollment->admin_status = 'rejected';
                    $enrollment->admin_note = "Gugur otomatis: nilai CBT di bawah {$passScore}.";
                    $enrollment->save();
                } elseif ($percentScore >= $passScore && $enrollment->admin_status !== 'rejected') {
                    $enrollment->admin_status = $enrollment->admin_status === 'verified' ? $enrollment->admin_status : 'verified';
                    $enrollment->save();
                }
            }
        }

        return redirect()->route('participant.assignments.show', $assignment)->with('success', 'Quiz terkirim dan dinilai otomatis.');
    }

    private function getQuizAttemptData(Request $request, CourseAssignment $assignment): array
    {
        $key = $this->quizSessionKey($assignment->id);
        $existing = $request->session()->get($key);

        if ($existing) {
            return $existing;
        }

        $questions = collect($assignment->quiz_schema)->shuffle()->map(function ($q) {
            $q['options'] = collect($q['options'] ?? [])->shuffle()->values()->all();
            return $q;
        })->values()->all();

        $startedAt = now();
        $expiresAt = null;
        $limitMinutes = $assignment->quiz_settings['time_limit_minutes'] ?? null;
        if ($limitMinutes) {
            $expiresAt = $startedAt->copy()->addMinutes($limitMinutes);
        }
        if ($assignment->exam_end_at) {
            $expiresAt = $expiresAt ? $expiresAt->min($assignment->exam_end_at) : $assignment->exam_end_at;
        }

        $payload = [
            'started_at' => $startedAt,
            'expires_at' => $expiresAt,
            'questions' => $questions,
        ];

        $request->session()->put($key, $payload);

        return $payload;
    }

    private function clearQuizAttemptData(Request $request, CourseAssignment $assignment): void
    {
        $request->session()->forget($this->quizSessionKey($assignment->id));
    }

    private function quizSessionKey(string $assignmentId): string
    {
        return "quiz_attempt:{$assignmentId}:" . auth()->id();
    }

    private function describeCbtWindow($assignments): array
    {
        if ($assignments->isEmpty()) {
            return [
                'state' => 'none',
                'label' => 'Belum dijadwalkan',
                'next_start' => null,
                'latest_end' => null,
            ];
        }

        $now = now();
        $open = $assignments->contains(function ($assignment) use ($now) {
            $startsOk = ! $assignment->exam_start_at || $now->gte($assignment->exam_start_at);
            $endsOk = ! $assignment->exam_end_at || $now->lte($assignment->exam_end_at);
            return $startsOk && $endsOk;
        });

        $starts = $assignments->pluck('exam_start_at')->filter();
        $ends = $assignments->pluck('exam_end_at')->filter();
        $nextStart = $starts->filter(fn ($start) => $start->isFuture())
            ->sortBy(fn ($date) => $date->timestamp)
            ->first();
        $latestEnd = $ends->sortByDesc(fn ($date) => $date->timestamp)->first();

        if ($open) {
            return [
                'state' => 'open',
                'label' => 'Sedang dibuka',
                'next_start' => $nextStart,
                'latest_end' => $latestEnd,
            ];
        }

        if ($nextStart) {
            return [
                'state' => 'scheduled',
                'label' => 'Dibuka ' . $nextStart->format('d M Y H:i'),
                'next_start' => $nextStart,
                'latest_end' => $latestEnd,
            ];
        }

        if ($latestEnd) {
            return [
                'state' => 'closed',
                'label' => 'Ditutup ' . $latestEnd->format('d M Y H:i'),
                'next_start' => null,
                'latest_end' => $latestEnd,
            ];
        }

        return [
            'state' => 'open',
            'label' => 'Siap dikerjakan',
            'next_start' => null,
            'latest_end' => null,
        ];
    }

    public function scanAttendance(Request $request, CourseSession $session)
    {
        abort_unless($session->status === 'published' && $session->is_active, 404);
        $enrolledIds = $this->enrolledClassIds($request->user());
        abort_unless(in_array($session->course_class_id, $enrolledIds), 403);

        $data = $request->validate([
            'code' => 'required|string',
            'status' => 'nullable|in:' . implode(',', array_keys(CourseAttendance::statuses())),
        ]);

        if (! hash_equals($session->attendance_code ?? '', $data['code'])) {
            return response()->json(['message' => 'Kode tidak valid'], 422);
        }
        if ($session->attendance_code_expires_at && $session->attendance_code_expires_at->isPast()) {
            return response()->json(['message' => 'Kode sudah kadaluarsa'], 422);
        }

        $status = $data['status'] ?? 'hadir';
        CourseAttendance::updateOrCreate(
            [
                'course_session_id' => $session->id,
                'user_id' => $request->user()->id,
            ],
            [
                'status' => $status,
                'checked_at' => now(),
                'created_by' => $request->user()->id,
                'recorded_by' => $request->user()->id,
                'recorded_source' => 'self-scan',
                'meta' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            ]
        );

        return response()->json(['message' => 'Presensi berhasil dicatat'], 200);
    }

    public function downloadSubmissionFile(CourseSubmission $submission)
    {
        abort_unless($submission->user_id === auth()->id(), 403);
        if (! $submission->file_url || ! Storage::exists($submission->file_url)) {
            abort(404);
        }

        return Storage::download($submission->file_url);
    }

    private function enrolledClassIds($user, bool $includePending = false): array
    {
        if (! $user) {
            return [];
        }

        $statuses = ['active', 'approved', 'completed'];
        if ($includePending) {
            $statuses[] = 'pending';
        }

        return CourseEnrollment::where('user_id', $user->id)
            ->whereIn('status', $statuses)
            ->pluck('course_class_id')
            ->toArray();
    }

    private function enforceEnrollment($user, string $classId): void
    {
        $enrolled = CourseEnrollment::where('user_id', $user->id)
            ->where('course_class_id', $classId)
            ->whereIn('status', ['active', 'approved', 'completed'])
            ->exists();

        abort_unless($enrolled, 403);
    }

    public function myProgress(Request $request)
    {
        $enrolledIds = $this->enrolledClassIds($request->user());
        $classes = CourseClass::whereIn('id', $enrolledIds)->with('instructor')->get();

        $this->shareConsentBanner($request, $classes);

        $rows = $classes->map(function (CourseClass $class) use ($request) {
            $attendances = CourseAttendance::whereHas('session', fn ($q) => $q->where('course_class_id', $class->id))
                ->where('user_id', $request->user()->id);

            $attended = (clone $attendances)->where('status', 'hadir')->count();
            $totalSessions = $attendances->count();
            $submissions = CourseSubmission::whereHas('assignment', fn ($q) => $q->where('course_class_id', $class->id))
                ->where('user_id', $request->user()->id)
                ->get();

            return [
                'class' => $class,
                'attended' => $attended,
                'total_sessions' => $totalSessions,
                'attendance_rate' => $totalSessions > 0 ? round(($attended / $totalSessions) * 100, 1) : null,
                'submitted' => $submissions->count(),
                'graded' => $submissions->where('status', 'graded')->count(),
                'avg_score' => ($avg = $submissions->whereNotNull('total_score')->avg('total_score')) ? round($avg, 1) : null,
            ];
        });

        return view('participant.progress', ['classes' => $rows]);
    }

    private function shareConsentBanner(Request $request, $classes): void
    {
        $classIds = $classes->pluck('id')->all();
        $consented = collect((array) $request->session()->get('consented_classes', []));
        $firstUnconsented = collect($classIds)->first(fn ($id) => ! $consented->contains($id));
        if ($firstUnconsented) {
            session(['consent_required' => true, 'consent_class' => $firstUnconsented]);
        }
    }

    public function consent(Request $request)
    {
        $data = $request->validate([
            'class_id' => 'required|exists:course_classes,id',
        ]);
        $consented = collect((array) $request->session()->get('consented_classes', []));
        $consented->push($data['class_id']);
        $request->session()->put('consented_classes', $consented->unique()->values()->all());
        $request->session()->forget('consent_required');
        $request->session()->forget('consent_class');

        return back()->with('success', 'Terima kasih, persetujuan Anda tercatat.');
    }
}
