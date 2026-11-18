<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar;

use DateTimeInterface;
use Illuminate\Contracts\Translation\Translator;
use InvalidArgumentException;

/**
 * Formats dates as locale × calendar × format.
 *
 * Formats:
 *   long    1 Meskerem 2019        መስከረም 1 2019
 *   medium  1 Mes 2019             መስከ 1, 2019
 *   short   01/01/2019
 *   month   Meskerem 2019          (billing-month and period headers)
 *
 * Options: weekday (prefix the weekday name), era (append E.C. / ዓ.ም.),
 * secondary (append the same date in the other calendar, medium format).
 *
 * Patterns and names live in resources/lang/{locale}/calendar.php.
 */
final class Formatter
{
    public const FORMATS = ['long', 'medium', 'short', 'month'];

    private const KEY = 'ethiopic-calendar::calendar.';

    public function __construct(
        private readonly Translator $translator,
        private readonly EthiopicCalendar $calendars,
    ) {}

    /**
     * Format a Gregorian date (DateTimeInterface or "Y-m-d"). Returns null for null or "".
     */
    public function date(
        DateTimeInterface|string|null $date,
        string $format = 'medium',
        ?Calendar $calendar = null,
        ?string $locale = null,
        bool $weekday = false,
        bool $era = false,
        bool $secondary = false,
    ): ?string {
        if ($date === null || $date === '') {
            return null;
        }

        if (! in_array($format, self::FORMATS, true)) {
            throw new InvalidArgumentException("Unknown date format \"{$format}\".");
        }

        $calendar ??= $this->calendars->calendar();
        [$year, $month, $day] = $this->parts($date, $calendar);

        $text = $this->pattern($format, $locale, [
            ':day' => (string) $day,
            ':dd' => sprintf('%02d', $day),
            ':mm' => sprintf('%02d', $month),
            ':year' => (string) $year,
            ':month' => $this->monthName($month, $calendar, $format !== 'medium', $locale),
        ]);

        if ($era) {
            $text = $this->pattern('with_era', $locale, [
                ':date' => $text,
                ':era' => $this->line('era.'.$calendar->value, $locale),
            ]);
        }

        if ($weekday && $format !== 'month') {
            $text = $this->pattern('weekday', $locale, [
                ':weekday' => $this->weekdayName(Arithmetic::dayOfWeek($date), false, $locale),
                ':date' => $text,
            ]);
        }

        if ($secondary) {
            $other = $calendar->isEthiopian() ? Calendar::Gregorian : Calendar::Ethiopian;

            $text = $this->pattern('secondary', $locale, [
                ':date' => $text,
                ':secondary' => (string) $this->date($date, $format === 'month' ? 'month' : 'medium', $other, $locale, era: true),
            ]);
        }

        return $text;
    }

    /**
     * Format a time (DateTimeInterface or "H:i[:s]") as Ethiopian time:
     * "4:30 in the morning" / "ጠዋት 4:30". With $ethiopian = false: "10:30".
     */
    public function time(DateTimeInterface|string|null $time, ?string $locale = null, bool $ethiopian = true): ?string
    {
        if ($time === null || $time === '') {
            return null;
        }

        $value = $time instanceof DateTimeInterface
            ? EthiopianTime::fromGregorian((int) $time->format('G'), (int) $time->format('i'))
            : EthiopianTime::parse($time);

        if ($value === null) {
            return null;
        }

        if (! $ethiopian) {
            return sprintf('%02d:%02d', ...array_values($value->toGregorian()));
        }

        return $this->pattern('time', $locale, [
            ':time' => $value->clock(),
            ':period' => $this->line('day_periods.'.$value->period, $locale),
        ]);
    }

    public function dateTime(
        ?DateTimeInterface $value,
        string $format = 'medium',
        ?Calendar $calendar = null,
        ?string $locale = null,
        bool $ethiopianTime = true,
    ): ?string {
        if ($value === null) {
            return null;
        }

        return $this->pattern('datetime', $locale, [
            ':date' => (string) $this->date($value, $format, $calendar, $locale),
            ':time' => (string) $this->time($value, $locale, $ethiopianTime),
        ]);
    }

    public function billingMonth(BillingMonth $month, ?string $locale = null): string
    {
        return $this->pattern('month', $locale, [
            ':month' => $this->monthName($month->month, $month->calendar, true, $locale),
            ':year' => (string) $month->year,
        ]);
    }

    public function fiscalYear(FiscalYear $year, ?string $locale = null): string
    {
        return $this->translator->get(self::KEY.'fiscal_year', ['year' => (string) $year->year], $locale);
    }

    public function monthName(int $month, ?Calendar $calendar = null, bool $long = true, ?string $locale = null): string
    {
        $calendar ??= $this->calendars->calendar();
        $max = $calendar->isEthiopian() ? 13 : 12;

        if ($month < 1 || $month > $max) {
            throw new InvalidArgumentException("Invalid {$calendar->value} month {$month}.");
        }

        return $this->line($calendar->value.'_months'.($long ? '' : '_short').'.'.$month, $locale);
    }

    /** @param  int  $isoWeekday  1 = Monday … 7 = Sunday */
    public function weekdayName(int $isoWeekday, bool $short = false, ?string $locale = null): string
    {
        if ($isoWeekday < 1 || $isoWeekday > 7) {
            throw new InvalidArgumentException("Invalid ISO weekday {$isoWeekday}.");
        }

        return $this->line(($short ? 'weekdays_short.' : 'weekdays.').$isoWeekday, $locale);
    }

    /**
     * Every string the date picker needs, for one locale, keyed for JavaScript.
     *
     * @return array<string, mixed>
     */
    public function pickerStrings(?string $locale = null): array
    {
        $list = fn (string $key): array => array_values((array) $this->translator->get(self::KEY.$key, [], $locale));

        return [
            'months' => [
                'ethiopian' => $list('ethiopian_months'),
                'ethiopianShort' => $list('ethiopian_months_short'),
                'gregorian' => $list('gregorian_months'),
                'gregorianShort' => $list('gregorian_months_short'),
            ],
            'weekdaysMin' => $list('weekdays_min'),
            'weekdays' => $list('weekdays'),
            'format' => $this->line('formats.long', $locale),
            'era' => [
                'ethiopian' => $this->line('era.ethiopian', $locale),
                'gregorian' => $this->line('era.gregorian', $locale),
            ],
            'labels' => (array) $this->translator->get(self::KEY.'picker', [], $locale),
        ];
    }

    // ── Internals ───────────────────────────────────────────

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function parts(DateTimeInterface|string $date, Calendar $calendar): array
    {
        if ($calendar->isEthiopian()) {
            $e = EthiopicDate::fromGregorian($date);

            return [$e->year, $e->month, $e->day];
        }

        $g = Day::normalize($date);

        return [$g->year, $g->month, $g->day];
    }

    /**
     * @param  array<string, string>  $values
     */
    private function pattern(string $name, ?string $locale, array $values): string
    {
        return strtr($this->line('formats.'.$name, $locale), $values);
    }

    private function line(string $key, ?string $locale): string
    {
        $value = $this->translator->get(self::KEY.$key, [], $locale);

        return is_string($value) ? $value : $key;
    }
}
