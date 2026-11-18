<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar\Tests\Support;

use Mammesat\EthiopicCalendar\Calendar;
use Mammesat\EthiopicCalendar\Native\WithEthiopicDatePicker;

/** A screen with two date fields, used by NativeDatePickerTest and analysed by Larastan. */
final class LeaseScreen extends FakeNativeScreen
{
    use WithEthiopicDatePicker;

    public ?string $startDate = null;

    public ?string $paidOn = '2026-09-25';

    /** @var list<string> */
    public array $updates = [];

    protected string $secret = 'x';

    /**
     * @return array{min?: string|null, max?: string|null, calendar?: Calendar|string|null}
     */
    protected function datePickerOptions(string $field): array
    {
        return $field === 'paidOn' ? ['min' => '2026-09-15', 'max' => '2026-10-05'] : [];
    }

    public function updatedStartDate(string $value): void
    {
        $this->updates[] = $value;
    }
}
