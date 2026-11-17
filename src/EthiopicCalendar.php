<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar;

use Closure;

/**
 * Holds the application's current calendar choice.
 *
 * Bound as a singleton. The calendar comes from, in order:
 *   1. setCalendar() — e.g. from middleware that reads the user's settings
 *   2. a resolver registered with resolveCalendarUsing()
 *   3. config('ethiopic-calendar.calendar')
 */
final class EthiopicCalendar
{
    private ?Calendar $calendar = null;

    /** @var (Closure(): (Calendar|string|null))|null */
    private ?Closure $resolver = null;

    public function __construct(
        private readonly Calendar $default = Calendar::Ethiopian,
        private readonly int $weekStartsOn = 1,
    ) {}

    public function calendar(): Calendar
    {
        if ($this->calendar !== null) {
            return $this->calendar;
        }

        $resolved = $this->resolver !== null ? ($this->resolver)() : null;

        if (is_string($resolved)) {
            $resolved = Calendar::tryFrom($resolved);
        }

        return $resolved ?? $this->default;
    }

    public function setCalendar(Calendar|string $calendar): self
    {
        $this->calendar = is_string($calendar) ? Calendar::from($calendar) : $calendar;

        return $this;
    }

    /**
     * @param  Closure(): (Calendar|string|null)  $resolver
     */
    public function resolveCalendarUsing(Closure $resolver): self
    {
        $this->resolver = $resolver;
        $this->calendar = null;

        return $this;
    }

    /** ISO weekday the picker grid starts on: 1 = Monday … 7 = Sunday. */
    public function weekStartsOn(): int
    {
        return $this->weekStartsOn;
    }

    public function fiscalYear(\DateTimeInterface|string|null $date = null): FiscalYear
    {
        return $date === null
            ? FiscalYear::current($this->calendar())
            : FiscalYear::containing($date, $this->calendar());
    }

    public function billingMonth(\DateTimeInterface|string|null $date = null): BillingMonth
    {
        return $date === null
            ? BillingMonth::current($this->calendar())
            : BillingMonth::containing($date, $this->calendar());
    }
}
