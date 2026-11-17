<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use JsonSerializable;
use Stringable;

/**
 * Immutable Ethiopian calendar date.
 *
 * Month 13 is Pagume (5 days, 6 in a leap year). String form is
 * "YYYY-MM-DD" in the Ethiopian calendar, e.g. "2019-01-01" for 1 Meskerem 2019.
 */
final readonly class EthiopicDate implements JsonSerializable, Stringable
{
    public const PAGUME = 13;

    public const NEHASE = 12;

    private function __construct(
        public int $year,
        public int $month,
        public int $day,
    ) {}

    // ── Construction ────────────────────────────────────────

    public static function of(int $year, int $month, int $day): self
    {
        if (! Converter::isValidEthiopic($year, $month, $day)) {
            throw new InvalidArgumentException("Invalid Ethiopian date {$year}-{$month}-{$day}.");
        }

        return new self($year, $month, $day);
    }

    public static function tryOf(int $year, int $month, int $day): ?self
    {
        return Converter::isValidEthiopic($year, $month, $day) ? new self($year, $month, $day) : null;
    }

    /** Parse an Ethiopian "YYYY-MM-DD" string. */
    public static function parse(string $value): self
    {
        return self::tryParse($value)
            ?? throw new InvalidArgumentException("Invalid Ethiopian date string \"{$value}\".");
    }

    public static function tryParse(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        $parts = Day::parse($value);

        return $parts === null ? null : self::tryOf(...$parts);
    }

    /** From a Gregorian date (DateTimeInterface or "Y-m-d"). */
    public static function fromGregorian(DateTimeInterface|string $date): self
    {
        return self::fromJdn(Day::toJdn($date));
    }

    public static function fromJdn(int $jdn): self
    {
        $e = Converter::jdnToEthiopic($jdn);

        return new self($e['year'], $e['month'], $e['day']);
    }

    public static function today(): self
    {
        return self::fromGregorian(CarbonImmutable::today());
    }

    // ── Conversion ──────────────────────────────────────────

    public function toJdn(): int
    {
        return Converter::ethiopicToJdn($this->year, $this->month, $this->day);
    }

    public function toGregorian(): CarbonImmutable
    {
        return Day::fromJdn($this->toJdn());
    }

    public function toGregorianString(): string
    {
        return $this->toGregorian()->format('Y-m-d');
    }

    /** Ethiopian "YYYY-MM-DD". */
    public function toDateString(): string
    {
        return sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day);
    }

    // ── Facts ───────────────────────────────────────────────

    public function isLeapYear(): bool
    {
        return Converter::isLeapYear($this->year);
    }

    public function daysInMonth(): int
    {
        return Converter::daysInMonth($this->year, $this->month);
    }

    public function isPagume(): bool
    {
        return $this->month === self::PAGUME;
    }

    /** ISO day of week: 1 = Monday … 7 = Sunday. */
    public function dayOfWeek(): int
    {
        return Converter::dayOfWeek($this->toJdn());
    }

    // ── Day arithmetic ──────────────────────────────────────

    public function addDays(int $days): self
    {
        return self::fromJdn($this->toJdn() + $days);
    }

    public function subDays(int $days): self
    {
        return $this->addDays(-$days);
    }

    /** Signed number of days from this date to $other. */
    public function diffInDays(self $other): int
    {
        return $other->toJdn() - $this->toJdn();
    }

    // ── Month arithmetic (billing rules, blueprint §7) ──────

    /**
     * Add whole months with an anchor day. A Pagume date counts as 30 Nehase,
     * and the result is never in Pagume because months 1–12 all have 30 days.
     *
     * @param  int|null  $anchorDay  1–30; defaults to this date's day (30 for Pagume)
     */
    public function addMonths(int $months, ?int $anchorDay = null): self
    {
        $anchorDay ??= $this->anchorDay();

        if ($anchorDay < 1 || $anchorDay > 30) {
            throw new InvalidArgumentException("Ethiopian anchor day must be 1–30, got {$anchorDay}.");
        }

        $month = $this->isPagume() ? self::NEHASE : $this->month;
        $index = ($this->year * 12) + ($month - 1) + $months;

        return self::of(intdiv($index, 12), ($index % 12) + 1, $anchorDay);
    }

    public function addYears(int $years, ?int $anchorDay = null): self
    {
        return $this->addMonths($years * 12, $anchorDay);
    }

    /** The day number used for month arithmetic: Pagume dates anchor on 30. */
    public function anchorDay(): int
    {
        return $this->isPagume() ? 30 : $this->day;
    }

    // ── Calendar month (Pagume is its own short month) ──────

    public function startOfMonth(): self
    {
        return new self($this->year, $this->month, 1);
    }

    public function endOfMonth(): self
    {
        return new self($this->year, $this->month, $this->daysInMonth());
    }

    public function startOfYear(): self
    {
        return new self($this->year, 1, 1);
    }

    public function endOfYear(): self
    {
        return new self($this->year, self::PAGUME, Converter::daysInMonth($this->year, self::PAGUME));
    }

    // ── Comparison ──────────────────────────────────────────

    public function compareTo(self $other): int
    {
        return $this->toJdn() <=> $other->toJdn();
    }

    public function equals(self $other): bool
    {
        return $this->compareTo($other) === 0;
    }

    public function isBefore(self $other): bool
    {
        return $this->compareTo($other) < 0;
    }

    public function isAfter(self $other): bool
    {
        return $this->compareTo($other) > 0;
    }

    public function between(self $start, self $end): bool
    {
        return ! $this->isBefore($start) && ! $this->isAfter($end);
    }

    // ── Serialisation ───────────────────────────────────────

    public function __toString(): string
    {
        return $this->toDateString();
    }

    public function jsonSerialize(): string
    {
        return $this->toDateString();
    }
}
