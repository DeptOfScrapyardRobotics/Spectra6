<?php

/*
| Proven against recording fakes: every command and data byte the panel would
| see, tagged by the DC level it went out under, every RST level, and a
| scripted BUSY line (low while busy). Nothing here touches a bus. The live
| check is Waveshare's 4 inch (E) Spectra 6 HAT on a Raspberry Pi.
*/

use DeptOfScrapyardRobotics\Displays\Spectra6\Tests\Support\ConfigPathVessel;
use DeptOfScrapyardRobotics\Displays\Spectra6\Tests\Support\FakeDigitalIOConnectionDriver;
use DeptOfScrapyardRobotics\Displays\Spectra6\Tests\Support\FakeSPIConnectionDriver;
use GeneralPurposeIO\Digital\DigitalOConnectionManager;
use GeneralPurposeIO\IntegratedCircuits\CircuitRegistry;
use GeneralPurposeIO\SPI\SPIConnectionManager;
use Voyager\Config\Repository;
use Voyager\Vessel\ControlPanel;

/*
| A Venusian app's core defines config() over the container's config repository;
| CircuitRegistry::conjure() calls it. A package suite has no core, so this
| stands in for it the same way.
*/
if (! function_exists('config')) {
    function config(array|string|null $key = null, mixed $default = null): mixed
    {
        $config = ControlPanel::getInstance()->make('config');

        return match (true) {
            is_null($key) => $config,
            is_array($key) => $config->set($key),
            default => $config->get($key, $default),
        };
    }
}

/**
 * The shared container as an app sets it up: config, the circuit catalog, and the two protocol managers, each
 * with a 'fake' driver.
 *
 * @return array{spi: FakeSPIConnectionDriver, digital: FakeDigitalIOConnectionDriver, app: ConfigPathVessel}
 */
function fakeBench(array $config = []): array
{
    $app = new ConfigPathVessel;
    $app->registerInstance('config', new Repository($config));
    $app->registerInstance('circuit', new CircuitRegistry);
    ControlPanel::setInstance($app);

    $bench = [
        'spi' => new FakeSPIConnectionDriver,
        'digital' => new FakeDigitalIOConnectionDriver,
        'app' => $app,
    ];

    $app->registerInstance('gpio.spi', (new SPIConnectionManager($app))->extend('fake', fn () => $bench['spi']));
    $app->registerInstance('gpio.digital', (new DigitalOConnectionManager($app))->extend('fake', fn () => $bench['digital']));

    return $bench;
}

pest()->afterEach(function (): void {
    ControlPanel::setInstance(null);
})->in(__DIR__);

/**
 * @return array{0: \DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6, 1: \DeptOfScrapyardRobotics\Displays\Spectra6\Tests\Support\FakeSPITransport, 2: array{dc: \DeptOfScrapyardRobotics\Displays\Spectra6\Tests\Support\FakeOutputPin, rst: \DeptOfScrapyardRobotics\Displays\Spectra6\Tests\Support\FakeOutputPin, busy: \DeptOfScrapyardRobotics\Displays\Spectra6\Tests\Support\FakeBusyPin, pwr: \DeptOfScrapyardRobotics\Displays\Spectra6\Tests\Support\FakeOutputPin}}
 */
function s6(?\DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6Configuration $config = null, bool $boot = true): array
{
    $pins = [
        'dc' => new \DeptOfScrapyardRobotics\Displays\Spectra6\Tests\Support\FakeOutputPin(25),
        'rst' => new \DeptOfScrapyardRobotics\Displays\Spectra6\Tests\Support\FakeOutputPin(17),
        'busy' => new \DeptOfScrapyardRobotics\Displays\Spectra6\Tests\Support\FakeBusyPin(24),
        'pwr' => new \DeptOfScrapyardRobotics\Displays\Spectra6\Tests\Support\FakeOutputPin(18),
    ];
    $spi = new \DeptOfScrapyardRobotics\Displays\Spectra6\Tests\Support\FakeSPITransport($pins['dc']);
    $transport = new \DeptOfScrapyardRobotics\Displays\Spectra6\Transports\Spectra6SPITransport($spi, $pins['dc'], $pins['rst'], $pins['busy'], $pins['pwr']);

    return [new \DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6($transport, $config ?? new \DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6Configuration, boot_now: $boot), $spi, $pins];
}

/** Waveshare's 4 inch (E) bring-up, one entry per command with its parameters. */
function s6BootCommands(): array
{
    return [
        [0xAA, [0x49, 0x55, 0x20, 0x08, 0x09, 0x18]],
        [0x01, [0x3F]],
        [0x00, [0x5F, 0x69]],
        [0x05, [0x40, 0x1F, 0x1F, 0x2C]],
        [0x08, [0x6F, 0x1F, 0x1F, 0x22]],
        [0x06, [0x6F, 0x1F, 0x17, 0x17]],
        [0x03, [0x00, 0x54, 0x00, 0x44]],
        [0x60, [0x02, 0x00]],
        [0x30, [0x08]],
        [0x50, [0x3F]],
        [0x61, [0x01, 0x90, 0x02, 0x58]],
        [0xE3, [0x2F]],
        [0x84, [0x01]],
    ];
}
