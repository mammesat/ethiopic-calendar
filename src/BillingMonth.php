<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * One of the twelve months of a year for billing and reporting.
 *
 * In the Ethiopian calendar Nehase (month 12) runs from 1 Nehase to the last
 * day of Pagume, so the year has exactly twelve billing months (blueprint §7.2).
 * In the Gregorian calendar it is the plain calendar month.
 *
 * start and end are inclusive Gregorian dates.
 */
final readonly class BillingMonth
{
    private function __construct(
        public Calendar $calendar,
        public int $year,
        public int $month,
        public CarbonImmutable $start,
        public CarbonImmutable $end,
    ) {}

    public static function of(int $year, int $month, Calendar $calendar): self
    {
        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException("Billing month must be 1–12, got {$month}.");
        }

        if ($calendar->isEthiopian()) {
            $first = EthiopicDate::of($year, $month, 1);
            $last = $month === EthiopicDate::NEHASE ? $first->endOfYear() : $first->endOfMonth();

            return new self($calendar, $year, $month, $first->toGregorian(), $last->toGregorian());
        }

        return new self(
            $calendar,
            $year,
            $month,
            Day::make($year, $month, 1),
            Day::make($year, $month, Arithmetic::daysInGregorianMonth($year, $month)),
        );
    }

    /** The billing month that contains a Gregorian date. A Pagume date is in Nehase. */
    public static function containing(DateTimeInterface|string $date, Calendar $calendar): self
    {
        if ($calendar->isEthiopian()) {
            $e = EthiopicDate::fromGregorian($date);

            return self::of($e->year, $e->isPagume() ? EthiopicDate::NEHASE : $e->month, $calendar);
        }

        $g = Day::normalize($date);

        return self::of($g->year, $g->month, $calendar);
    }

    public static function current(Calendar $calendar): self
    {
        return self::containing(CarbonImmutable::today(), $calendar);
    }

    public function addMonths(int $months): self
    {
        $index = ($this->year * 12) + ($this->month - 1) + $months;

        return self::of(intdiv($index, 12), ($index % 12) + 1, $this->calendar);
    }

    public function next(): self
    {
        return $this->addMonths(1);
    }

    public function previous(): self
    {
        return $this->addMonths(-1);
    }

    public function contains(DateTimeInterface|string $date): bool
    {
        $jdn = Day::toJdn($date);

        return $jdn >= Day::toJdn($this->start) && $jdn <= Day::toJdn($this->end);
    }

    public function lengthInDays(): int
    {
        return Day::toJdn($this->end) - Day::toJdn($this->start) + 1;
    }

    public function equals(self $other): bool
    {
        return $this->calendar === $other->calendar
            && $this->year === $other->year
            && $this->month === $other->month;
    }
}
