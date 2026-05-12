<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\CourseAttendance;
use App\Models\CourseSession;
use App\Traits\ParticipantHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Participant\MarkAttendanceRequest;

class ParticipantAttendanceController extends Controller
{
    use ParticipantHelper;

    public function index(Request $request)
    {
        $enrolledIds = $this->enrolledClassIds($request->user());

        $sessions = CourseSession::with('course')
            ->whereIn('course_class_id', $enrolledIds)
            ->where('status', 'published')
            ->where('is_active', true)
            ->orderBy('start_at')
            ->get();

        $attendanceMap = CourseAttendance::where('user_id', $request->user()->id)
            ->whereIn('course_session_id', $sessions->pluck('id'))
            ->get()
            ->keyBy('course_session_id');

        return view('participant.sessions.index', [
            'sessions' => $sessions,
            'attendanceMap' => $attendanceMap,
        ]);
    }

    public function form(Request $request, CourseSession $session)
    {
        abort_unless($session->status === 'published' && $session->is_active, 404);
        $enrolledIds = $this->enrolledClassIds($request->user());
        abort_unless(in_array($session->course_class_id, $enrolledIds), 403);

        $attendance = CourseAttendance::where('course_session_id', $session->id)
            ->where('user_id', $request->user()->id)
            ->first();

        return view('participant.sessions.attendance', [
            'session' => $session,
            'attendance' => $attendance,
            'statusOptions' => CourseAttendance::statuses(),
        ]);
    }

    public function mark(MarkAttendanceRequest $request, CourseSession $session)
    {
        abort_unless($session->status === 'published' && $session->is_active, 404);
        $enrolledIds = $this->enrolledClassIds($request->user());
        abort_unless(in_array($session->course_class_id, $enrolledIds), 403);

        $data = $request->validated();

        if ($session->attendance_code) {
            $expires = $session->attendance_code_expires_at;
            $codeValid = hash_equals($session->attendance_code, $data['attendance_code']);
            if (! $codeValid || ($expires && $expires->isPast())) {
                throw ValidationException::withMessages(['attendance_code' => 'Kode presensi tidak valid atau sudah kadaluarsa.']);
            }
        }

        $status = $data['status'] ?? 'hadir';
        $signatureUrl = $this->storeSignature($data['signature_data'] ?? null, $request->user()->id, $session->id);
        $meta = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
        if ($signatureUrl) {
            $meta['signature_url'] = $signatureUrl;
        }

        $updates = [
            'status' => $status,
            'reason' => $data['reason'] ?? null,
            'checked_at' => now(),
            'created_by' => $request->user()->id,
            'recorded_by' => $request->user()->id,
            'recorded_source' => 'self',
            'meta' => $meta,
        ];
        if ($signatureUrl) {
            $updates['proof_url'] = $signatureUrl;
        } elseif (! empty($data['proof_url'])) {
            $updates['proof_url'] = $data['proof_url'];
        }

        CourseAttendance::updateOrCreate(
            [
                'course_session_id' => $session->id,
                'user_id' => $request->user()->id,
            ],
            $updates
        );

        return back()->with('success', 'Presensi dicatat sebagai ' . (CourseAttendance::statuses()[$status] ?? $status));
    }

    public function scan(Request $request, CourseSession $session)
    {
        abort_unless($session->status === 'published' && $session->is_active, 404);
        $enrolledIds = $this->enrolledClassIds($request->user());
        abort_unless(in_array($session->course_class_id, $enrolledIds), 403);

        $data = $request->validate([
            'code' => 'required|string',
            'status' => 'nullable|in:' . implode(',', array_keys(CourseAttendance::statuses())),
            'signature_data' => 'nullable|string',
        ]);

        if (! hash_equals($session->attendance_code ?? '', $data['code'])) {
            return response()->json(['message' => 'Kode tidak valid'], 422);
        }
        if ($session->attendance_code_expires_at && $session->attendance_code_expires_at->isPast()) {
            return response()->json(['message' => 'Kode sudah kadaluarsa'], 422);
        }

        $status = $data['status'] ?? 'hadir';
        $signatureUrl = $this->storeSignature($data['signature_data'] ?? null, $request->user()->id, $session->id);
        $meta = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
        if ($signatureUrl) {
            $meta['signature_url'] = $signatureUrl;
        }

        $updates = [
            'status' => $status,
            'checked_at' => now(),
            'created_by' => $request->user()->id,
            'recorded_by' => $request->user()->id,
            'recorded_source' => 'self-scan',
            'meta' => $meta,
        ];
        if ($signatureUrl) {
            $updates['proof_url'] = $signatureUrl;
        }

        CourseAttendance::updateOrCreate(
            [
                'course_session_id' => $session->id,
                'user_id' => $request->user()->id,
            ],
            $updates
        );

        return response()->json(['message' => 'Presensi berhasil dicatat'], 200);
    }
}
