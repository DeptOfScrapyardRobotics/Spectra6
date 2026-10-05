<?php

use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6;
use DeptOfScrapyardRobotics\Displays\Spectra6\Providers\Spectra6ServiceProvider;
use DeptOfScrapyardRobotics\Displays\Spectra6\Tests\Support\ConfigPathVessel;
use GeneralPurposeIO\IntegratedCircuits\CircuitRegistry;
use Voyager\Config\Repository;
use Voyager\NutsAndBolts\ServiceProvider;

it('registers the wiring config under circuits.spectra6, keeping anything the app already set', function (): void {
    $app = new ConfigPathVessel;
    $app->registerInstance('config', new Repository(['circuits' => ['spectra6' => ['default_config' => 'bench'], 'adxl345' => ['default_config' => 'i2c']]]));

    (new Spectra6ServiceProvider($app))->register();
    $config = $app->make('config');

    expect($config->get('circuits.spectra6.default_config'))->toBe('bench')
        ->and($config->get('circuits.spectra6.configs.spi.busy.pin'))->toBe(24)
        ->and($config->get('circuits.spectra6.configs.spi.pwr.enabled'))->toBeFalse()
        ->and($config->get('circuits.adxl345'))->toBe(['default_config' => 'i2c']);
});

it('publishes the config into config/circuits under the spectra6-config tag', function (): void {
    $app = new ConfigPathVessel('/app/config');
    $app->registerInstance('config', new Repository);

    $provider = new Spectra6ServiceProvider($app);
    $provider->register();
    $provider->boot();

    expect(ServiceProvider::pathsToPublish(Spectra6ServiceProvider::class, 'spectra6-config'))->toBe([
        dirname(__DIR__, 2).'/config/spectra6.php' => '/app/config/circuits/spectra6.php',
    ]);
});

it('adds the panel to the circuit catalog when one is bound', function (): void {
    $app = new ConfigPathVessel;
    $app->registerInstance('config', new Repository);
    $app->registerInstance('circuit', $catalog = new CircuitRegistry);

    $provider = new Spectra6ServiceProvider($app);
    $provider->register();
    $provider->boot();

    expect($catalog->listCircuits())->toBe(['spectra6' => Spectra6::class]);
});
