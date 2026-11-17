<?php

declare(strict_types=1);

namespace Mammesat\EthiopicCalendar;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class EthiopicCalendarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ethiopic-calendar.php', 'ethiopic-calendar');

        $this->app->singleton(EthiopicCalendar::class, function ($app): EthiopicCalendar {
            $config = $app['config'];

            return new EthiopicCalendar(
                Calendar::tryFrom((string) $config->get('ethiopic-calendar.calendar')) ?? Calendar::Ethiopian,
                (int) $config->get('ethiopic-calendar.week_starts_on', 1),
            );
        });

        $this->app->singleton(Formatter::class);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'ethiopic-calendar');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ethiopic-calendar');

        Blade::componentNamespace('Mammesat\\EthiopicCalendar\\View\\Components', 'ethiopic');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/ethiopic-calendar.php' => config_path('ethiopic-calendar.php'),
            ], 'ethiopic-calendar-config');

            $this->publishes([
                __DIR__.'/../resources/lang' => $this->app->langPath('vendor/ethiopic-calendar'),
            ], 'ethiopic-calendar-lang');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/ethiopic-calendar'),
            ], 'ethiopic-calendar-views');
        }
    }
}
