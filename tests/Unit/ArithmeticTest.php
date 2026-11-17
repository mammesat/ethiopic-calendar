<?php

declare(strict_types=1);

use Mammesat\EthiopicCalendar\Arithmetic;
use Mammesat\EthiopicCalendar\Calendar;
use Mammesat\EthiopicCalendar\EthiopicDate;

/**
 * Monthly periods for one term, built the way the billing engine will:
 * every start computed from the lease start with the anchor day, never chained.
 *
 * @return list<array{0: string, 1: string}> [start, end] as Ethiopian Y-m-d
 */
function ethiopianMonthlyPeriods(string $startEthiopian, int $years = 1): array
{
    $start = EthiopicDate::parse($startEthiopian);
    $anchor = $start->anchorDay();
    $count = 12 * $years;

    $starts = [$start];
    for ($k = 1; $k <= $count; $k++) {
        $starts[] = $start->addMonths($k, $anchor);
    }

    $periods = [];
    for ($k = 0; $k < $count; $k++) {
        $periods[] = [$starts[$k]->toDateString(), $starts[$k + 1]->subDays(1)->toDateString()];
    }

    return $periods;
}

describe('Ethiopian month addition', function () {
    it('keeps the anchor day', function () {
        $d = EthiopicDate::of(2019, 1, 15);

        expect((string) $d->addMonths(1))->toBe('2019-02-15')
            ->and((string) $d->addMonths(11))->toBe('2019-12-15')
            ->and((string) $d->addMonths(12))->toBe('2020-01-15')
            ->and((string) $d->addMonths(-1))->toBe('2018-12-15')
            ->and((string) $d->addMonths(-13))->toBe('2017-12-15');
    });

    it('never lands in Pagume: Nehase is followed by Meskerem', function () {
        expect((string) EthiopicDate::of(2018, 12, 1)->addMonths(1))->toBe('2019-01-01')
            ->and((string) EthiopicDate::of(2018, 12, 30)->addMonths(1))->toBe('2019-01-30');
    });

    it('treats a Pagume date as 30 Nehase', function () {
        $pagume3 = EthiopicDate::of(2018, 13, 3);

        expect($pagume3->anchorDay())->toBe(30)
            ->and((string) $pagume3->addMonths(0))->toBe('2018-12-30')
            ->and((string) $pagume3->addMonths(1))->toBe('2019-01-30')
            ->and((string) $pagume3->addYears(1))->toBe('2019-12-30');
    });

    it('accepts an explicit anchor day', function () {
        expect((string) EthiopicDate::of(2019, 3, 1)->addMonths(2, 30))->toBe('2019-05-30');
    });

    it('rejects an anchor day outside 1–30', function (int $anchor) {
        EthiopicDate::of(2019, 1, 1)->addMonths(1, $anchor);
    })->with([0, 31])->throws(InvalidArgumentException::class);
});

describe('Gregorian month addition', function () {
    it('falls back to the last day and returns to the anchor day', function () {
        $jan31 = '2026-01-31';

        expect(Arithmetic::addMonths($jan31, 1, Calendar::Gregorian)->toDateString())->toBe('2026-02-28')
            ->and(Arithmetic::addMonths($jan31, 2, Calendar::Gregorian)->toDateString())->toBe('2026-03-31')
            ->and(Arithmetic::addMonths($jan31, 3, Calendar::Gregorian)->toDateString())->toBe('2026-04-30')
            ->and(Arithmetic::addMonths('2028-01-31', 1, Calendar::Gregorian)->toDateString())->toBe('2028-02-29');
    });

    it('uses the explicit anchor when stepping from a shortened date', function () {
        expect(Arithmetic::addMonths('2026-02-28', 1, Calendar::Gregorian, 31)->toDateString())->toBe('2026-03-31');
    });

    it('crosses year boundaries both ways', function () {
        expect(Arithmetic::addMonths('2026-11-15', 3, Calendar::Gregorian)->toDateString())->toBe('2027-02-15')
            ->and(Arithmetic::addMonths('2026-02-15', -3, Calendar::Gregorian)->toDateString())->toBe('2025-11-15');
    });

    it('handles 29 February anniversaries', function () {
        expect(Arithmetic::addYears('2024-02-29', 1, Calendar::Gregorian)->toDateString())->toBe('2025-02-28')
            ->and(Arithmetic::addYears('2024-02-29', 4, Calendar::Gregorian)->toDateString())->toBe('2028-02-29');
    });
});

describe('Ethiopian addMonths through Arithmetic', function () {
    it('works on gregorian input and output', function () {
        // 1 Meskerem 2019 = 2026-09-11 → 1 Tikimt 2019
        expect(Arithmetic::addMonths('2026-09-11', 1, Calendar::Ethiopian)->toDateString())
            ->toBe(eth('2019-02-01'));
    });

    it('returns the anchor day per calendar', function () {
        expect(Arithmetic::anchorDay(eth('2018-13-03'), Calendar::Ethiopian))->toBe(30)
            ->and(Arithmetic::anchorDay('2026-01-31', Calendar::Gregorian))->toBe(31);
    });
});

