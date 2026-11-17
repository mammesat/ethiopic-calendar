<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Mammesat\EthiopicCalendar\Calendar;
use Mammesat\EthiopicCalendar\Casts\EthiopicDateCast;
use Mammesat\EthiopicCalendar\EthiopicCalendar;
use Mammesat\EthiopicCalendar\EthiopicDate;

final class CastTestModel extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'start_date' => EthiopicDateCast::class,
            'paid_on_ethiopic' => EthiopicDateCast::class.':paid_on',
        ];
    }
}

describe('Calendar setting', function () {
    it('defaults to the configured calendar', function () {
        expect(app(EthiopicCalendar::class)->calendar())->toBe(Calendar::Ethiopian);
    });

    it('reads the configuration', function () {
        config()->set('ethiopic-calendar.calendar', 'gregorian');
        app()->forgetInstance(EthiopicCalendar::class);

        expect(app(EthiopicCalendar::class)->calendar())->toBe(Calendar::Gregorian);
    });

    it('prefers an explicit setting, then a resolver', function () {
        $calendars = app(EthiopicCalendar::class);

        $calendars->resolveCalendarUsing(fn () => 'gregorian');
        expect($calendars->calendar())->toBe(Calendar::Gregorian);

        $calendars->setCalendar(Calendar::Ethiopian);
        expect($calendars->calendar())->toBe(Calendar::Ethiopian);
    });

    it('gives the fiscal year and billing month in the app calendar', function () {
        $calendars = app(EthiopicCalendar::class);

        expect($calendars->fiscalYear(eth('2018-11-01'))->year)->toBe(2019)
            ->and($calendars->billingMonth(eth('2018-13-02'))->month)->toBe(12);
    });
});

describe('EthiopicDateCast', function () {
    it('stores Gregorian and reads Ethiopian', function () {
        $model = new CastTestModel(['start_date' => EthiopicDate::of(2019, 1, 1)]);

        expect($model->getAttributes()['start_date'])->toBe('2026-09-11')
            ->and($model->start_date)->toBeInstanceOf(EthiopicDate::class)
            ->and((string) $model->start_date)->toBe('2019-01-01');
    });

    it('accepts Gregorian strings, DateTime and null', function () {
        $model = new CastTestModel;

        $model->start_date = '2023-09-11';
        expect((string) $model->start_date)->toBe('2015-13-06');

        $model->start_date = CarbonImmutable::create(2026, 9, 11, 23, 30);
        expect($model->getAttributes()['start_date'])->toBe('2026-09-11');

        $model->start_date = null;
        expect($model->start_date)->toBeNull();
    });

    it('reads datetime strings stored by the driver', function () {
        $model = (new CastTestModel)->setRawAttributes(['start_date' => '2026-09-11 00:00:00']);

        expect((string) $model->start_date)->toBe('2019-01-01');
    });

    it('exposes a read-only Ethiopian companion of another column', function () {
        $model = (new CastTestModel)->setRawAttributes(['paid_on' => '2026-09-12']);

        expect((string) $model->paid_on_ethiopic)->toBe('2019-01-02');

        $model->paid_on_ethiopic = '2026-09-13';
    })->throws(InvalidArgumentException::class);
});
