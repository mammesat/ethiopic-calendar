<?php

declare(strict_types=1);

/**
 * Every locale must define exactly the keys English defines (working rule 4).
 */
function flattenKeys(array $array, string $prefix = ''): array
{
    $keys = [];

    foreach ($array as $key => $value) {
        $path = $prefix === '' ? (string) $key : "{$prefix}.{$key}";
        $keys = is_array($value) ? [...$keys, ...flattenKeys($value, $path)] : [...$keys, $path];
    }

    sort($keys);

    return $keys;
}

function langFile(string $locale): array
{
    return require __DIR__."/../../resources/lang/{$locale}/calendar.php";
}

it('has the same keys in every locale', function (string $locale) {
    expect(flattenKeys(langFile($locale)))->toBe(flattenKeys(langFile('en')));
})->with(['am', 'om']);

it('has no empty strings', function (string $locale) {
    $empty = array_filter(
        flattenKeys(langFile($locale)),
        // An era may be empty on purpose: am / om write Gregorian dates without one.
        fn (string $key): bool => ! str_starts_with($key, 'era.') && trim((string) data_get(langFile($locale), $key)) === '',
    );

    expect($empty)->toBe([]);
})->with(['en', 'am', 'om']);

it('numbers months and weekdays from 1', function (string $locale) {
    $lang = langFile($locale);

    expect(array_keys($lang['ethiopian_months']))->toBe(range(1, 13))
        ->and(array_keys($lang['ethiopian_months_short']))->toBe(range(1, 13))
        ->and(array_keys($lang['gregorian_months']))->toBe(range(1, 12))
        ->and(array_keys($lang['weekdays']))->toBe(range(1, 7))
        ->and(array_keys($lang['weekdays_min']))->toBe(range(1, 7));
})->with(['en', 'am', 'om']);

it('uses the transliteration of blueprint §11.3 for English month names', function () {
    expect(array_values(langFile('en')['ethiopian_months']))->toBe([
        'Meskerem', 'Tikimt', 'Hidar', 'Tahsas', 'Tir', 'Yekatit', 'Megabit',
        'Miyazya', 'Ginbot', 'Sene', 'Hamle', 'Nehase', 'Pagume',
    ]);
});
