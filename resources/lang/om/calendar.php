<?php

// DRAFT — every string in this file must be reviewed by a native
// Afaan Oromo speaker before release (implementation plan, milestone 1).
//
// Afaan Oromo commonly uses the same month names for both calendars,
// matched by season (Fulbaana ≈ Meskerem ≈ September). To keep the two
// apart, Oromo names are used for Ethiopian dates only; Gregorian dates
// are written in Latin letters, English style: "Sep 25, 2026".

return [
    'ethiopian_months' => [
        1 => 'Fulbaana', 'Onkoloolessa', 'Sadaasa', 'Muddee', 'Amajjii', 'Guraandhala', 'Bitootessa',
        'Eebila', 'Caamsaa', 'Waxabajjii', 'Adoolessa', 'Hagayya', 'Qaammee',
    ],

    'ethiopian_months_short' => [
        1 => 'Ful', 'Onk', 'Sad', 'Mud', 'Ama', 'Gur', 'Bit',
        'Eeb', 'Caa', 'Wax', 'Ado', 'Hag', 'Qaa',
    ],

    'gregorian_months' => [
        1 => 'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ],

    'gregorian_months_short' => [
        1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
    ],

    'weekdays' => [
        1 => 'Wiixata', 'Kibxata', 'Roobii', 'Kamiisa', 'Jimaata', 'Sanbata', 'Dilbata',
    ],

    'weekdays_short' => [
        1 => 'Wii', 'Kib', 'Roo', 'Kam', 'Jim', 'San', 'Dil',
    ],

    'weekdays_min' => [
        1 => 'Wi', 'Ki', 'Ro', 'Ka', 'Ji', 'Sa', 'Di',
    ],

    'day_periods' => [
        'night' => 'halkan',
        'morning' => 'ganama',
        'afternoon' => 'waaree booda',
        'evening' => 'galgala',
    ],

    'era' => [
        'ethiopian' => 'A.L.I.',
        'gregorian' => '', // Gregorian dates carry no era marker
    ],

    'formats' => [
        'long' => ':month :day, :year',
        'medium' => ':month :day, :year',
        'short' => ':dd/:mm/:year',
        'month' => ':month :year',
        'weekday' => ':weekday, :date',
        'with_era' => ':date :era',
        'secondary' => ':date (:secondary)',
        'time' => ':time :period',
        'datetime' => ':date, :time',
    ],

    // Gregorian dates use these patterns instead of 'formats' (long, medium, short, month).
    'gregorian_formats' => [
        'long' => ':month :day, :year',
        'medium' => ':month :day, :year',
        'short' => ':dd/:mm/:year',
        'month' => ':month :year',
    ],

    'fiscal_year' => 'Bara baajataa :year',

    'picker' => [
        'placeholder' => 'Guyyaa filadhu',
        'today' => "Har'a",
        'clear' => 'Haqi',
        'close' => 'Cufi',
        'previous_month' => "Ji'a darbe",
        'next_month' => "Ji'a itti aanu",
        'previous_years' => 'Waggoota darban',
        'next_years' => 'Waggoota itti aanan',
        'choose_month' => "Ji'a filadhu",
        'choose_year' => 'Waggaa filadhu',
        'ethiopian' => 'Itoophiyaa',
        'gregorian' => 'Gorgooriyaa',
    ],
];
