<?php

namespace DeptOfScrapyardRobotics\Displays\Spectra6\Providers;

use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6;
use Voyager\NutsAndBolts\ServiceProvider;

/**
 * The panel's wiring config lives under the circuits tree: config('circuits.spectra6'), published to
 * config/circuits/spectra6.php, which the config loader keys the same way.
 */
class Spectra6ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/spectra6.php', 'circuits.spectra6');
    }

    public function boot(): void
    {
        $this->publishes([
            dirname(__DIR__, 2).'/config/spectra6.php' => $this->app->configPath('circuits/spectra6.php'),
        ], 'spectra6-config');

        // With the GPIO catalog installed, the panel is conjurable by slug: app('circuit')->conjure('spectra6').
        if ($this->app->isBound('circuit')) {
            $this->app->make('circuit')->addCircuit('spectra6', Spectra6::class);
        }
    }
}
