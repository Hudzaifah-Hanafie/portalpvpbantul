<?php

namespace App\Traits;

use App\Models\CourseEnrollment;
use App\Models\CourseAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

trait ParticipantHelper
{
    protected function enrolledClassIds($user, bool $includePending = false): array
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

    protected function storeSignature(?string $payload, string $userId, string $sessionId): ?string
    {
        if (! $payload) {
            return null;
        }

        if (! preg_match('#^data:image/(png|jpeg);base64,#', $payload, $matches)) {
            throw ValidationException::withMessages(['signature_data' => 'Format tanda tangan tidak valid.']);
        }

        $payload = substr($payload, strpos($payload, ',') + 1);
        $binary = base64_decode($payload, true);
        if ($binary === false) {
            throw ValidationException::withMessages(['signature_data' => 'Tanda tangan tidak dapat dibaca.']);
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $path = sprintf(
            'attendances/signatures/%s/%s-%s.%s',
            now()->format('Ymd'),
            $userId,
            Str::uuid(),
            $extension
        );

        Storage::disk('public')->put($path, $binary);

        return Storage::url($path);
    }

    protected function enforceEnrollment($user, string $classId): void
    {
        $enrolled = CourseEnrollment::where('user_id', $user->id)
            ->where('course_class_id', $classId)
            ->whereIn('status', ['active', 'approved', 'completed'])
            ->exists();

        abort_unless($enrolled, 403);
    }

    protected function getQuizAttemptData(Request $request, CourseAssignment $assignment): array
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

    protected function clearQuizAttemptData(Request $request, CourseAssignment $assignment): void
    {
        $request->session()->forget($this->quizSessionKey($assignment->id));
    }

    protected function quizSessionKey(string $assignmentId): string
    {
        return "quiz_attempt:{$assignmentId}:" . auth()->id();
    }

    protected function describeCbtWindow($assignments): array
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

    protected function shareConsentBanner(Request $request, $classes): void
    {
        $classIds = $classes->pluck('id')->all();
        $consented = collect((array) $request->session()->get('consented_classes', []));
        $firstUnconsented = collect($classIds)->first(fn ($id) => ! $consented->contains($id));
        if ($firstUnconsented) {
            session(['consent_required' => true, 'consent_class' => $firstUnconsented]);
        }
    }
}
