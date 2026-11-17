<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar\View\Components;

use DateTimeInterface;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Mammesat\EthiopicCalendar\Calendar;
use Mammesat\EthiopicCalendar\Day;
use Mammesat\EthiopicCalendar\EthiopicDate;
use Mammesat\EthiopicCalendar\Formatter;

/**
 * <x-ethiopic::date :value="$lease->start_date" />
 * <x-ethiopic::date :value="$invoice->due_date" format="long" weekday secondary />
 *
 * Renders <time datetime="Y-m-d">…</time>, or the placeholder for an empty value.
 */
final class Date extends Component
{
    public readonly ?string $text;

    public readonly ?string $iso;

    public function __construct(
        Formatter $formatter,
        DateTimeInterface|EthiopicDate|string|null $value = null,
        public readonly string $format = 'medium',
        Calendar|string|null $calendar = null,
        bool $weekday = false,
        bool $era = false,
        bool $secondary = false,
        public readonly string $placeholder = '—',
    ) {
        $gregorian = $value instanceof EthiopicDate ? $value->toGregorian() : $value;
        $calendar = is_string($calendar) ? Calendar::from($calendar) : $calendar;

        $this->iso = $gregorian === null || $gregorian === '' ? null : Day::normalize($gregorian)->toDateString();
        $this->text = $formatter->date($gregorian, $format, $calendar, null, $weekday, $era, $secondary);
    }

    public function render(): View
    {
        return app(ViewFactory::class)->make('ethiopic-calendar::components.date');
    }
}
