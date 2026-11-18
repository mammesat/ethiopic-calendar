<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar\Tests\Support;

/** Stands in for NativePHP's NativeComponent, which this package does not require. */
abstract class FakeNativeScreen
{
    public bool $leftScreen = false;

    public function onBackPressed(): void
    {
        $this->leftScreen = true;
    }
}
