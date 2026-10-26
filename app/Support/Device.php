<?php

namespace App\Support;

/**
 * Minimal, dependency-free device classification from a User-Agent string.
 * Shared by the stand redirect and the survey submission paths.
 */
class Device
{
    public static function detect(?string $ua): string
    {
        $ua = strtolower((string) $ua);
        if ($ua === '') {
            return 'bot';
        }
        if (preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview/', $ua)) {
            return 'bot';
        }
        if (preg_match('/ipad|tablet|playbook|silk|(android(?!.*mobile))/', $ua)) {
            return 'tablet';
        }
        if (preg_match('/mobi|iphone|ipod|android|blackberry|opera mini|iemobile/', $ua)) {
            return 'mobile';
        }
        return 'desktop';
    }

    public static function hashIp(?string $ip): ?string
    {
        return $ip ? hash('sha256', $ip . '|' . config('app.key')) : null;
    }
}
