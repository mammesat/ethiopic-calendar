<?php

declare(strict_types=1);

use Mammesat\EthiopicCalendar\Calendar;
use Mammesat\EthiopicCalendar\MonthGrid;

$days = fn (array $weeks): array => array_values(array_filter(array_merge(...$weeks)));

it('lays out 1 Meskerem 2019 on a Friday, Monday first', function () use ($days) {
    $weeks = MonthGrid::weeks(2019, 1, Calendar::Ethiopian);

    expect($weeks[0])->toHaveCount(7)
        ->and(array_slice($weeks[0], 0, 4))->toBe([null, null, null, null])
        ->and($weeks[0][4])->toBe(['jdn' => 2461295, 'iso' => '2026-09-11', 'day' => 1])
        ->and($days($weeks))->toHaveCount(30);
});

it('shows Pagume as a short month', function () use ($days) {
    expect($days(MonthGrid::weeks(2018, 13, Calendar::Ethiopian)))->toHaveCount(5)
        ->and($days(MonthGrid::weeks(2019, 13, Calendar::Ethiopian)))->toHaveCount(6);
});

it('builds Gregorian months and honours a Sunday start', function () use ($days) {
    expect($days(MonthGrid::weeks(2028, 2, Calendar::Gregorian)))->toHaveCount(29)
        ->and(MonthGrid::weeks(2026, 2, Calendar::Gregorian, 7)[0][0]['day'])->toBe(1); // 1 Feb 2026 is a Sunday
});

it('steps Nehase → Pagume → Meskerem and wraps Gregorian years', function () {
    expect(MonthGrid::step(2018, 12, 1, Calendar::Ethiopian))->toBe([2018, 13])
        ->and(MonthGrid::step(2018, 13, 1, Calendar::Ethiopian))->toBe([2019, 1])
        ->and(MonthGrid::step(2019, 1, -1, Calendar::Ethiopian))->toBe([2018, 13])
        ->and(MonthGrid::step(2026, 12, 1, Calendar::Gregorian))->toBe([2027, 1]);
});

it('finds the month of a JDN and its first and last days', function () {
    expect(MonthGrid::monthOf(2461295, Calendar::Ethiopian))->toBe([2019, 1])
        ->and(MonthGrid::monthOf(2461295, Calendar::Gregorian))->toBe([2026, 9])
        ->and(MonthGrid::lastJdn(2019, 13, Calendar::Ethiopian) - MonthGrid::firstJdn(2019, 13, Calendar::Ethiopian))->toBe(5);
});

it('rejects month 13 in the Gregorian grid and bad week starts', function () {
    expect(fn () => MonthGrid::daysInMonth(2026, 13, Calendar::Gregorian))->toThrow(InvalidArgumentException::class)
        ->and(fn () => MonthGrid::weeks(2019, 1, Calendar::Ethiopian, 0))->toThrow(InvalidArgumentException::class);
});
