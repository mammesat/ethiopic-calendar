<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * Internal helpers for date-only values.
 *
 * Every public API in this package accepts a Gregorian date as a
 * DateTimeInterface or a strict "Y-m-d" string and returns CarbonImmutable
 * at midnight in the default timezone. Time and timezone of an input
 * DateTimeInterface are ignored: only its local Y-m-d is used.
 *
 * Day arithmetic goes through the Julian Day Number, so it is never
 * affected by DST or time-of-day.
 *
 * @internal
 */
final class Day
{
    private const PATTERN = '/^(\d{4})-(\d{2})-(\d{2})$/';

    /**
     * @return array{0: int, 1: int, 2: int}|null
     */
    public static function parse(string $value): ?array
    {
        if (! preg_match(self::PATTERN, $value, $m)) {
            return null;
        }

        $parts = [(int) $m[1], (int) $m[2], (int) $m[3]];

        return $parts;
    }

    public static function toJdn(DateTimeInterface|string $date): int
    {
        [$y, $m, $d] = self::parts($date);

        return Converter::gregorianToJdn($y, $m, $d);
    }

    public static function fromJdn(int $jdn): CarbonImmutable
    {
        $g = Converter::jdnToGregorian($jdn);

        return self::make($g['year'], $g['month'], $g['day']);
    }

    public static function normalize(DateTimeInterface|string $date): CarbonImmutable
    {
        [$y, $m, $d] = self::parts($date);

        if (! checkdate($m, $d, $y)) {
            throw new InvalidArgumentException("Invalid Gregorian date {$y}-{$m}-{$d}.");
        }

        return self::make($y, $m, $d);
    }

    public static function make(int $year, int $month, int $day): CarbonImmutable
    {
        return CarbonImmutable::create($year, $month, $day, 0, 0, 0)
            ?? throw new InvalidArgumentException("Invalid Gregorian date {$year}-{$month}-{$day}.");
    }

    public static function addDays(DateTimeInterface|string $date, int $days): CarbonImmutable
    {
        return self::fromJdn(self::toJdn($date) + $days);
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function parts(DateTimeInterface|string $date): array
    {
        if ($date instanceof DateTimeInterface) {
            return [(int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j')];
        }

        return self::parse($date)
            ?? throw new InvalidArgumentException("Expected a Gregorian date as Y-m-d, got \"{$date}\".");
    }
}