describe('End date (blueprint §7.3)', function () {
    it('ends on the last day of Pagume for a term starting 1 Meskerem', function () {
        // §7.3 example: start 1 Meskerem 2019, 2 years → last day of Pagume 2020.
        expect(Arithmetic::endDateFor(eth('2019-01-01'), 2, Calendar::Ethiopian)->toDateString())
            ->toBe(eth('2020-13-05'))
            ->and(Arithmetic::endDateFor(eth('2019-01-01'), 1, Calendar::Ethiopian)->toDateString())
            ->toBe(eth('2019-13-06'));
    });

    it('ends on 29 Nehase for a term starting in Pagume', function () {
        expect(Arithmetic::endDateFor(eth('2018-13-03'), 1, Calendar::Ethiopian)->toDateString())
            ->toBe(eth('2019-12-29'));
    });

    it('ends the day before the anniversary in the gregorian calendar', function () {
        expect(Arithmetic::endDateFor('2026-01-01', 1, Calendar::Gregorian)->toDateString())->toBe('2026-12-31')
            ->and(Arithmetic::endDateFor('2026-03-15', 5, Calendar::Gregorian)->toDateString())->toBe('2031-03-14')
            ->and(Arithmetic::endDateFor('2024-02-29', 1, Calendar::Gregorian)->toDateString())->toBe('2025-02-27');
    });

    it('rejects durations below one year', function () {
        Arithmetic::endDateFor('2026-01-01', 0, Calendar::Gregorian);
    })->throws(InvalidArgumentException::class);
});

describe('Monthly periods of §7.2', function () {
    it('from 1 Meskerem: Nehase runs to the last day of Pagume', function () {
        $periods = ethiopianMonthlyPeriods('2019-01-01');

        expect($periods)->toHaveCount(12)
            ->and($periods[0])->toBe(['2019-01-01', '2019-01-30'])
            ->and($periods[10])->toBe(['2019-11-01', '2019-11-30'])
            ->and($periods[11])->toBe(['2019-12-01', '2019-13-06']);
    });

    it('from 15 Nehase: the Pagume period runs 15 Nehase – 14 Meskerem', function () {
        $periods = ethiopianMonthlyPeriods('2018-12-15');

        expect($periods[0])->toBe(['2018-12-15', '2019-01-14'])
            ->and($periods[1])->toBe(['2019-01-15', '2019-02-14'])
            ->and($periods[11])->toBe(['2019-11-15', '2019-12-14']);
    });

    it('from 30 Tir: the period from 30 Nehase includes Pagume', function () {
        $periods = ethiopianMonthlyPeriods('2018-05-30');

        expect($periods[0])->toBe(['2018-05-30', '2018-06-29'])
            ->and($periods[7])->toBe(['2018-12-30', '2019-01-29'])
            ->and($periods[11])->toBe(['2019-04-30', '2019-05-29']);
    });

    it('from Pagume 3: first period to 29 Meskerem, then the 30th of each month', function () {
        $periods = ethiopianMonthlyPeriods('2018-13-03');

        expect($periods[0])->toBe(['2018-13-03', '2019-01-29'])
            ->and($periods[1])->toBe(['2019-01-30', '2019-02-29'])
            ->and($periods[11])->toBe(['2019-11-30', '2019-12-29']);
    });

    it('the period containing Pagume is 35 or 36 days', function () {
        $common = ethiopianMonthlyPeriods('2018-01-01')[11];   // 2018: Pagume 5
        $leap = ethiopianMonthlyPeriods('2019-01-01')[11];     // 2019: Pagume 6

        $days = fn (array $p): int => EthiopicDate::parse($p[0])->diffInDays(EthiopicDate::parse($p[1])) + 1;

        expect($days($common))->toBe(35)->and($days($leap))->toBe(36);
    });

    it('has no gap or overlap, and the last period ends on the end date', function (string $start, int $years) {
        $periods = ethiopianMonthlyPeriods($start, $years);

        foreach ($periods as $i => [$from, $to]) {
            expect(EthiopicDate::parse($to)->isBefore(EthiopicDate::parse($from)))->toBeFalse();

            if ($i > 0) {
                expect(EthiopicDate::parse($periods[$i - 1][1])->addDays(1)->toDateString())->toBe($from);
            }
        }

        $end = Arithmetic::endDateFor(eth($start), $years, Calendar::Ethiopian);

        expect(eth(end($periods)[1]))->toBe($end->toDateString());
    })->with([
        ['2019-01-01', 1],
        ['2019-01-01', 10],
        ['2018-12-15', 3],
        ['2018-05-30', 2],
        ['2018-13-03', 5],
        ['2015-13-06', 4],
    ]);
});

describe('Day arithmetic', function () {
    it('adds days across Pagume and the new year', function () {
        expect((string) EthiopicDate::of(2015, 13, 6)->addDays(1))->toBe('2016-01-01')
            ->and((string) EthiopicDate::of(2016, 1, 1)->subDays(1))->toBe('2015-13-06')
            ->and(Arithmetic::addDays('2026-02-28', 1)->toDateString())->toBe('2026-03-01');
    });

    it('measures signed differences', function () {
        expect(Arithmetic::diffInDays('2026-01-01', '2026-12-31'))->toBe(364)
            ->and(Arithmetic::diffInDays('2026-12-31', '2026-01-01'))->toBe(-364)
            ->and(EthiopicDate::of(2019, 1, 1)->diffInDays(EthiopicDate::of(2020, 1, 1)))->toBe(366);
    });

    it('ignores time of day and timezone of inputs', function () {
        $late = new DateTimeImmutable('2026-09-11 23:59:59', new DateTimeZone('America/New_York'));

        expect(EthiopicDate::fromGregorian($late)->toDateString())->toBe('2019-01-01')
            ->and(Arithmetic::dayOfWeek($late))->toBe(5);
    });
});
