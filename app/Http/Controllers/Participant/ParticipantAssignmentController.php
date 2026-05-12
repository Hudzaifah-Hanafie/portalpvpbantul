<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\CourseAssignment;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\CourseSubmission;
use App\Traits\ParticipantHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Support\EnrollmentPolicy;
use App\Http\Requests\Participant\SubmitAssignmentRequest;

class ParticipantAssignmentController extends Controller
{
    use ParticipantHelper;

    public function index(Request $request)
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
        $submissionMap = CourseSubmission::where('user_id', $request->user()->id)
            ->whereIn('course_assignment_id', $assignments->pluck('id'))
            ->orderByDesc('version')
            ->get()
            ->keyBy('course_assignment_id');
        $classes = CourseClass::whereIn('id', $availableIds)->orderBy('title')->pluck('title', 'id');

        return view('participant.assignments.index', compact('assignments', 'classes', 'classId', 'submissionMap'));
    }

    public function show(CourseAssignment $assignment)
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

    public function submit(SubmitAssignmentRequest $request, CourseAssignment $assignment)
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

        $data = $request->validated();
        
        $filePath = null;
        if ($request->hasFile('file_upload')) {
            // Memanfaatkan PHP GD Image Optimizer yang baru dibuat
            // Kompresi rasio ekstrem namun tetap terbaca agar server tidak jebol HDD-nya.
            $optimizer = new \App\Services\ImageOptimizer();
            $path = $optimizer->optimizeAndStore($request->file('file_upload'), 'submissions', 'public', 1080, 75);
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

    private function submitQuiz(Request $request, CourseAssignment $assignment)
    {
        if (empty($assignment->quiz_schema)) {
            abort(404, 'Quiz belum dikonfigurasi.');
        }

        if ($assignment->exam_start_at && now()->lt($assignment->exam_start_at)) {
            return back()->with('error', 'Ujian belum dibuka.');
        }
        if ($assignment->exam_end_at && now()->gt($assignment->exam_end_at) && ! $assignment->auto_submit) {
            return back()->with('error', 'Waktu ujian sudah ditutup.');
        }

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

        if ($assignment->quiz_scope !== 'selection') {
            $enrollment = CourseEnrollment::where('user_id', $request->user()->id)
                ->where('course_class_id', $assignment->course_class_id)
                ->first();
            if ($enrollment) {
                $enrollment->updateLearningOutcome();
            }
        }

        $this->clearQuizAttemptData($request, $assignment);

        if ($assignment->quiz_scope === 'selection') {
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
        }

        return redirect()->route('participant.assignments.show', $assignment)->with('success', 'Quiz terkirim dan dinilai otomatis.');
    }

    public function downloadFile(CourseSubmission $submission)
    {
        abort_unless($submission->user_id === auth()->id(), 403);
        if (! $submission->file_url || ! \Illuminate\Support\Facades\Storage::exists($submission->file_url)) {
            abort(404);
        }

        return \Illuminate\Support\Facades\Storage::download($submission->file_url);
    }
}
