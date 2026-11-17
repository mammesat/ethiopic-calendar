<?php

return [
    'ethiopian_months' => [
        1 => 'Meskerem', 'Tikimt', 'Hidar', 'Tahsas', 'Tir', 'Yekatit', 'Megabit',
        'Miyazya', 'Ginbot', 'Sene', 'Hamle', 'Nehase', 'Pagume',
    ],

    'ethiopian_months_short' => [
        1 => 'Mes', 'Tik', 'Hid', 'Tah', 'Tir', 'Yek', 'Meg',
        'Miy', 'Gin', 'Sen', 'Ham', 'Neh', 'Pag',
    ],

    'gregorian_months' => [
        1 => 'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ],

    'gregorian_months_short' => [
        1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
    ],

    // ISO order: 1 = Monday … 7 = Sunday
    'weekdays' => [
        1 => 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday',
    ],

    'weekdays_short' => [
        1 => 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun',
    ],

    // Column headers of the picker grid
    'weekdays_min' => [
        1 => 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su',
    ],

    'day_periods' => [
        'night' => 'at night',
        'morning' => 'in the morning',
        'afternoon' => 'in the afternoon',
        'evening' => 'in the evening',
    ],

    'era' => [
        'ethiopian' => 'E.C.',
        'gregorian' => 'G.C.',
    ],

    // :day, :month, :year, :weekday, :era. Numeric format uses :dd and :mm.
    'formats' => [
        'long' => ':day :month :year',
        'medium' => ':day :month :year',
        'short' => ':dd/:mm/:year',
        'month' => ':month :year',
        'weekday' => ':weekday, :date',
        'with_era' => ':date :era',
        'secondary' => ':date (:secondary)',
        'time' => ':time :period',
        'datetime' => ':date, :time',
    ],

    'fiscal_year' => 'FY :year',

    'picker' => [
        'placeholder' => 'Select a date',
        'today' => 'Today',
        'clear' => 'Clear',
        'close' => 'Close',
        'previous_month' => 'Previous month',
        'next_month' => 'Next month',
        'previous_years' => 'Earlier years',
        'next_years' => 'Later years',
        'choose_month' => 'Choose month',
        'choose_year' => 'Choose year',
        'ethiopian' => 'Ethiopian',
        'gregorian' => 'Gregorian',
    ],
];
