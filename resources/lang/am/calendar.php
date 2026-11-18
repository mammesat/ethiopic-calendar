<?php

// Month, weekday and day-period names carried over from
// mammesat/filament-ethiopic-calendar. Other strings: to be reviewed by a
// native speaker.

return [
    'ethiopian_months' => [
        1 => 'መስከረም', 'ጥቅምት', 'ኅዳር', 'ታኅሣሥ', 'ጥር', 'የካቲት', 'መጋቢት',
        'ሚያዝያ', 'ግንቦት', 'ሰኔ', 'ሐምሌ', 'ነሐሴ', 'ጳጉሜ',
    ],

    'ethiopian_months_short' => [
        1 => 'መስከ', 'ጥቅም', 'ኅዳር', 'ታኅሣ', 'ጥር', 'የካቲ', 'መጋቢ',
        'ሚያዝ', 'ግንቦ', 'ሰኔ', 'ሐምሌ', 'ነሐሴ', 'ጳጉሜ',
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
        1 => 'ሰኞ', 'ማክሰኞ', 'ረቡዕ', 'ሐሙስ', 'ዓርብ', 'ቅዳሜ', 'እሑድ',
    ],

    'weekdays_short' => [
        1 => 'ሰኞ', 'ማክ', 'ረቡ', 'ሐሙ', 'ዓር', 'ቅዳ', 'እሑ',
    ],

    'weekdays_min' => [
        1 => 'ሰ', 'ማ', 'ረ', 'ሐ', 'ዓ', 'ቅ', 'እ',
    ],

    'day_periods' => [
        'night' => 'ለሊት',
        'morning' => 'ጠዋት',
        'afternoon' => 'ከሰዓት',
        'evening' => 'ማታ',
    ],

    'era' => [
        'ethiopian' => 'ዓ.ም.',
        'gregorian' => '', // Gregorian dates carry no era marker
    ],

    'formats' => [
        'long' => ':month :day :year',
        'medium' => ':month :day, :year',
        'short' => ':dd/:mm/:year',
        'month' => ':month :year',
        'weekday' => ':weekday፣ :date',
        'with_era' => ':date :era',
        'secondary' => ':date (:secondary)',
        'time' => ':period :time',
        'datetime' => ':date፣ :time',
    ],

    // Gregorian dates use these patterns instead of 'formats' (long, medium, short, month).
    'gregorian_formats' => [
        'long' => ':month :day, :year',
        'medium' => ':month :day, :year',
        'short' => ':dd/:mm/:year',
        'month' => ':month :year',
    ],

    'fiscal_year' => 'በጀት ዓመት :year',

    'picker' => [
        'placeholder' => 'ቀን ይምረጡ',
        'today' => 'ዛሬ',
        'clear' => 'አጽዳ',
        'close' => 'ዝጋ',
        'previous_month' => 'ያለፈው ወር',
        'next_month' => 'የሚቀጥለው ወር',
        'previous_years' => 'ቀደምት ዓመታት',
        'next_years' => 'ቀጣይ ዓመታት',
        'choose_month' => 'ወር ይምረጡ',
        'choose_year' => 'ዓመት ይምረጡ',
        'ethiopian' => 'ኢትዮጵያ',
        'gregorian' => 'ግሪጎሪያን',
    ],
];
