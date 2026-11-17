<?php

declare(strict_types=1);

use Mammesat\EthiopicCalendar\Converter;
use Mammesat\EthiopicCalendar\EthiopicDate;

it('covers every day of Ethiopian years 1990 to 2060', function () {
    $rows = fixtureRows();

    expect($rows[0][1])->toBe('1990-01-01')
        ->and(end($rows)[1])->toBe('2060-13-05')
        ->and(count($rows))->toBe(end($rows)[2] - $rows[0][2] + 1);
});

it('matches the converter for every fixture date', function () {
    $failures = [];

    foreach (fixtureRows() as [$gregorian, $ethiopian, $jdn, $weekday]) {
        [$gy, $gm, $gd] = array_map(intval(...), explode('-', $gregorian));
        [$ey, $em, $ed] = array_map(intval(...), explode('-', $ethiopian));

        $ok = Converter::gregorianToJdn($gy, $gm, $gd) === $jdn
            && Converter::ethiopicToJdn($ey, $em, $ed) === $jdn
            && Converter::jdnToEthiopic($jdn) === ['year' => $ey, 'month' => $em, 'day' => $ed]
            && Converter::jdnToGregorian($jdn) === ['year' => $gy, 'month' => $gm, 'day' => $gd]
            && Converter::dayOfWeek($jdn) === $weekday;

        if (! $ok) {
            $failures[] = "{$gregorian} / {$ethiopian}";
        }
    }

    expect($failures)->toBe([]);
});

it('matches EthiopicDate for every fixture date', function () {
    $failures = [];

    foreach (fixtureRows() as [$gregorian, $ethiopian, $jdn, $weekday]) {
        $date = EthiopicDate::fromGregorian($gregorian);

        if ($date->toDateString() !== $ethiopian
            || $date->toGregorianString() !== $gregorian
            || $date->dayOfWeek() !== $weekday
            || EthiopicDate::parse($ethiopian)->toJdn() !== $jdn) {
            $failures[] = "{$gregorian} / {$ethiopian}";
        }
    }

    expect($failures)->toBe([]);
});

it('includes every Pagume 6 of the leap years', function () {
    $pagume6 = array_values(array_filter(fixtureRows(), static fn (array $r): bool => str_ends_with($r[1], '-13-06')));

    $years = array_map(static fn (array $r): int => (int) substr($r[1], 0, 4), $pagume6);

    expect($years)->toBe(range(1991, 2059, 4));
});
