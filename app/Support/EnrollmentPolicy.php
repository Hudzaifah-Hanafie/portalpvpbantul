<?php

namespace App\Support;

use App\Models\SiteSetting;

class EnrollmentPolicy
{
    public const SELECTION_MANUAL = 'manual';
    public const SELECTION_AUTO = 'auto';

    public const COUPON_APPROVED = 'approved';
    public const COUPON_PUBLISHED = 'published';

    public static function selectionMode(): string
    {
        $mode = (string) SiteSetting::valueOf('course_selection_mode', self::SELECTION_MANUAL);
        $mode = strtolower(trim($mode));

        return in_array($mode, [self::SELECTION_MANUAL, self::SELECTION_AUTO], true)
            ? $mode
            : self::SELECTION_MANUAL;
    }

    public static function couponIssueMode(): string
    {
        $mode = (string) SiteSetting::valueOf('course_coupon_issue_mode', self::COUPON_APPROVED);
        $mode = strtolower(trim($mode));

        return in_array($mode, [self::COUPON_APPROVED, self::COUPON_PUBLISHED], true)
            ? $mode
            : self::COUPON_APPROVED;
    }

    public static function cbtPassScore(): float
    {
        $score = (float) SiteSetting::valueOf('course_cbt_pass_score', 70);
        return max(0, min(100, $score));
    }

    public static function interviewPassScore(): float
    {
        $score = (float) SiteSetting::valueOf('course_interview_pass_score', 70);
        return max(0, min(100, $score));
    }
}
