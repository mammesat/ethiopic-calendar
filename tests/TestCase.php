<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar\Tests;

use Mammesat\EthiopicCalendar\EthiopicCalendarServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [EthiopicCalendarServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.timezone', 'Africa/Addis_Ababa');
        $app['config']->set('app.locale', 'en');
        $app['config']->set('app.fallback_locale', 'en');
    }
}
