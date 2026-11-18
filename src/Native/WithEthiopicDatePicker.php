<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar\Native;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Mammesat\EthiopicCalendar\Calendar;
use Mammesat\EthiopicCalendar\Converter;
use Mammesat\EthiopicCalendar\Day;
use Mammesat\EthiopicCalendar\EthiopicCalendar;
use Mammesat\EthiopicCalendar\Formatter;
use Mammesat\EthiopicCalendar\MonthGrid;
use ReflectionProperty;

/**
 * Material-style date picker dialog for NativePHP Mobile screens
 * (NativeComponent), in the Ethiopian or Gregorian calendar.
 *
 * Usage in a screen:
 *
 *     class EditLease extends NativeComponent
 *     {
 *         use WithEthiopicDatePicker;
 *
 *         public ?string $startDate = null;   // always Gregorian Y-m-d
 *
 *         protected function datePickerOptions(string $field): array
 *         {
 *             return ['max' => today()->toDateString()];   // optional
 *         }
 *     }
 *
 * In the screen's view, make the root a `native:stack fill`, include the
 * `ethiopic-calendar::native.date-field` partial (with 'picker' => $this,
 * 'field' => 'startDate') for each date input, and include
 * `ethiopic-calendar::native.date-picker-dialog` (with 'picker' => $this) once
 * as the stack's last child. Included views don't get the screen as $this,
 * hence the explicit 'picker'. See the README for the full example.
 *
 * Tapping a day only highlights it; OK writes it to the field's property and
 * calls updated{Field}() when the screen defines it. The Android back button
 * closes an open dialog.
 */
trait WithEthiopicDatePicker
{
    /** Property name of the field being edited; null when the dialog is closed. */
    public ?string $datePickerField = null;

    /** "days" or "years". */
    public string $datePickerMode = 'days';

    /** Month on screen, in the picker's calendar (Pagume = 13). */
    public int $datePickerYear = 0;

    public int $datePickerMonth = 1;

    /** Highlighted, not yet confirmed, Gregorian Y-m-d. */
    public ?string $datePickerPending = null;

    /** First year shown in the year grid. */
    public int $datePickerYearPage = 0;

    /**
     * Per-field options. Override in the screen.
     *
     * @return array{min?: string|null, max?: string|null, calendar?: Calendar|string|null}
     */
    protected function datePickerOptions(string $field): array
    {
        return [];
    }

    // ── Actions (bound in the views) ────────────────────────

    public function openDatePicker(string $field): void
    {
        $this->assertDatePickerField($field);

        $value = $this->{$field};
        $this->datePickerField = $field;
        $this->datePickerPending = is_string($value) && Day::parse($value) !== null ? $value : null;
        $this->datePickerMode = 'days';

        [$this->datePickerYear, $this->datePickerMonth] = MonthGrid::monthOf(
            $this->clampJdn($this->datePickerPending !== null ? Day::toJdn($this->datePickerPending) : Day::toJdn(CarbonImmutable::today())),
            $this->datePickerCalendar(),
        );
    }

    public function cancelDatePicker(): void
    {
        $this->datePickerField = null;
        $this->datePickerPending = null;
    }

    public function confirmDatePicker(): void
    {
        $field = $this->datePickerField;

        if ($field === null) {
            return;
        }

        if ($this->datePickerPending !== null) {
            $this->{$field} = $this->datePickerPending;

            $hook = 'updated'.Str::studly($field);
            if (method_exists($this, $hook)) {
                $this->{$hook}($this->datePickerPending);
            }
        }

        $this->cancelDatePicker();
    }

    public function datePickerPickDay(int $jdn): void
    {
        if ($this->datePickerField === null || $this->isDatePickerJdnDisabled($jdn)) {
            return;
        }

        $this->datePickerPending = Day::fromJdn($jdn)->toDateString();
    }

    public function datePickerPreviousMonth(): void
    {
        if ($this->datePickerCanGo(-1)) {
            [$this->datePickerYear, $this->datePickerMonth] = MonthGrid::step($this->datePickerYear, $this->datePickerMonth, -1, $this->datePickerCalendar());
        }
    }

    public function datePickerNextMonth(): void
    {
        if ($this->datePickerCanGo(1)) {
            [$this->datePickerYear, $this->datePickerMonth] = MonthGrid::step($this->datePickerYear, $this->datePickerMonth, 1, $this->datePickerCalendar());
        }
    }

    public function datePickerToggleYears(): void
    {
        if ($this->datePickerMode === 'years') {
            $this->datePickerMode = 'days';

            return;
        }

        $this->datePickerMode = 'years';
        $this->datePickerYearPage = $this->datePickerYear - 7;
    }

    public function datePickerYearsPage(int $delta): void
    {
        $this->datePickerYearPage = max(1, $this->datePickerYearPage + 15 * $delta);
    }

    public function datePickerPickYear(int $year): void
    {
        if ($this->isDatePickerYearDisabled($year)) {
            return;
        }

        $this->datePickerYear = $year;
        $this->datePickerMonth = min($this->datePickerMonth, MonthGrid::monthsInYear($this->datePickerCalendar()));
        $this->datePickerMode = 'days';
    }

    /** Taps on the dialog card land here so they don't reach the backdrop. */
    public function datePickerNoop(): void {}

    /** The Android back button closes an open dialog before leaving the screen. */
    public function onBackPressed(): void
    {
        if ($this->datePickerField !== null) {
            $this->cancelDatePicker();

            return;
        }

        parent::onBackPressed();
    }

    // ── View data ───────────────────────────────────────────

