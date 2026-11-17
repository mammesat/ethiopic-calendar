<?php

return [
    /*
     * Default calendar for display, input and billing: "ethiopian" or "gregorian".
     * An application with a per-user setting overrides it at runtime:
     *
     *   app(EthiopicCalendar::class)->setCalendar($settings->calendar);
     */
    'calendar' => env('ETHIOPIC_CALENDAR', 'ethiopian'),

    /*
     * First column of the date picker grid, ISO weekday: 1 = Monday … 7 = Sunday.
     */
    'week_starts_on' => 1,
];
