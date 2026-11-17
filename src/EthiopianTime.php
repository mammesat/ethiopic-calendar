<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar;

use InvalidArgumentException;

/**
 * Ethiopian time: a 12-hour clock that starts at sunrise, six hours behind
 * the 24-hour clock (06:00 → 12:00 morning), and four day periods.
 *
 *   night      00:00–05:59  →  6:00–11:59
 *   morning    06:00–11:59  → 12:00–5:59
 *   afternoon  12:00–17:59  →  6:00–11:59
 *   evening    18:00–23:59  → 12:00–5:59
 *
 * Ported from mammesat/filament-ethiopic-calendar (EthiopicTimeService).
 */
final readonly class EthiopianTime
{
    public const PERIODS = ['night', 'morning', 'afternoon', 'evening'];

    private function __construct(
        public int $hour,
        public int $minute,
        public string $period,
    ) {}

    public static function fromGregorian(int $hour, int $minute = 0): self
    {
        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
            throw new InvalidArgumentException("Invalid time {$hour}:{$minute}.");
        }

        $ethiopianHour = ($hour + 6) % 12;

        return new self($ethiopianHour === 0 ? 12 : $ethiopianHour, $minute, self::periodOf($hour));
    }

    public static function of(int $hour, int $minute, string $period): self
    {
        if ($hour < 1 || $hour > 12 || $minute < 0 || $minute > 59) {
            throw new InvalidArgumentException("Invalid Ethiopian time {$hour}:{$minute}.");
        }

        if (! in_array($period, self::PERIODS, true)) {
            throw new InvalidArgumentException("Unknown day period \"{$period}\".");
        }

        // night and afternoon run 6–11; morning and evening run 12, 1–5.
        $startsAtSix = $period === 'night' || $period === 'afternoon';

        if ($startsAtSix !== ($hour >= 6 && $hour <= 11)) {
            throw new InvalidArgumentException("{$hour}:00 is not in the {$period} period.");
        }

        return new self($hour, $minute, $period);
    }

    /** Parse "H:i" or "H:i:s" (24-hour). */
    public static function parse(string $time): ?self
    {
        if (! preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', trim($time), $m)) {
            return null;
        }

        $hour = (int) $m[1];
        $minute = (int) $m[2];

        return $hour <= 23 && $minute <= 59 ? self::fromGregorian($hour, $minute) : null;
    }

    public static function periodOf(int $gregorianHour): string
    {
        return self::PERIODS[intdiv($gregorianHour, 6)]
            ?? throw new InvalidArgumentException("Invalid hour {$gregorianHour}.");
    }

    /**
     * @return array{hour: int, minute: int}
     */
    public function toGregorian(): array
    {
        $base = array_search($this->period, self::PERIODS, true) * 6;
        // Within a period, Ethiopian 12 is its first hour, then 1, 2 … 5 (or 6 … 11).
        $offset = match (true) {
            $this->hour === 12 => 0,
            $this->hour >= 6 => $this->hour - 6,
            default => $this->hour,
        };

        return ['hour' => $base + $offset, 'minute' => $this->minute];
    }

    /** "4:30" — the period name is added by the Formatter. */
    public function clock(): string
    {
        return sprintf('%d:%02d', $this->hour, $this->minute);
    }
}
