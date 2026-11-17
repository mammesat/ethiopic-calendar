<?php

declare(strict_types=1);

use Mammesat\EthiopicCalendar\EthiopicDate;
use Mammesat\EthiopicCalendar\Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature');

/**
 * @return list<array{0: string, 1: string, 2: int, 3: int}>
 */
function fixtureRows(): array
{
    static $rows = null;

    if ($rows === null) {
        $data = json_decode((string) file_get_contents(__DIR__.'/fixtures/dates.json'), true, flags: JSON_THROW_ON_ERROR);
        $rows = $data['rows'];
    }

    return $rows;
}

/** Shorthand: Ethiopian Y-m-d → Gregorian Y-m-d. */
function eth(string $ethiopian): string
{
    return EthiopicDate::parse($ethiopian)->toGregorianString();
}

/** Shorthand: Gregorian Y-m-d → Ethiopian Y-m-d. */
function greg(string $gregorian): string
{
    return EthiopicDate::fromGregorian($gregorian)->toDateString();
}
