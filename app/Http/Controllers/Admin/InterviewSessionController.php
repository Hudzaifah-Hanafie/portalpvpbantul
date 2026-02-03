<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\InterviewAllocation;
use App\Models\InterviewScore;
use App\Models\InterviewSession;
use App\Models\TrainingSchedule;
use App\Models\User;
use App\Support\EnrollmentPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InterviewSessionController extends Controller
{
    public function index(): View
    {
        $sessions = InterviewSession::with(['trainingSchedule', 'interviewer', 'allocations'])->latest('date')->paginate(15);

        return view('admin.interviews.index', compact('sessions'));
    }

    public function create(): View
    {
        return view('admin.interviews.form', [
            'session' => new InterviewSession([
                'date' => now()->toDateString(),
                'location' => 'Ruang Wawancara BLK',
            ]),
            'action' => route('admin.interview-session.store'),
            'method' => 'POST',
            'schedules' => TrainingSchedule::orderBy('mulai')->pluck('judul', 'id'),
            'interviewers' => User::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateSession($request);
        InterviewSession::create($data);

        return redirect()->route('admin.interview-session.index')->with('success', 'Sesi wawancara dibuat.');
    }

    public function edit(InterviewSession $interview_session): View
    {
        return view('admin.interviews.form', [
            'session' => $interview_session,
            'action' => route('admin.interview-session.update', $interview_session->id),
            'method' => 'PUT',
            'schedules' => TrainingSchedule::orderBy('mulai')->pluck('judul', 'id'),
            'interviewers' => User::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function update(Request $request, InterviewSession $interview_session): RedirectResponse
    {
        $data = $this->validateSession($request);
        $interview_session->update($data);

        return redirect()->route('admin.interview-session.index')->with('success', 'Sesi wawancara diperbarui.');
    }

    public function show(InterviewSession $interview_session): View
    {
        $interview_session->load([
            'trainingSchedule',
            'interviewer',
            'allocations.enrollment.user',
            'allocations.score',
        ]);

        $eligibleEnrollments = CourseEnrollment::with(['user'])
            ->whereHas('course', fn ($q) => $q->where('id', $interview_session->training_schedule_id))
            ->orWhere('course_class_id', $interview_session->training_schedule_id)
            ->get();

        return view('admin.interviews.show', [
            'session' => $interview_session,
            'eligibleEnrollments' => $eligibleEnrollments,
        ]);
    }

    public function destroy(InterviewSession $interview_session): RedirectResponse
    {
        $interview_session->delete();
        return redirect()->route('admin.interview-session.index')->with('success', 'Sesi wawancara dihapus.');
    }

    public function storeAllocation(Request $request, InterviewSession $interview_session): RedirectResponse
    {
        $data = $request->validate([
            'course_enrollment_id' => 'required|exists:course_enrollments,id',
            'status' => 'nullable|string|in:SCHEDULED,ATTENDED,ABSENT',
        ]);

        InterviewAllocation::updateOrCreate(
            [
                'interview_session_id' => $interview_session->id,
                'course_enrollment_id' => $data['course_enrollment_id'],
            ],
            [
                'status' => $data['status'] ?? 'SCHEDULED',
            ]
        );

        return back()->with('success', 'Peserta ditambahkan ke jadwal wawancara.');
    }

    public function updateAllocationStatus(Request $request, InterviewAllocation $allocation): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|string|in:SCHEDULED,ATTENDED,ABSENT',
        ]);

        $allocation->update(['status' => $data['status']]);

        return back()->with('success', 'Status peserta diperbarui.');
    }

    public function storeScore(Request $request, InterviewAllocation $allocation): RedirectResponse
    {
        $data = $request->validate([
            'score_communication' => 'required|integer|min:0|max:10',
            'score_motivation' => 'required|integer|min:0|max:10',
            'score_technical' => 'required|integer|min:0|max:10',
            'interviewer_notes' => 'nullable|string',
        ]);

        $final = round(
            ($data['score_communication'] + $data['score_motivation'] + $data['score_technical']) / 3,
            2
        );

        InterviewScore::updateOrCreate(
            ['interview_allocation_id' => $allocation->id],
            array_merge($data, ['final_score' => $final])
        );

        if ($allocation->enrollment) {
            $allocation->enrollment->interview_score = $final * 10; // skala 100
            $allocation->enrollment->save();
            $allocation->enrollment->updateFinalScore();

            $enrollment = $allocation->enrollment;
            if (EnrollmentPolicy::selectionMode() === EnrollmentPolicy::SELECTION_AUTO
                && ! in_array($enrollment->status, ['approved', 'rejected'], true)) {
                $cbtPass = EnrollmentPolicy::cbtPassScore();
                $interviewPass = EnrollmentPolicy::interviewPassScore();
                $cbtScore = $enrollment->written_score ?? 0;
                $interviewScore = $enrollment->interview_score ?? 0;

                if ($cbtScore < $cbtPass || $interviewScore < $interviewPass) {
                    $enrollment->status = 'rejected';
                    $enrollment->admin_status = 'rejected';
                    $enrollment->admin_note = "Gugur otomatis: nilai CBT < {$cbtPass} atau wawancara < {$interviewPass}.";
                    $enrollment->save();
                } else {
                    $enrollment->status = 'approved';
                    $enrollment->admin_status = 'verified';
                    $enrollment->save();

                    if (EnrollmentPolicy::couponIssueMode() === EnrollmentPolicy::COUPON_APPROVED) {
                        $enrollment->issueCoupon('approved', $request->user()->id);
                    }
                }
            }
        }

        return back()->with('success', 'Nilai wawancara disimpan.');
    }

    private function validateSession(Request $request): array
    {
        return $request->validate([
            'training_schedule_id' => 'required|exists:training_schedules,id',
            'interviewer_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'nullable|string|max:255',
            'quota' => 'required|integer|min:1|max:500',
        ]);
    }
}
