<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * Calendar-aware date arithmetic on Gregorian dates (blueprint §7).
 *
 * Inputs and outputs are Gregorian (DateTimeInterface or "Y-m-d" in,
 * CarbonImmutable at midnight out); the Calendar argument decides whose
 * months are counted.
 *
 * Month addition always uses an anchor day, and callers that step through
 * a schedule should compute every period from the start date
 * (addMonths($start, $k)) rather than chaining, so a short month never
 * shifts the following ones:
 *
 *   Gregorian, anchor 31:  31 Jan → 28 Feb → 31 Mar
 *   Ethiopian, Pagume 3:   anchor 30 → 30 Meskerem → 30 Tikimt …
 */
final class Arithmetic
{
    public static function addMonths(
        DateTimeInterface|string $date,
        int $months,
        Calendar $calendar,
        ?int $anchorDay = null,
    ): CarbonImmutable {
        if ($calendar->isEthiopian()) {
            return EthiopicDate::fromGregorian($date)->addMonths($months, $anchorDay)->toGregorian();
        }

        $date = Day::normalize($date);
        $anchorDay ??= $date->day;

        if ($anchorDay < 1 || $anchorDay > 31) {
            throw new InvalidArgumentException("Gregorian anchor day must be 1–31, got {$anchorDay}.");
        }

        $index = ($date->year * 12) + ($date->month - 1) + $months;
        $year = intdiv($index, 12);
        $month = ($index % 12) + 1;
        $lastDay = self::daysInGregorianMonth($year, $month);

        return Day::make($year, $month, min($anchorDay, $lastDay));
    }

    public static function addYears(
        DateTimeInterface|string $date,
        int $years,
        Calendar $calendar,
        ?int $anchorDay = null,
    ): CarbonImmutable {
        return self::addMonths($date, $years * 12, $calendar, $anchorDay);
    }

    /**
     * The anchor day of a start date: its day number, except that a Pagume
     * date anchors on 30 (Pagume is the tail of Nehase).
     */
    public static function anchorDay(DateTimeInterface|string $date, Calendar $calendar): int
    {
        return $calendar->isEthiopian()
            ? EthiopicDate::fromGregorian($date)->anchorDay()
            : Day::normalize($date)->day;
    }

    /**
     * Lease end date: the day before the anniversary of the start date,
     * $years later, in the given calendar (blueprint §7.3).
     */
    public static function endDateFor(DateTimeInterface|string $start, int $years, Calendar $calendar): CarbonImmutable
    {
        if ($years < 1) {
            throw new InvalidArgumentException("Duration must be at least one year, got {$years}.");
        }

        return Day::addDays(self::addYears($start, $years, $calendar), -1);
    }

    public static function addDays(DateTimeInterface|string $date, int $days): CarbonImmutable
    {
        return Day::addDays($date, $days);
    }

    /** Signed whole days from $from to $to. */
    public static function diffInDays(DateTimeInterface|string $from, DateTimeInterface|string $to): int
    {
        return Day::toJdn($to) - Day::toJdn($from);
    }

    /** ISO day of week: 1 = Monday … 7 = Sunday. */
    public static function dayOfWeek(DateTimeInterface|string $date): int
    {
        return Converter::dayOfWeek(Day::toJdn($date));
    }

    /** Days in a Gregorian month, without relying on ext-calendar. */
    public static function daysInGregorianMonth(int $year, int $month): int
    {
        return match ($month) {
            2 => ($year % 4 === 0 && ($year % 100 !== 0 || $year % 400 === 0)) ? 29 : 28,
            4, 6, 9, 11 => 30,
            default => 31,
        };
    }
}
