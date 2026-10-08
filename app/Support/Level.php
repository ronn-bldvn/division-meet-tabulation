<?php

namespace App\Support;

/**
 * The two divisions of the meet: Elementary and High School.
 *
 * Levels are stored on games (events) as a plain string, matching the
 * string-based enums already used for game status and bracket type.
 */
class Level
{
    public const ELEMENTARY = 'elementary';

    public const HIGH_SCHOOL = 'high_school';

    /** Every level, keyed by stored value => display label. */
    public const ALL = [
        self::ELEMENTARY => 'Elementary',
        self::HIGH_SCHOOL => 'High School',
    ];

    /** @return array<int, string> */
    public static function keys(): array
    {
        return array_keys(self::ALL);
    }

    /** Human-readable label for a stored level value. */
    public static function label(?string $level): string
    {
        return self::ALL[$level] ?? 'Unassigned';
    }

    public static function isValid(?string $level): bool
    {
        return $level !== null && array_key_exists($level, self::ALL);
    }

    /**
     * Return the level if it is a known one, otherwise null. Used to ignore
     * junk query-string values instead of erroring.
     */
    public static function normalize(?string $level): ?string
    {
        return self::isValid($level) ? $level : null;
    }
}
