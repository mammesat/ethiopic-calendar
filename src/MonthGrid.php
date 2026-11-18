<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar;

use InvalidArgumentException;

/**
 * The days of one month laid out as weeks, for date pickers.
 *
 * In the picker grid Pagume is its own short month (month 13), unlike
 * BillingMonth where it belongs to Nehase. Cells carry the JDN and the
 * Gregorian Y-m-d so a UI can hand back a storable value directly.
 */
final class MonthGrid
{
    /**
     * Rows of seven cells starting on $weekStart (ISO 1 = Monday … 7 = Sunday);
     * null pads days outside the month.
     *
     * @return list<list<array{jdn: int, iso: string, day: int}|null>>
     */
    public static function weeks(int $year, int $month, Calendar $calendar, int $weekStart = 1): array
    {
        if ($weekStart < 1 || $weekStart > 7) {
            throw new InvalidArgumentException("Week start must be an ISO weekday 1–7, got {$weekStart}.");
        }

        $first = self::firstJdn($year, $month, $calendar);
        $lead = (Converter::dayOfWeek($first) - $weekStart + 7) % 7;

        /** @var list<array{jdn: int, iso: string, day: int}|null> $cells */
        $cells = array_fill(0, $lead, null);

        for ($d = 0, $length = self::daysInMonth($year, $month, $calendar); $d < $length; $d++) {
            $cells[] = ['jdn' => $first + $d, 'iso' => Day::fromJdn($first + $d)->toDateString(), 'day' => $d + 1];
        }

        while (count($cells) % 7 !== 0) {
            $cells[] = null;
        }

        return array_chunk($cells, 7);
    }

    /** 13 for the Ethiopian grid (Pagume), 12 for the Gregorian one. */
    public static function monthsInYear(Calendar $calendar): int
    {
        return $calendar->isEthiopian() ? 13 : 12;
    }

    public static function daysInMonth(int $year, int $month, Calendar $calendar): int
    {
        self::assertMonth($month, $calendar);

        return $calendar->isEthiopian()
            ? Converter::daysInMonth($year, $month)
            : Arithmetic::daysInGregorianMonth($year, $month);
    }

    public static function firstJdn(int $year, int $month, Calendar $calendar): int
    {
        return $calendar->isEthiopian()
            ? Converter::ethiopicToJdn($year, $month, 1)
            : Converter::gregorianToJdn($year, $month, 1);
    }

    public static function lastJdn(int $year, int $month, Calendar $calendar): int
    {
        return self::firstJdn($year, $month, $calendar) + self::daysInMonth($year, $month, $calendar) - 1;
    }

    /**
     * Move a grid month by $delta months: Nehase → Pagume → Meskerem.
     *
     * @return array{0: int, 1: int} [year, month]
     */
    public static function step(int $year, int $month, int $delta, Calendar $calendar): array
    {
        $n = self::monthsInYear($calendar);
        $index = $year * $n + ($month - 1) + $delta;

        return [intdiv($index, $n), $index % $n + 1];
    }

    /**
     * [year, month] of a JDN in the grid's calendar.
     *
     * @return array{0: int, 1: int}
     */
    public static function monthOf(int $jdn, Calendar $calendar): array
    {
        $p = $calendar->isEthiopian() ? Converter::jdnToEthiopic($jdn) : Converter::jdnToGregorian($jdn);

        return [$p['year'], $p['month']];
    }

    private static function assertMonth(int $month, Calendar $calendar): void
    {
        if ($month < 1 || $month > self::monthsInYear($calendar)) {
            throw new InvalidArgumentException("Invalid {$calendar->value} month {$month}.");
        }
    }
}
