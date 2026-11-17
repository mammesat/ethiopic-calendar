<?php

/**
 * Renders the real Blade components into demo/index.html for a browser check
 * of the date picker without a Laravel application.
 *
 *   php bin/build-demo.php
 *   php -S 127.0.0.1:8123        (from the package root)
 *   open http://127.0.0.1:8123/demo/
 *
 * Uses the Tailwind Play CDN and Alpine from a CDN; the package's own
 * JavaScript is loaded from resources/js as ES modules.
 */

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Mammesat\EthiopicCalendar\EthiopicCalendarServiceProvider;
use Orchestra\Testbench\Foundation\Application;

require __DIR__.'/../vendor/autoload.php';

$app = Application::create(basePath: Application::applicationBasePath(), options: [
    'extra' => ['providers' => [EthiopicCalendarServiceProvider::class], 'dont-discover' => ['*']],
]);
$app['config']->set('app.timezone', 'Africa/Addis_Ababa');

$sections = [];

foreach (['en' => 'English', 'am' => 'አማርኛ', 'om' => 'Afaan Oromoo'] as $locale => $name) {
    $app->setLocale($locale);

    $sections[] = Blade::render(<<<'BLADE'
        <section class="space-y-4 rounded-xl bg-white p-4 shadow-sm" data-locale="{{ $locale }}">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">{{ $name }}</h2>
            <div x-data="{ start: '2026-09-11', paid: null }" class="space-y-4">
                <x-ethiopic::date-picker x-model="start" label="Ethiopian" :id="'eth-'.$locale" />
                <p class="text-sm text-gray-600">Bound value: <code x-text="start ?? 'null'" data-testid="value-eth-{{ $locale }}"></code></p>
                <x-ethiopic::date-picker x-model="paid" calendar="gregorian" label="Gregorian, min 2026-09-01, max 2026-12-31" min="2026-09-01" max="2026-12-31" :id="'greg-'.$locale" />
                <p class="text-sm text-gray-600">Bound value: <code x-text="paid ?? 'null'" data-testid="value-greg-{{ $locale }}"></code></p>
            </div>
            <p class="text-sm">Display: <x-ethiopic::date value="2023-09-11" format="long" weekday era secondary /></p>
        </section>
    BLADE, ['locale' => $locale, 'name' => $name]);
}

$body = implode("\n", $sections);

$html = <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Date picker demo</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script type="module" src="/resources/js/date-picker.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <style>body{font-family:system-ui,"Noto Sans Ethiopic","Nyala",sans-serif}</style>
</head>
<body class="min-h-screen bg-gray-100 p-4">
    <main class="mx-auto max-w-md space-y-6">
        <h1 class="text-xl font-bold">mammesat/ethiopic-calendar</h1>
        {$body}
    </main>
</body>
</html>
HTML;

@mkdir(__DIR__.'/../demo', 0777, true);
file_put_contents(__DIR__.'/../demo/index.html', $html);

fwrite(STDOUT, "Wrote demo/index.html\n");
