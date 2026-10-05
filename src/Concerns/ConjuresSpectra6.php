<?php

namespace DeptOfScrapyardRobotics\Displays\Spectra6\Concerns;

use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6Configuration;
use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6Exception;
use DeptOfScrapyardRobotics\Displays\Spectra6\Transports\Spectra6SPITransport;
use GeneralPurposeIO\Contracts\Digital\DigitalInTransport;
use GeneralPurposeIO\Contracts\Digital\DigitalOutTransport;
use GeneralPurposeIO\Contracts\SPI\SPIMode;
use Voyager\Vessel\ControlPanel;

/**
 * The spi() protocol factory the circuit catalog calls. Its parameters are the keys of a config/circuits/spectra6.php
 * entry, so app('circuit')->conjure('spectra6') builds a wired, booted panel from the app's config alone. A bus or
 * pin device that is not connected yet is connected here; one the app already connected is shared as it is.
 */
trait ConjuresSpectra6
{
    /**
     * Opens the bus in $mode when it is not connected yet and clocks this chip select at $speed whatever the bus
     * runs at; a bus the app already opened in another mode is refused. DC, RST, BUSY and PWR are opened after the
     * bus, so pins on an FT232H ride the bus's own context. PWR is the HAT's supply switch; leave it disabled when
     * the panel is always powered.
     *
     * @param  array{driver: string, device: string|int, pin: int}  $dc
     * @param  array{driver: string, device: string|int, pin: int}  $rst
     * @param  array{driver: string, device: string|int, pin: int}  $busy
     * @param  array{enabled?: bool, driver?: string, device?: string|int, pin?: int}  $pwr
     */
    public static function spi(
        string $driver,
        string|int $device,
        array $dc,
        array $rst,
        array $busy,
        array $pwr = [],
        int $chip_select = 0,
        int $mode = 0,
        int $speed = 10_000_000,
        ?int $width = null,
        ?int $height = null,
        bool $boot_now = true,
    ): static {
        $spi_mode = SPIMode::tryFrom($mode) ?? throw Spectra6Exception::invalidSpiMode($mode);

        if ($speed < 1) {
            throw Spectra6Exception::invalidSpiClock($speed);
        }

        $configuration = new Spectra6Configuration(...array_filter(['width' => $width, 'height' => $height], static fn (?int $value): bool => ! is_null($value)));

        $bus = static::gpio('gpio.spi')->driver($driver);
        $spi = $bus->device($device, $chip_select)
            ?? $bus->connectTo($device)->mode($spi_mode)->speed($speed)->register()->device($device, $chip_select);

        if (is_null($spi)) {
            throw Spectra6Exception::notConnected('SPI', $driver, $device);
        }

        $bus_mode = $bus->settingsOf($device)?->mode;

        if (! is_null($bus_mode) && $bus_mode !== $spi_mode) {
            throw Spectra6Exception::wrongSpiMode($device, $bus_mode->value, $mode);
        }

        $spi->speed($speed);

        $transport = new Spectra6SPITransport(
            $spi,
            static::outputLine($dc, 'dc'),
            static::outputLine($rst, 'rst'),
            static::inputLine($busy, 'busy'),
            ($pwr['enabled'] ?? false) ? static::outputLine($pwr, 'pwr') : null,
        );

        return new static($transport, $configuration, $boot_now);
    }

    /** @param  array{driver?: string, device?: string|int, pin?: int}  $line */
    protected static function outputLine(array $line, string $name): DigitalOutTransport
    {
        if (! isset($line['driver'], $line['device'], $line['pin'])) {
            throw Spectra6Exception::incompletePin($name);
        }

        $pins = static::gpio('gpio.digital')->driver($line['driver']);
        $pin = $pins->output($line['device'], $line['pin'])
            ?? $pins->connectTo($line['device'])->register()->output($line['device'], $line['pin']);

        return $pin ?? throw Spectra6Exception::notConnected('DigitalIO', $line['driver'], $line['device']);
    }

    /** @param  array{driver?: string, device?: string|int, pin?: int}  $line */
    protected static function inputLine(array $line, string $name): DigitalInTransport
    {
        if (! isset($line['driver'], $line['device'], $line['pin'])) {
            throw Spectra6Exception::incompletePin($name);
        }

        $pins = static::gpio('gpio.digital')->driver($line['driver']);
        $pin = $pins->input($line['device'], $line['pin'])
            ?? $pins->connectTo($line['device'])->register()->input($line['device'], $line['pin']);

        return $pin ?? throw Spectra6Exception::notConnected('DigitalIO', $line['driver'], $line['device']);
    }

    /** A protocol manager from the app's container: gpio.spi or gpio.digital. */
    protected static function gpio(string $manager): mixed
    {
        return ControlPanel::getInstance()->make($manager);
    }
}
