<?php

declare(strict_types=1);

use Mammesat\EthiopicCalendar\EthiopianTime;

it('shifts the clock by six hours', function (int $hour, int $minute, int $ethHour, string $period) {
    $t = EthiopianTime::fromGregorian($hour, $minute);

    expect([$t->hour, $t->period, $t->clock()])->toBe([$ethHour, $period, sprintf('%d:%02d', $ethHour, $minute)]);
})->with([
    [0, 15, 6, 'night'],
    [5, 59, 11, 'night'],
    [6, 0, 12, 'morning'],
    [7, 30, 1, 'morning'],
    [11, 59, 5, 'morning'],
    [12, 0, 6, 'afternoon'],
    [17, 45, 11, 'afternoon'],
    [18, 0, 12, 'evening'],
    [23, 59, 5, 'evening'],
]);

it('round-trips every minute of the day', function () {
    for ($h = 0; $h < 24; $h++) {
        for ($m = 0; $m < 60; $m++) {
            $t = EthiopianTime::fromGregorian($h, $m);

            expect(EthiopianTime::of($t->hour, $t->minute, $t->period)->toGregorian())
                ->toBe(['hour' => $h, 'minute' => $m]);
        }
    }
});

it('parses H:i and H:i:s', function () {
    expect(EthiopianTime::parse('14:05')?->clock())->toBe('8:05')
        ->and(EthiopianTime::parse('09:00:00')?->period)->toBe('morning')
        ->and(EthiopianTime::parse('24:00'))->toBeNull()
        ->and(EthiopianTime::parse('noon'))->toBeNull();
});

it('rejects an hour outside its period', function (int $hour, string $period) {
    EthiopianTime::of($hour, 0, $period);
})->with([
    [8, 'morning'],
    [3, 'night'],
    [12, 'afternoon'],
    [7, 'evening'],
    [13, 'morning'],
    [1, 'dawn'],
])->throws(InvalidArgumentException::class);
