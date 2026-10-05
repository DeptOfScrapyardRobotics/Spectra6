<?php

use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6BoosterSoftStart;
use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6PanelSetting;
use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6PowerOffSequence;
use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6TCON;
use DeptOfScrapyardRobotics\Displays\Spectra6\Enums\Spectra6VendorRegister;
use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6Exception;

it('carries the vendor values', function (): void {
    expect((new Spectra6PanelSetting)->toBytes())->toBe([0x5F, 0x69])
        ->and((new Spectra6PowerOffSequence)->toBytes())->toBe([0x00, 0x54, 0x00, 0x44])
        ->and((new Spectra6BoosterSoftStart)->toBytes())->toBe([0x6F, 0x1F, 0x17, 0x17])
        ->and(Spectra6TCON::fromBytes([0x03, 0x04])->toBytes())->toBe([0x03, 0x04])
        ->and(Spectra6VendorRegister::CMDH->payload())->toBe([0x49, 0x55, 0x20, 0x08, 0x09, 0x18]);
});

it('refuses register values out of range instead of masking them', function (callable $build, string $message): void {
    expect($build)->toThrow(Spectra6Exception::class, $message);
})->with([
    'panel' => [fn () => new Spectra6PanelSetting(byte0: 0x100), 'byte0 takes 0 to 255; got 256'],
    'tcon' => [fn () => new Spectra6TCON(g2s: -1), 'g2s takes 0 to 255; got -1'],
    'booster' => [fn () => new Spectra6BoosterSoftStart(byte3: 300), 'byte3 takes 0 to 255'],
]);
