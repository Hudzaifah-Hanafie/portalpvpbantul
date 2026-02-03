<?php

namespace App\Support;

use App\Models\SiteSetting;

class EmailSettings
{
    public static function confirmationsEnabled(): bool
    {
        return self::boolValue('email_confirmations_enabled', true);
    }

    public static function notificationsEnabled(): bool
    {
        return self::boolValue('email_notifications_enabled', true);
    }

    private static function boolValue(string $key, bool $default): bool
    {
        $value = SiteSetting::valueOf($key);
        if ($value === null) {
            return $default;
        }

        $parsed = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        return $parsed ?? $default;
    }
}
