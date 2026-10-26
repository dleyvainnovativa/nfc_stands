<?php

namespace App\Support;

use App\Models\Stand;

/**
 * Public stand codes for /t/{code}.
 *
 * 8-char base62, random (non-sequential) so codes can't be guessed or
 * enumerated from one another. 62^8 ≈ 2.18e14 — collisions are astronomically
 * rare, but we still check the DB and retry to be safe.
 *
 * Ambiguous look-alikes are kept (full base62) because codes are generated and
 * scanned, not typed — except the claim flow, where we uppercase+trim input and
 * match case-insensitively is NOT done (codes are case-sensitive); claimers copy
 * the exact code printed on the tag.
 */
class StandCode
{
    public const LENGTH = 8;
    private const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /** One random code of the configured length. */
    public static function random(int $length = self::LENGTH): string
    {
        $max = strlen(self::ALPHABET) - 1;
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= self::ALPHABET[random_int(0, $max)];
        }
        return $code;
    }

    /** A code guaranteed unique against the stands table (and an optional in-memory set). */
    public static function unique(array $taken = []): string
    {
        do {
            $code = self::random();
        } while (
            isset($taken[$code]) ||
            Stand::withoutTenantScope()->where('code', $code)->exists()
        );
        return $code;
    }

    /**
     * N unique codes in one pass, de-duped within the batch and against the DB.
     * Used by the super-admin "generate blank tags" tool.
     *
     * @return string[]
     */
    public static function uniqueBatch(int $count): array
    {
        $set = [];
        for ($i = 0; $i < $count; $i++) {
            $code = self::unique($set);
            $set[$code] = true;
        }
        return array_keys($set);
    }
}
