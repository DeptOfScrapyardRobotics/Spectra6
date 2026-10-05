<?php

use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6BoosterSoftStart;
use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6PLLControl;
use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6;
use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6Configuration;
use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6Exception;
use GeneralPurposeIO\Contracts\IntegratedCircuits\CircuitException;
use GeneralPurposeIO\Contracts\IntegratedCircuits\DisplayPanel;
use GeneralPurposeIO\Contracts\IntegratedCircuits\RefreshesOnCommand;
use GeneralPurposeIO\Contracts\IntegratedCircuits\RefreshMode;
use GeneralPurposeIO\Contracts\IntegratedCircuits\Switchable;
use GeneralPurposeIO\Contracts\IntegratedCircuits\WindowAddressable;
use Surface\Contracts\Framebuffers\BitDepth;
use Surface\Contracts\Framebuffers\EInkColor;
use Surface\Contracts\Framebuffers\PixelFormat;

it('is a panel that refreshes on command, takes whole frames, and does not switch', function (): void {
    expect(is_subclass_of(Spectra6::class, DisplayPanel::class))->toBeTrue()
        ->and(is_subclass_of(Spectra6::class, RefreshesOnCommand::class))->toBeTrue()
        ->and(is_subclass_of(Spectra6::class, WindowAddressable::class))->toBeFalse()
        ->and(is_subclass_of(Spectra6::class, Switchable::class))->toBeFalse();
});

it('boots with the vendor bring-up: power, reset, wait, CMDH, registers, resolution', function (): void {
    [$panel, $spi, $pins] = s6();

    expect($panel->hasBooted())->toBeTrue()
        ->and($pins['pwr']->levels)->toBe([true])
        ->and($pins['rst']->levels)->toBe([true, false, true])
        ->and($spi->commands())->toBe(s6BootCommands())
        ->and([$panel->width(), $panel->height()])->toBe([400, 600]);
});

it('writes the configured size into the resolution register', function (): void {
    [, $spi] = s6(new Spectra6Configuration(width: 480, height: 800));

    expect($spi->commands()[10])->toBe([0x61, [0x01, 0xE0, 0x03, 0x20]]);
});

it('waits while BUSY is low', function (): void {
    [$panel, , $pins] = s6(boot: false);
    $pins['busy']->script = [false, true, false, false, true];

    $panel->boot();

    expect($pins['busy']->reads)->toBe(5);
});

it('throws when BUSY never rises', function (): void {
    [$panel, , $pins] = s6(new Spectra6Configuration(busy_timeout_ms: 15), boot: false);
    $pins['busy']->level = false;

    expect(fn () => $panel->boot())->toThrow(Spectra6Exception::class, 'BUSY stayed low for 15 ms')
        ->and($panel->hasBooted())->toBeFalse();
});

it('describes its bytes as packed four-bit palette codes for the six inks', function (): void {
    [$panel] = s6(boot: false);
    $spec = $panel->formatSpec();

    expect($spec->pixel_format)->toBe(PixelFormat::ROW_MAJOR)
        ->and($spec->bit_depth)->toBe(BitDepth::B4)
        ->and($spec->palette->colors())->toBe([EInkColor::BLACK->value, EInkColor::WHITE->value, EInkColor::YELLOW->value, EInkColor::RED->value, EInkColor::BLUE->value, EInkColor::GREEN->value])
        ->and($spec->palette->codes())->toBe([0x0, 0x1, 0x2, 0x3, 0x5, 0x6]);
});

it('streams a whole frame: 200 bytes a row, 600 rows', function (): void {
    [$panel, $spi] = s6();
    $before = count($spi->writes);

    $panel->transmit(0, 0, array_fill(0, 200 * 600, 0x11));
    $commands = $spi->commands($before);

    expect($commands)->toHaveCount(1)
        ->and($commands[0][0])->toBe(0x10)
        ->and(count($commands[0][1]))->toBe(120_000);
});

