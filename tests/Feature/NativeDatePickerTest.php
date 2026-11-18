<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Mammesat\EthiopicCalendar\Tests\Support\LeaseScreen;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-09-20'); // 10 Meskerem 2019
    $this->screen = new LeaseScreen;
});

afterEach(fn () => CarbonImmutable::setTestNow());

it('opens on today when the field is empty, with nothing highlighted', function () {
    $this->screen->openDatePicker('startDate');
    $state = $this->screen->datePickerState();

    expect($state['open'])->toBeTrue()
        ->and($state['title'])->toBe('Meskerem 2019')
        ->and($state['headline'])->toBe('—')
        ->and($state['weekdays'])->toBe(['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'])
        ->and(collect($state['weeks'])->flatten(1)->filter()->firstWhere('today', true)['day'])->toBe(10);
});

it('highlights a tapped day and writes it only on OK', function () {
    $s = $this->screen;
    $s->openDatePicker('startDate');
    $s->datePickerPickDay(2461309); // 15 Meskerem 2019 = 2026-09-25

    expect($s->startDate)->toBeNull()
        ->and($s->datePickerState()['headline'])->toBe('15 Mes 2019');

    $s->confirmDatePicker();

    expect($s->startDate)->toBe('2026-09-25')
        ->and($s->updates)->toBe(['2026-09-25'])
        ->and($s->datePickerField)->toBeNull()
        ->and($s->datePickerLabel('startDate'))->toBe('15 Mes 2019')
        ->and($s->datePickerSecondaryLabel('startDate'))->toBe('25 Sep 2026 G.C.');
});

it('discards the highlight on Cancel', function () {
    $s = $this->screen;
    $s->openDatePicker('startDate');
    $s->datePickerPickDay(2461309);
    $s->cancelDatePicker();

    expect($s->startDate)->toBeNull()->and($s->datePickerState()['open'])->toBeFalse();
});

it('opens on the current value and moves through Pagume', function () {
    $s = $this->screen;
    $s->startDate = '2026-08-20'; // 14 Nehase 2018
    $s->openDatePicker('startDate');

    expect([$s->datePickerYear, $s->datePickerMonth])->toBe([2018, 12]);

    $s->datePickerNextMonth();
    expect($s->datePickerState()['title'])->toBe('Pagume 2018');

    $s->datePickerNextMonth();
    expect([$s->datePickerYear, $s->datePickerMonth])->toBe([2019, 1]);
});

it('respects min and max', function () {
    $s = $this->screen;
    $s->openDatePicker('paidOn');
    $state = $s->datePickerState();
    $cell = fn (int $day) => collect($state['weeks'])->flatten(1)->filter()->firstWhere('day', $day);

    expect($cell(4)['disabled'])->toBeTrue()     // 14 Sep
        ->and($cell(5)['disabled'])->toBeFalse() // 15 Sep
        ->and($cell(26)['disabled'])->toBeTrue() // 6 Oct
        ->and($state['canPrevious'])->toBeFalse()
        ->and($state['canNext'])->toBeFalse();

    $s->datePickerPickDay(2461298); // 14 Sep: disabled
    expect($s->datePickerPending)->toBe('2026-09-25');
});

it('picks a year from the year grid and keeps the month', function () {
    $s = $this->screen;
    $s->openDatePicker('startDate');
    $s->datePickerToggleYears();

    expect($s->datePickerMode)->toBe('years')
        ->and($s->datePickerState()['years'][0][0]['year'])->toBe(2012);

    $s->datePickerYearsPage(-1);
    expect($s->datePickerYearPage)->toBe(1997);

    $s->datePickerPickYear(2001);
    expect([$s->datePickerYear, $s->datePickerMonth, $s->datePickerMode])->toBe([2001, 1, 'days']);
});

it('closes the dialog on back, then leaves the screen on the next back', function () {
    $s = $this->screen;
    $s->openDatePicker('startDate');
    $s->onBackPressed();

    expect($s->datePickerField)->toBeNull()->and($s->leftScreen)->toBeFalse();

    $s->onBackPressed();
    expect($s->leftScreen)->toBeTrue();
});

it('refuses to edit anything but a public field', function (string $field) {
    $this->screen->openDatePicker($field);
})->with(['secret', 'datePickerYear', 'missing'])->throws(InvalidArgumentException::class);

it('translates the dialog', function () {
    app()->setLocale('am');
    $s = $this->screen;
    $s->openDatePicker('startDate');
    $state = $s->datePickerState();

    expect($state['title'])->toBe('መስከረም 2019')
        ->and($state['strings']['ok'])->toBe('እሺ')
        ->and($state['weekdays'][0])->toBe('ሰ');
});
