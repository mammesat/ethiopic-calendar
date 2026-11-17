<?php

declare(strict_types=1);

use Mammesat\EthiopicCalendar\Converter;

// Cases carried over from mammesat/filament-ethiopic-calendar (EthiopicCalendarServiceTest).

it('converts gregorian to ethiopian', function () {
    expect(Converter::toEthiopic(2023, 9, 12))->toBe(['year' => 2016, 'month' => 1, 'day' => 1]);
});

it('converts ethiopian to gregorian', function () {
    expect(Converter::toGregorian(2017, 1, 1))->toBe(['year' => 2024, 'month' => 9, 'day' => 11]);
});

it('matches the known dates of the implementation plan', function () {
    expect(Converter::toEthiopic(2026, 9, 11))->toBe(['year' => 2019, 'month' => 1, 'day' => 1])
        ->and(Converter::toEthiopic(2023, 9, 11))->toBe(['year' => 2015, 'month' => 13, 'day' => 6]);
});

it('handles pagume in common and leap years', function () {
    expect(Converter::toGregorian(2016, 13, 5))->toBe(['year' => 2024, 'month' => 9, 'day' => 10])
        ->and(Converter::isValidEthiopic(2016, 13, 6))->toBeFalse()
        ->and(Converter::toGregorian(2015, 13, 6))->toBe(['year' => 2023, 'month' => 9, 'day' => 11]);
});

it('knows the new year boundaries', function (string $gregorian, string $ethiopian) {
    expect(greg($gregorian))->toBe($ethiopian)
        ->and(eth($ethiopian))->toBe($gregorian);
})->with([
    ['2023-09-11', '2015-13-06'],
    ['2023-09-12', '2016-01-01'],
    ['2024-09-10', '2016-13-05'],
    ['2024-09-11', '2017-01-01'],
    ['2026-09-06', '2018-13-01'],
    ['2026-09-10', '2018-13-05'],
    ['2019-09-11', '2011-13-06'],
]);

it('round-trips century boundaries and gregorian leap days', function (string $gregorian) {
    expect(eth(greg($gregorian)))->toBe($gregorian);
})->with(['1900-03-01', '2000-02-29', '2020-02-29', '2100-01-01', '2100-02-28', '2100-03-01']);

it('counts days in each month', function () {
    expect(Converter::daysInMonth(2017, 1))->toBe(30)
        ->and(Converter::daysInMonth(2016, 13))->toBe(5)
        ->and(Converter::daysInMonth(2015, 13))->toBe(6)
        ->and(Converter::daysInYear(2015))->toBe(366)
        ->and(Converter::daysInYear(2016))->toBe(365);
});

it('treats year mod 4 = 3 as leap', function () {
    expect(Converter::isLeapYear(2011))->toBeTrue()
        ->and(Converter::isLeapYear(2015))->toBeTrue()
        ->and(Converter::isLeapYear(2019))->toBeTrue()
        ->and(Converter::isLeapYear(2016))->toBeFalse()
        ->and(Converter::isLeapYear(2018))->toBeFalse()
        ->and(Converter::isLeapYear(2020))->toBeFalse();
});

it('rejects invalid months', function (int $month) {
    Converter::daysInMonth(2017, $month);
})->with([0, 14])->throws(InvalidArgumentException::class);

it('rejects invalid gregorian dates', function () {
    Converter::toEthiopic(2024, 2, 30);
})->throws(InvalidArgumentException::class);

it('rejects invalid ethiopian dates', function (int $y, int $m, int $d) {
    Converter::toGregorian($y, $m, $d);
})->with([
    [2016, 13, 6],
    [2017, 13, 7],
    [2017, 1, 31],
    [2017, 0, 1],
    [2017, 1, 0],
    [0, 1, 1],
])->throws(InvalidArgumentException::class);

it('rejects dates before the ethiopian epoch', function () {
    Converter::jdnToEthiopic(Converter::EPOCH_JDN - 1);
})->throws(InvalidArgumentException::class);

it('exposes the epoch as 1 Meskerem 1', function () {
    expect(Converter::ethiopicToJdn(1, 1, 1))->toBe(Converter::EPOCH_JDN)
        ->and(Converter::jdnToEthiopic(Converter::EPOCH_JDN))->toBe(['year' => 1, 'month' => 1, 'day' => 1]);
});

it('returns ISO weekdays from the JDN', function () {
    // 11 Sep 2026 is a Friday.
    expect(Converter::dayOfWeek(Converter::gregorianToJdn(2026, 9, 11)))->toBe(5)
        // 1 Jan 2000 was a Saturday.
        ->and(Converter::dayOfWeek(Converter::gregorianToJdn(2000, 1, 1)))->toBe(6);
});
