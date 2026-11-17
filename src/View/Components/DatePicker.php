<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar\View\Components;

use DateTimeInterface;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Mammesat\EthiopicCalendar\Calendar;
use Mammesat\EthiopicCalendar\Day;
use Mammesat\EthiopicCalendar\EthiopicCalendar;
use Mammesat\EthiopicCalendar\Formatter;

/**
 * <x-ethiopic::date-picker wire:model="start_date" />
 * <x-ethiopic::date-picker wire:model.live="paid_on" :max="today()" label="Payment date" />
 * <x-ethiopic::date-picker name="due_date" value="2026-09-11" />   (plain form)
 *
 * The bound value is always a Gregorian "Y-m-d" string, in any calendar.
 */
final class DatePicker extends Component
{
    /** @var array<string, mixed> */
    public readonly array $config;

    public readonly string $inputId;

    public function __construct(
        Formatter $formatter,
        EthiopicCalendar $calendars,
        DateTimeInterface|string|null $value = null,
        DateTimeInterface|string|null $min = null,
        DateTimeInterface|string|null $max = null,
        Calendar|string|null $calendar = null,
        public readonly ?string $name = null,
        public readonly ?string $label = null,
        public readonly ?string $placeholder = null,
        public readonly bool $clearable = true,
        public readonly bool $required = false,
        public readonly bool $disabled = false,
        ?string $id = null,
    ) {
        $calendar = is_string($calendar) ? Calendar::from($calendar) : ($calendar ?? $calendars->calendar());

        $this->inputId = $id ?? 'ethiopic-date-'.bin2hex(random_bytes(4));

        $this->config = [
            'value' => self::iso($value),
            'min' => self::iso($min),
            'max' => self::iso($max),
            'calendar' => $calendar->value,
            'weekStart' => $calendars->weekStartsOn(),
            'strings' => $formatter->pickerStrings(),
        ];
    }

    public function render(): View
    {
        return app(ViewFactory::class)->make('ethiopic-calendar::components.date-picker');
    }

    private static function iso(DateTimeInterface|string|null $value): ?string
    {
        return $value === null || $value === '' ? null : Day::normalize($value)->toDateString();
    }
}
