<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CourseEnrollment extends Model
{
    use HasUuid;

    protected $fillable = [
        'course_class_id',
        'user_id',
        'status',
        'admin_status',
        'admin_note',
        'written_score',
        'interview_score',
        'final_score',
        'created_by',
        'muted_until',
        'completed_at',
        'certificate_url',
        'coupon_code',
        'coupon_issued_at',
        'coupon_issue_mode',
        'coupon_issued_by',
    ];

    protected $casts = [
        'muted_until' => 'datetime',
        'completed_at' => 'datetime',
        'coupon_issued_at' => 'datetime',
        'written_score' => 'float',
        'interview_score' => 'float',
        'final_score' => 'float',
    ];

    public static function statuses(): array
    {
        return [
            'active' => 'Aktif',
            'approved' => 'Disetujui',
            'pending' => 'Pending',
            'rejected' => 'Ditolak',
            'blocked' => 'Diblokir',
            'completed' => 'Selesai',
        ];
    }

    public static function adminStatuses(): array
    {
        return [
            'pending' => 'Belum Dicek',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'course_class_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainingSchedule(): BelongsTo
    {
        return $this->belongsTo(TrainingSchedule::class, 'course_class_id');
    }

    public function interviewAllocations(): HasMany
    {
        return $this->hasMany(InterviewAllocation::class, 'course_enrollment_id');
    }

    public function issueCoupon(string $mode, ?string $issuedBy = null): bool
    {
        if ($this->coupon_code) {
            return false;
        }

        $this->coupon_code = $this->generateCouponCode();
        $this->coupon_issued_at = now();
        $this->coupon_issue_mode = $mode;
        $this->coupon_issued_by = $issuedBy;
        $this->save();

        return true;
    }

    private function generateCouponCode(): string
    {
        do {
            $code = 'PVP-' . Str::upper(Str::random(6));
        } while (self::where('coupon_code', $code)->exists());

        return $code;
    }

    public function updateFinalScore(): void
    {
        $written = $this->written_score;
        $interview = $this->interview_score;

        if ($written === null && $interview === null) {
            return;
        }

        $writtenPart = $written !== null ? $written * 0.4 : 0;
        $interviewPart = $interview !== null ? $interview * 0.6 : 0;
        $this->final_score = round($writtenPart + $interviewPart, 2);
        $this->save();
    }
}
