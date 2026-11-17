<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar;

use InvalidArgumentException;

/**
 * Gregorian <-> Ethiopian conversion through the Julian Day Number (JDN).
 *
 * Pure integer arithmetic, no formatting and no locale. The same algorithm
 * lives in resources/js/ethiopic.js; both are tested against
 * tests/fixtures/dates.json.
 *
 * Ported from mammesat/filament-ethiopic-calendar (EthiopicCalendarService).
 */
final class Converter
{
    /** JDN of 1 Meskerem 1 (Ethiopian epoch, Amete Mihret). */
    public const EPOCH_JDN = 1724221;

    /** JDN of 1 Meskerem 1 is the smallest date this package supports. */
    public const MIN_JDN = self::EPOCH_JDN;

    // ── Gregorian ───────────────────────────────────────────

    public static function gregorianToJdn(int $year, int $month, int $day): int
    {
        if (! checkdate($month, $day, $year)) {
            throw new InvalidArgumentException("Invalid Gregorian date {$year}-{$month}-{$day}.");
        }

        $a = intdiv(14 - $month, 12);
        $y = $year + 4800 - $a;
        $m = $month + (12 * $a) - 3;

        return $day
            + intdiv(153 * $m + 2, 5)
            + (365 * $y)
            + intdiv($y, 4)
            - intdiv($y, 100)
            + intdiv($y, 400)
            - 32045;
    }

    /**
     * @return array{year: int, month: int, day: int}
     */
    public static function jdnToGregorian(int $jdn): array
    {
        self::assertSupportedJdn($jdn);

        $a = $jdn + 32044;
        $b = intdiv(4 * $a + 3, 146097);
        $c = $a - intdiv(146097 * $b, 4);

        $d = intdiv(4 * $c + 3, 1461);
        $e = $c - intdiv(1461 * $d, 4);
        $m = intdiv(5 * $e + 2, 153);

        return [
            'year' => 100 * $b + $d - 4800 + intdiv($m, 10),
            'month' => $m + 3 - 12 * intdiv($m, 10),
            'day' => $e - intdiv(153 * $m + 2, 5) + 1,
        ];
    }

    // ── Ethiopian ───────────────────────────────────────────

    public static function ethiopicToJdn(int $year, int $month, int $day): int
    {
        if (! self::isValidEthiopic($year, $month, $day)) {
            throw new InvalidArgumentException("Invalid Ethiopian date {$year}-{$month}-{$day}.");
        }

        return self::ethiopicToJdnUnchecked($year, $month, $day);
    }

    /**
     * @return array{year: int, month: int, day: int}
     */
    public static function jdnToEthiopic(int $jdn): array
    {
        self::assertSupportedJdn($jdn);

        $year = intdiv(4 * ($jdn - self::EPOCH_JDN) + 1463, 1461);
        $month = intdiv($jdn - self::ethiopicToJdnUnchecked($year, 1, 1), 30) + 1;
        $day = $jdn - self::ethiopicToJdnUnchecked($year, $month, 1) + 1;

        return ['year' => $year, 'month' => $month, 'day' => $day];
    }

    // ── Shortcuts ───────────────────────────────────────────

    /**
     * @return array{year: int, month: int, day: int}
     */
    public static function toEthiopic(int $year, int $month, int $day): array
    {
        return self::jdnToEthiopic(self::gregorianToJdn($year, $month, $day));
    }

    /**
     * @return array{year: int, month: int, day: int}
     */
    public static function toGregorian(int $year, int $month, int $day): array
    {
        return self::jdnToGregorian(self::ethiopicToJdn($year, $month, $day));
    }

    // ── Rules ───────────────────────────────────────────────

    /** A year is leap (Pagume has 6 days) when year mod 4 = 3. */
    public static function isLeapYear(int $year): bool
    {
        return $year % 4 === 3;
    }

    public static function daysInMonth(int $year, int $month): int
    {
        if ($month < 1 || $month > 13) {
            throw new InvalidArgumentException("Invalid Ethiopian month {$month}.");
        }

        if ($month <= 12) {
            return 30;
        }

        return self::isLeapYear($year) ? 6 : 5;
    }

    public static function daysInYear(int $year): int
    {
        return self::isLeapYear($year) ? 366 : 365;
    }

    public static function isValidEthiopic(int $year, int $month, int $day): bool
    {
        if ($year < 1 || $month < 1 || $month > 13 || $day < 1) {
            return false;
        }

        return $day <= self::daysInMonth($year, $month);
    }

    /** ISO-8601 day of week: 1 = Monday … 7 = Sunday. JDN 0 was a Monday. */
    public static function dayOfWeek(int $jdn): int
    {
        return ($jdn % 7) + 1;
    }

    // ── Internals ───────────────────────────────────────────

    private static function ethiopicToJdnUnchecked(int $year, int $month, int $day): int
    {
        return self::EPOCH_JDN
            + 365 * ($year - 1)
            + intdiv($year, 4)
            + 30 * ($month - 1)
            + $day
            - 1;
    }

    private static function assertSupportedJdn(int $jdn): void
    {
        if ($jdn < self::MIN_JDN) {
            throw new InvalidArgumentException("Dates before 1 Meskerem 1 (JDN {$jdn}) are not supported.");
        }
    }
}