    /** The field's value formatted for its trigger box, or null when empty. */
    public function datePickerLabel(string $field, string $format = 'medium'): ?string
    {
        $value = $this->{$field} ?? null;

        if (! is_string($value) || Day::parse($value) === null) {
            return null;
        }

        return app(Formatter::class)->date($value, $format, $this->datePickerCalendar($field));
    }

    /** The field's value in the other calendar, for an optional secondary line. */
    public function datePickerSecondaryLabel(string $field): ?string
    {
        $value = $this->{$field} ?? null;

        if (! is_string($value) || Day::parse($value) === null) {
            return null;
        }

        $other = $this->datePickerCalendar($field)->isEthiopian() ? Calendar::Gregorian : Calendar::Ethiopian;

        return app(Formatter::class)->date($value, 'medium', $other, era: true);
    }

    /**
     * Everything the dialog view renders.
     *
     * @return array<string, mixed>
     */
    public function datePickerState(): array
    {
        $open = $this->datePickerField !== null;
        $strings = (array) trans('ethiopic-calendar::calendar.picker');

        if (! $open) {
            return ['open' => false, 'strings' => $strings];
        }

        $calendar = $this->datePickerCalendar();
        $formatter = app(Formatter::class);
        $weekStart = app(EthiopicCalendar::class)->weekStartsOn();
        $today = CarbonImmutable::today()->toDateString();

        $weeks = array_map(fn (array $week): array => array_map(fn (?array $cell): ?array => $cell === null ? null : [
            ...$cell,
            'selected' => $cell['iso'] === $this->datePickerPending,
            'today' => $cell['iso'] === $today,
            'disabled' => $this->isDatePickerJdnDisabled($cell['jdn']),
        ], $week), MonthGrid::weeks($this->datePickerYear, $this->datePickerMonth, $calendar, $weekStart));

        $weekdays = array_map(
            fn (int $i): string => (string) trans('ethiopic-calendar::calendar.weekdays_min.'.((($weekStart - 1 + $i) % 7) + 1)),
            range(0, 6),
        );

        $years = array_map(fn (int $year): array => [
            'year' => $year,
            'selected' => $year === $this->datePickerYear,
            'disabled' => $this->isDatePickerYearDisabled($year),
        ], range($this->datePickerYearPage, $this->datePickerYearPage + 14));

        return [
            'open' => true,
            'mode' => $this->datePickerMode,
            'strings' => $strings,
            'headline' => $this->datePickerPending !== null
                ? $formatter->date($this->datePickerPending, 'medium', $calendar)
                : '—',
            'title' => $formatter->monthName($this->datePickerMonth, $calendar).' '.$this->datePickerYear,
            'gridKey' => $calendar->value.'-'.$this->datePickerYear.'-'.$this->datePickerMonth,
            'weekdays' => $weekdays,
            'weeks' => $weeks,
            'years' => array_chunk($years, 3),
            'canPrevious' => $this->datePickerCanGo(-1),
            'canNext' => $this->datePickerCanGo(1),
        ];
    }

    // ── Internals ───────────────────────────────────────────

    protected function datePickerCalendar(?string $field = null): Calendar
    {
        $calendar = $this->datePickerOptions($field ?? $this->datePickerField ?? '')['calendar'] ?? null;

        if (is_string($calendar)) {
            $calendar = Calendar::from($calendar);
        }

        return $calendar ?? app(EthiopicCalendar::class)->calendar();
    }

    /**
     * @return array{0: int|null, 1: int|null} [min JDN, max JDN]
     */
    private function datePickerBounds(): array
    {
        $options = $this->datePickerOptions($this->datePickerField ?? '');
        $min = $options['min'] ?? null;
        $max = $options['max'] ?? null;

        return [
            $min !== null ? Day::toJdn($min) : null,
            $max !== null ? Day::toJdn($max) : null,
        ];
    }

    private function isDatePickerJdnDisabled(int $jdn): bool
    {
        [$min, $max] = $this->datePickerBounds();

        return $jdn < Converter::MIN_JDN || ($min !== null && $jdn < $min) || ($max !== null && $jdn > $max);
    }

    private function isDatePickerYearDisabled(int $year): bool
    {
        if ($year < 1) {
            return true;
        }

        $calendar = $this->datePickerCalendar();
        [$min, $max] = $this->datePickerBounds();
        $last = MonthGrid::monthsInYear($calendar);

        return ($min !== null && MonthGrid::lastJdn($year, $last, $calendar) < $min)
            || ($max !== null && MonthGrid::firstJdn($year, 1, $calendar) > $max);
    }

    private function datePickerCanGo(int $delta): bool
    {
        $calendar = $this->datePickerCalendar();
        [$year, $month] = MonthGrid::step($this->datePickerYear, $this->datePickerMonth, $delta, $calendar);

        if ($year < 1) {
            return false;
        }

        [$min, $max] = $this->datePickerBounds();

        return $delta < 0
            ? $min === null || MonthGrid::lastJdn($year, $month, $calendar) >= $min
            : $max === null || MonthGrid::firstJdn($year, $month, $calendar) <= $max;
    }

    private function clampJdn(int $jdn): int
    {
        [$min, $max] = $this->datePickerBounds();

        if ($min !== null && $jdn < $min) {
            return $min;
        }

        return $max !== null && $jdn > $max ? $max : $jdn;
    }

    /**
     * Only a public, non-picker property of this screen may be edited, so a
     * forged callback cannot write arbitrary state.
     */
    private function assertDatePickerField(string $field): void
    {
        if (str_starts_with($field, 'datePicker')
            || ! property_exists($this, $field)
            || ! (new ReflectionProperty($this, $field))->isPublic()) {
            throw new InvalidArgumentException("\"{$field}\" is not a date field of this screen.");
        }
    }
}