it('takes whole frames only, with the right byte count', function (array $args, string $message): void {
    [$panel, $spi] = s6();
    $before = count($spi->writes);

    expect(fn () => $panel->transmit(...$args))->toThrow(Spectra6Exception::class, $message)
        ->and(count($spi->writes))->toBe($before);
})->with([
    'window' => [[8, 0, array_fill(0, 10, 0x11), 8, 5], 'takes whole frames'],
    'byte count' => [[0, 0, array_fill(0, 100, 0x11)], 'needs 120000 bytes; got 100'],
]);

it('refreshes: power on, the refresh booster, refresh, power off', function (): void {
    [$panel, $spi] = s6();
    $before = count($spi->writes);

    $panel->refresh();

    expect($spi->commands($before))->toBe([[0x04, []], [0x06, [0x6F, 0x1F, 0x17, 0x27]], [0x12, [0x00]], [0x02, [0x00]]])
        ->and(fn () => $panel->refresh(RefreshMode::PARTIAL))->toThrow(Spectra6Exception::class, 'has no partial refresh');
});

it('takes a refresh booster setting without writing it until the next refresh', function (): void {
    [$panel, $spi] = s6();
    $before = count($spi->writes);

    $panel->refresh_booster = new Spectra6BoosterSoftStart(0x6F, 0x1F, 0x17, 0x37);

    expect($spi->commands($before))->toBe([]);

    $panel->refresh();

    expect($spi->commands($before)[1])->toBe([0x06, [0x6F, 0x1F, 0x17, 0x37]]);
});

it('sleeps, and boots again from a hardware reset', function (): void {
    [$panel, $spi, $pins] = s6();
    $before = count($spi->writes);

    $panel->sleep();

    expect($spi->commands($before))->toBe([[0x07, [0xA5]]])
        ->and($panel->hasBooted())->toBeFalse();

    $panel->boot();

    expect($pins['rst']->levels)->toBe([true, false, true, true, false, true]);
});

it('reads any configuration key and writes the register settings', function (): void {
    [$panel, $spi] = s6();
    $before = count($spi->writes);

    $panel->pll_control = new Spectra6PLLControl(0x0A);

    expect($spi->commands($before))->toBe([[0x30, [0x0A]]])
        ->and($panel->pll_control->frame_rate)->toBe(0x0A)
        ->and($panel->busy_timeout_ms)->toBe(60_000)
        ->and(fn () => $panel->height = 10)->toThrow(Spectra6Exception::class, "Invalid property 'height'");
});

it('throws when the bus refuses a write', function (): void {
    [$panel, $spi] = s6();
    $spi->answer = -1;

    expect(fn () => $panel->refresh())->toThrow(Spectra6Exception::class, 'Spectra6 SPI command 0x04 write failed: -1 of 1 bytes');
});

it('refuses a size the resolution register cannot hold and a BUSY timeout under 1 ms', function (): void {
    expect(fn () => new Spectra6Configuration(width: 0))->toThrow(Spectra6Exception::class, 'does not fit the resolution register')
        ->and(fn () => new Spectra6Configuration(busy_timeout_ms: 0))->toThrow(Spectra6Exception::class, 'busy_timeout_ms takes 1');
});

it('releases DC, RST, BUSY and PWR on close, leaving the bus to its driver', function (): void {
    [$panel, $spi, $pins] = s6();

    $panel->close();

    expect($pins['dc']->closed())->toBeTrue()
        ->and($pins['rst']->closed())->toBeTrue()
        ->and($pins['busy']->closed())->toBeTrue()
        ->and($pins['pwr']->closed())->toBeTrue()
        ->and($spi->closed())->toBeFalse();
});

it('roots its exception at the framework circuit exception', function (): void {
    expect(Spectra6Exception::busyTimeout(1))->toBeInstanceOf(CircuitException::class);
});
