<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar;

/**
 * The calendar a date is displayed, entered and billed in.
 * Dates are always stored as Gregorian Y-m-d, whatever the calendar.
 */
enum Calendar: string
{
    case Ethiopian = 'ethiopian';
    case Gregorian = 'gregorian';

    public function isEthiopian(): bool
    {
        return $this === self::Ethiopian;
    }
}
