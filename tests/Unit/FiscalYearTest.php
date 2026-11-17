<?php

declare(strict_types=1);

use Mammesat\EthiopicCalendar\BillingMonth;
use Mammesat\EthiopicCalendar\Calendar;
use Mammesat\EthiopicCalendar\FiscalYear;

describe('Ethiopian fiscal year', function () {
    it('places dates on both sides of 1 Hamle', function (string $ethiopian, int $fy) {
        expect(FiscalYear::containing(eth($ethiopian), Calendar::Ethiopian)->year)->toBe($fy);
    })->with([
        '30 Sene 2018' => ['2018-10-30', 2018],
        '1 Hamle 2018' => ['2018-11-01', 2019],
        '10 Nehase 2018' => ['2018-12-10', 2019],
        'Pagume 3 2018' => ['2018-13-03', 2019],
        '1 Meskerem 2019' => ['2019-01-01', 2019],
        '6 Hidar 2019' => ['2019-03-06', 2019],
        '30 Sene 2019' => ['2019-10-30', 2019],
    ]);

    it('runs from 1 Hamle to 30 Sene', function () {
        $fy = FiscalYear::of(2019, Calendar::Ethiopian);

        expect($fy->start->toDateString())->toBe(eth('2018-11-01'))
            ->and($fy->end->toDateString())->toBe(eth('2019-10-30'))
            ->and($fy->label())->toBe('19');
    });

    it('has twelve months, Hamle first and Nehase including Pagume', function () {
        $months = FiscalYear::of(2019, Calendar::Ethiopian)->months();

        expect($months)->toHaveCount(12)
            ->and([$months[0]->year, $months[0]->month])->toBe([2018, 11])
            ->and([$months[1]->year, $months[1]->month])->toBe([2018, 12])
            ->and($months[1]->end->toDateString())->toBe(eth('2018-13-05'))
            ->and([$months[2]->year, $months[2]->month])->toBe([2019, 1])
            ->and([$months[11]->year, $months[11]->month])->toBe([2019, 10]);
    });

    it('chains with no gap', function () {
        $fy = FiscalYear::of(2019, Calendar::Ethiopian);

        expect($fy->next()->start->toDateString())->toBe($fy->end->addDay()->toDateString())
            ->and($fy->previous()->year)->toBe(2018)
            ->and($fy->contains(eth('2018-13-05')))->toBeTrue()
            ->and($fy->contains(eth('2019-11-01')))->toBeFalse();
    });
});

describe('Gregorian fiscal year', function () {
    it('is the calendar year', function () {
        $fy = FiscalYear::containing('2026-07-15', Calendar::Gregorian);

        expect($fy->year)->toBe(2026)
            ->and($fy->start->toDateString())->toBe('2026-01-01')
            ->and($fy->end->toDateString())->toBe('2026-12-31')
            ->and($fy->label())->toBe('26')
            ->and($fy->months()[1]->end->toDateString())->toBe('2026-02-28');
    });

    it('pads labels to two digits', function () {
        expect(FiscalYear::of(2105, Calendar::Gregorian)->label())->toBe('05');
    });
});

describe('Billing month', function () {
    it('puts Pagume dates in Nehase', function () {
        $m = BillingMonth::containing(eth('2019-13-06'), Calendar::Ethiopian);

        expect([$m->year, $m->month])->toBe([2019, 12])
            ->and($m->start->toDateString())->toBe(eth('2019-12-01'))
            ->and($m->end->toDateString())->toBe(eth('2019-13-06'))
            ->and($m->lengthInDays())->toBe(36);
    });

    it('steps from Nehase to Meskerem of the next year', function () {
        $m = BillingMonth::of(2019, 12, Calendar::Ethiopian)->next();

        expect([$m->year, $m->month])->toBe([2020, 1])
            ->and($m->previous()->equals(BillingMonth::of(2019, 12, Calendar::Ethiopian)))->toBeTrue();
    });

    it('is the plain month in the gregorian calendar', function () {
        $m = BillingMonth::containing('2028-02-10', Calendar::Gregorian);

        expect($m->start->toDateString())->toBe('2028-02-01')
            ->and($m->end->toDateString())->toBe('2028-02-29')
            ->and($m->contains('2028-02-29'))->toBeTrue()
            ->and($m->contains('2028-03-01'))->toBeFalse();
    });

    it('rejects month 13', function () {
        BillingMonth::of(2019, 13, Calendar::Ethiopian);
    })->throws(InvalidArgumentException::class);
});
