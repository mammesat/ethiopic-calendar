<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Mammesat\EthiopicCalendar\BillingMonth;
use Mammesat\EthiopicCalendar\Calendar;
use Mammesat\EthiopicCalendar\EthiopicCalendar;
use Mammesat\EthiopicCalendar\FiscalYear;
use Mammesat\EthiopicCalendar\Formatter;

beforeEach(function () {
    $this->f = app(Formatter::class);
});

it('formats Ethiopian dates in English', function (string $format, string $expected) {
    expect($this->f->date('2026-09-11', $format))->toBe($expected);
})->with([
    ['long', '1 Meskerem 2019'],
    ['medium', '1 Mes 2019'],
    ['short', '01/01/2019'],
    ['month', 'Meskerem 2019'],
]);

it('formats Ethiopian dates in Amharic', function (string $format, string $expected) {
    expect($this->f->date('2026-09-11', $format, locale: 'am'))->toBe($expected);
})->with([
    ['long', 'መስከረም 1 2019'],
    ['medium', 'መስከ 1, 2019'],
    ['short', '01/01/2019'],
    ['month', 'መስከረም 2019'],
]);

it('formats Ethiopian dates in Afaan Oromo', function () {
    expect($this->f->date('2026-09-11', 'long', locale: 'om'))->toBe('Fulbaana 1, 2019');
});

it('formats Pagume days', function () {
    expect($this->f->date('2023-09-11', 'long'))->toBe('6 Pagume 2015')
        ->and($this->f->date('2023-09-11', 'long', locale: 'am'))->toBe('ጳጉሜ 6 2015');
});

it('formats Gregorian dates', function () {
    expect($this->f->date('2026-09-11', 'long', Calendar::Gregorian))->toBe('11 September 2026')
        ->and($this->f->date('2026-09-11', 'medium', Calendar::Gregorian, 'am'))->toBe('Sep 11, 2026');
});

it('writes Gregorian dates in Latin letters, without an era, in Amharic and Afaan Oromo', function (string $locale) {
    expect($this->f->date('2026-09-25', 'medium', Calendar::Gregorian, $locale))->toBe('Sep 25, 2026')
        ->and($this->f->date('2026-09-25', 'long', Calendar::Gregorian, $locale))->toBe('September 25, 2026')
        ->and($this->f->date('2026-09-25', 'medium', Calendar::Gregorian, $locale, era: true))->toBe('Sep 25, 2026');
})->with(['am', 'om']);

it('shows the Gregorian secondary date in Latin letters in Amharic and Afaan Oromo', function () {
    expect($this->f->date('2026-09-25', 'medium', locale: 'am', secondary: true))->toBe('መስከ 15, 2019 (Sep 25, 2026)')
        ->and($this->f->date('2026-09-25', 'medium', locale: 'om', secondary: true))->toBe('Ful 15, 2019 (Sep 25, 2026)')
        ->and($this->f->date('2026-09-25', 'medium', Calendar::Gregorian, 'am', secondary: true))->toBe('Sep 25, 2026 (መስከ 15, 2019 ዓ.ም.)');
});

it('adds weekday, era and the secondary date', function () {
    expect($this->f->date('2026-09-11', 'long', weekday: true))->toBe('Friday, 1 Meskerem 2019')
        ->and($this->f->date('2026-09-11', 'long', era: true))->toBe('1 Meskerem 2019 E.C.')
        ->and($this->f->date('2026-09-11', 'medium', secondary: true))->toBe('1 Mes 2019 (11 Sep 2026 G.C.)')
        ->and($this->f->date('2026-09-11', 'medium', Calendar::Gregorian, secondary: true))->toBe('11 Sep 2026 (1 Mes 2019 E.C.)')
        ->and($this->f->date('2026-09-11', 'long', locale: 'am', weekday: true, era: true))->toBe('ዓርብ፣ መስከረም 1 2019 ዓ.ም.');
});

it('returns null for empty values', function () {
    expect($this->f->date(null))->toBeNull()
        ->and($this->f->date(''))->toBeNull()
        ->and($this->f->time(null))->toBeNull()
        ->and($this->f->dateTime(null))->toBeNull();
});

it('rejects unknown formats', function () {
    $this->f->date('2026-09-11', 'fancy');
})->throws(InvalidArgumentException::class);

it('follows the application calendar', function () {
    app(EthiopicCalendar::class)->setCalendar('gregorian');

    expect($this->f->date('2026-09-11', 'long'))->toBe('11 September 2026');
});

it('formats Ethiopian time', function () {
    expect($this->f->time('06:30'))->toBe('12:30 in the morning')
        ->and($this->f->time('00:15'))->toBe('6:15 at night')
        ->and($this->f->time('13:45'))->toBe('7:45 in the afternoon')
        ->and($this->f->time('18:00', 'am'))->toBe('ማታ 12:00')
        ->and($this->f->time('13:45', ethiopian: false))->toBe('13:45')
        ->and($this->f->time('bad'))->toBeNull();
});

it('formats date and time together', function () {
    $at = CarbonImmutable::create(2026, 9, 11, 14, 5);

    expect($this->f->dateTime($at))->toBe('1 Mes 2019, 8:05 in the afternoon')
        ->and($this->f->dateTime($at, locale: 'am'))->toBe('መስከ 1, 2019፣ ከሰዓት 8:05');
});

it('labels billing months and fiscal years', function () {
    $nehase = BillingMonth::containing('2027-09-10', Calendar::Ethiopian); // Pagume 5, 2019 → Nehase 2019

    expect($this->f->billingMonth($nehase))->toBe('Nehase 2019')
        ->and($this->f->billingMonth($nehase, 'am'))->toBe('ነሐሴ 2019')
        ->and($this->f->fiscalYear(FiscalYear::of(2019, Calendar::Ethiopian)))->toBe('FY 2019')
        ->and($this->f->fiscalYear(FiscalYear::of(2019, Calendar::Ethiopian), 'am'))->toBe('በጀት ዓመት 2019');
});

it('names months and weekdays', function () {
    expect($this->f->monthName(13, Calendar::Ethiopian))->toBe('Pagume')
        ->and($this->f->monthName(13, Calendar::Ethiopian, false, 'am'))->toBe('ጳጉሜ')
        ->and($this->f->weekdayName(7, locale: 'am'))->toBe('እሑድ')
        ->and($this->f->weekdayName(1, true, 'om'))->toBe('Wii');
});

it('rejects month 13 in the gregorian calendar', function () {
    $this->f->monthName(13, Calendar::Gregorian);
})->throws(InvalidArgumentException::class);

it('provides the picker strings', function () {
    $s = $this->f->pickerStrings('am');

    expect($s['months']['ethiopian'])->toHaveCount(13)
        ->and($s['months']['gregorian'])->toHaveCount(12)
        ->and($s['weekdaysMin'])->toHaveCount(7)
        ->and($s['labels']['today'])->toBe('ዛሬ')
        ->and($s['format'])->toBe(':month :day :year');
});
