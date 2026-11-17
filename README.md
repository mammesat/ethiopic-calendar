# Ethiopic Calendar for Laravel, Livewire and Alpine

Ethiopian calendar support without Filament or dayjs: conversion, billing-safe
month arithmetic, fiscal years, formatting in English, Amharic and Afaan Oromo,
a display component, and a touch-friendly date picker.

Dates are **stored as Gregorian** `Y-m-d`; the Ethiopian calendar is used for
display, input and month arithmetic.

## Installation

```bash
composer require mammesat/ethiopic-calendar
```

During development of an app next to this repository, use a path repository:

```json
"repositories": [{ "type": "path", "url": "../ethiopic-calendar" }]
```

Optional publishing:

```bash
php artisan vendor:publish --tag=ethiopic-calendar-config
php artisan vendor:publish --tag=ethiopic-calendar-lang
php artisan vendor:publish --tag=ethiopic-calendar-views
```

## The calendar setting

```php
use Mammesat\EthiopicCalendar\EthiopicCalendar;

// config/ethiopic-calendar.php → 'calendar' => 'ethiopian' (default) or 'gregorian'
// Override per request, e.g. in middleware that reads your settings:
app(EthiopicCalendar::class)->setCalendar($settings->calendar);
```

## Dates

```php
use Mammesat\EthiopicCalendar\EthiopicDate;

$d = EthiopicDate::fromGregorian('2026-09-11');   // 1 Meskerem 2019
$d->year; $d->month; $d->day;                     // 2019, 1, 1
$d->toGregorianString();                           // "2026-09-11"
(string) $d;                                       // "2019-01-01"
EthiopicDate::of(2015, 13, 6)->toGregorian();      // CarbonImmutable 2023-09-11
$d->addDays(30); $d->diffInDays($other); $d->dayOfWeek(); // ISO 1–7
```

Month 13 is Pagume: 5 days, 6 when `year % 4 === 3`.

## Billing arithmetic

Month addition always uses an **anchor day**, and **Pagume is the tail of Nehase**:

- Ethiopian months 1–12 all have 30 days, so the result is never in Pagume.
  Nehase is followed by Meskerem.
- A Pagume date is treated as 30 Nehase (anchor day 30).
- Gregorian anchor days that don't exist in a month fall on its last day and
  come back next month: 31 Jan → 28 Feb → 31 Mar.

Compute each period from the start date (`addMonths($start, $k)`), never by
chaining, so a short month does not shift the following ones.

```php
use Mammesat\EthiopicCalendar\{Arithmetic, BillingMonth, Calendar, FiscalYear};

Arithmetic::addMonths('2026-01-31', 1, Calendar::Gregorian);     // 2026-02-28
Arithmetic::addMonths('2026-01-31', 2, Calendar::Gregorian);     // 2026-03-31
Arithmetic::anchorDay($start, Calendar::Ethiopian);              // 30 for a Pagume start
Arithmetic::endDateFor($start, 2, Calendar::Ethiopian);          // day before the anniversary

$month = BillingMonth::containing('2027-09-10', Calendar::Ethiopian); // Nehase 2019, through Pagume
$month->start; $month->end; $month->next();

$fy = FiscalYear::containing('2026-07-08', Calendar::Ethiopian); // 1 Hamle 2018 → FY 2019
$fy->label();   // "19"
$fy->months();  // Hamle, Nehase (with Pagume), Meskerem … Sene
```

The Ethiopian fiscal year runs 1 Hamle to 30 Sene and is named after the year
in which it ends. The Gregorian fiscal year is January to December.

## Formatting

```php
use Mammesat\EthiopicCalendar\Formatter;

$f = app(Formatter::class);
$f->date('2026-09-11');                          // "1 Mes 2019"           (medium)
$f->date('2026-09-11', 'long', locale: 'am');    // "መስከረም 1 ቀን 2019"
$f->date('2026-09-11', 'short');                 // "01/01/2019"
$f->date('2026-09-11', 'long', weekday: true, era: true);  // "Friday, 1 Meskerem 2019 E.C."
$f->date('2026-09-11', secondary: true);         // "1 Mes 2019 (11 Sep 2026 G.C.)"
$f->time('06:30');                               // "12:30 in the morning"  (Ethiopian time)
$f->billingMonth($month);                        // "Nehase 2019"
$f->fiscalYear($fy);                             // "FY 2019"
```

Formats: `long`, `medium`, `short`, `month`. The calendar defaults to the app
setting; pass a `Calendar` to override it.

## Blade components

```blade
<x-ethiopic::date :value="$invoice->due_date" />
<x-ethiopic::date :value="$lease->start_date" format="long" weekday secondary />

<x-ethiopic::date-picker wire:model="start_date" label="Start date" />
<x-ethiopic::date-picker wire:model.live="paid_on" :max="today()" required />
<x-ethiopic::date-picker name="due_date" value="2026-09-11" />   {{-- plain form --}}
```

The picker's value is always a Gregorian `Y-m-d` string. Attributes: `min`,
`max`, `calendar` (`ethiopian` / `gregorian`), `label`, `placeholder`,
`clearable`, `required`, `disabled`, `id`.

### JavaScript

Import the picker once in your app bundle, before Livewire starts Alpine:

```js
// resources/js/app.js
import '../../vendor/mammesat/ethiopic-calendar/resources/js/date-picker.js'
```

It registers `Alpine.data('ethiopicDatePicker')` on `alpine:init`. For a
custom Alpine setup, call `registerDatePicker(Alpine)` yourself.

### Tailwind

The views use Tailwind utility classes. Add the package views to your sources:

```css
/* Tailwind v4 */
@source "../../vendor/mammesat/ethiopic-calendar/resources/views";
```

## Eloquent

```php
use Mammesat\EthiopicCalendar\Casts\EthiopicDateCast;

protected function casts(): array
{
    return [
        'start_date' => 'date',                                       // normal Carbon attribute
        'start_date_ethiopic' => EthiopicDateCast::class.':start_date', // read-only companion
    ];
}
```

Without an argument, `EthiopicDateCast` reads and writes its own column: it stores
Gregorian `Y-m-d` and returns an `EthiopicDate`.

## Translations

`resources/lang/{en,am,om}/calendar.php`. Month and weekday names in English and
Amharic come from `mammesat/filament-ethiopic-calendar`. **The Afaan Oromo file
is a draft awaiting native review.**

## Development

```bash
composer install && npm install
composer test        # Pest
npm test             # Vitest
composer analyse     # Larastan, level 8
composer lint        # Pint
composer fixtures    # regenerate tests/fixtures/dates.json
php bin/build-demo.php && php -S 127.0.0.1:8123   # then open /demo/
```

`tests/fixtures/dates.json` holds every day of Ethiopian years 1990–2060. It is
generated by walking day by day from one known anchor (1 Meskerem 1990 =
11 September 1997), **without** the package converter, and both the PHP and the
JavaScript conversions are tested against it.

## License

MIT
