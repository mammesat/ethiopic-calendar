<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar;

use Carbon\CarbonImmutable;
use DateTimeInterface;

/**
 * Fiscal year (blueprint §10.1).
 *
 * Ethiopian: 1 Hamle to 30 Sene, named after the Ethiopian year in which it
 * ends. A date in Hamle, Nehase or Pagume belongs to the next year's FY.
 * Gregorian: 1 January to 31 December.
 *
 * start and end are inclusive Gregorian dates.
 */
final readonly class FiscalYear
{
    public const ETHIOPIAN_FIRST_MONTH = 11; // Hamle

    private function __construct(
        public Calendar $calendar,
        public int $year,
        public CarbonImmutable $start,
        public CarbonImmutable $end,
    ) {}

    public static function of(int $year, Calendar $calendar): self
    {
        if ($calendar->isEthiopian()) {
            return new self(
                $calendar,
                $year,
                EthiopicDate::of($year - 1, self::ETHIOPIAN_FIRST_MONTH, 1)->toGregorian(),
                EthiopicDate::of($year, 10, 30)->toGregorian(),
            );
        }

        return new self($calendar, $year, Day::make($year, 1, 1), Day::make($year, 12, 31));
    }

    public static function containing(DateTimeInterface|string $date, Calendar $calendar): self
    {
        if ($calendar->isEthiopian()) {
            $e = EthiopicDate::fromGregorian($date);

            return self::of($e->month >= self::ETHIOPIAN_FIRST_MONTH ? $e->year + 1 : $e->year, $calendar);
        }

        return self::of(Day::normalize($date)->year, $calendar);
    }

    public static function current(Calendar $calendar): self
    {
        return self::containing(CarbonImmutable::today(), $calendar);
    }

    /** Last two digits of the year, as used in document numbers: FY 2019 → "19". */
    public function label(): string
    {
        return sprintf('%02d', $this->year % 100);
    }

    public function next(): self
    {
        return self::of($this->year + 1, $this->calendar);
    }

    public function previous(): self
    {
        return self::of($this->year - 1, $this->calendar);
    }

    public function contains(DateTimeInterface|string $date): bool
    {
        $jdn = Day::toJdn($date);

        return $jdn >= Day::toJdn($this->start) && $jdn <= Day::toJdn($this->end);
    }

    /**
     * The twelve billing months, in order. Ethiopian: Hamle, Nehase (with
     * Pagume), Meskerem … Sene.
     *
     * @return list<BillingMonth>
     */
    public function months(): array
    {
        $first = BillingMonth::containing($this->start, $this->calendar);

        return array_map(static fn (int $i): BillingMonth => $first->addMonths($i), range(0, 11));
    }

    public function equals(self $other): bool
    {
        return $this->calendar === $other->calendar && $this->year === $other->year;
    }
}
