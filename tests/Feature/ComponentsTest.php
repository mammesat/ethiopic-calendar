<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Mammesat\EthiopicCalendar\EthiopicDate;

it('renders the date display component', function () {
    expect(Blade::render('<x-ethiopic::date value="2026-09-11" />'))
        ->toContain('<time datetime="2026-09-11"')
        ->toContain('1 Mes 2019</time>');
});

it('accepts options and an EthiopicDate value', function () {
    $html = Blade::render(
        '<x-ethiopic::date :value="$d" format="long" weekday era class="text-sm" />',
        ['d' => EthiopicDate::of(2015, 13, 6)],
    );

    expect($html)->toContain('datetime="2023-09-11"')
        ->toContain('class="text-sm"')
        ->toContain('Monday, 6 Pagume 2015 E.C.');
});

it('renders a placeholder for an empty date', function () {
    expect(Blade::render('<x-ethiopic::date :value="null" />'))->toContain('<span >—</span>');
});

it('renders the date picker with its configuration', function () {
    $html = Blade::render('<x-ethiopic::date-picker wire:model="start_date" value="2026-09-11" min="2026-01-01" label="Start date" />');

    expect($html)
        ->toContain('x-data="ethiopicDatePicker(')
        ->toContain('x-modelable="value"')
        ->toContain('wire:model="start_date"')
        ->toContain('Start date')
        ->toContain('\u0022calendar\u0022:\u0022ethiopian\u0022')
        ->toContain('\u0022value\u0022:\u00222026-09-11\u0022')
        ->toContain('\u0022min\u0022:\u00222026-01-01\u0022');
});

it('renders a hidden input for plain forms and a gregorian grid on request', function () {
    $html = Blade::render('<x-ethiopic::date-picker name="due_date" calendar="gregorian" required />');

    expect($html)
        ->toContain('<input type="hidden" name="due_date"')
        ->toContain('\u0022calendar\u0022:\u0022gregorian\u0022')
        ->not->toContain('x-on:click="clear()"');
});

it('translates the picker', function () {
    app()->setLocale('am');

    expect(Blade::render('<x-ethiopic::date-picker />'))->toContain('ቀን ይምረጡ')->toContain('ዛሬ');
});
