# spectra6

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dept-of-scrapyard-robotics/spectra6.svg)](https://packagist.org/packages/dept-of-scrapyard-robotics/spectra6)
[![License](https://img.shields.io/packagist/l/dept-of-scrapyard-robotics/spectra6.svg)](LICENSE)

Drive E Ink Spectra 6 ePaper panels (black, white, yellow, red, blue, green) from PHP over SPI, using the ScrapyardIO GPIO framework.

`dept-of-scrapyard-robotics/spectra6` powers and boots the controller with the panel vendor's sequence, takes whole frames packed as four-bit colour codes, and refreshes the panel. A Surface ePaper framebuffer created with the panel's `formatSpec()` stores frames in exactly that packing.

```
ext-posi / ext-ftdi            1:1 system and libftdi calls
  → microscrap/*               libgpiod, spidev, libmpsse in PHP
    → microscrap/scrapyard-*   adapters: the `native` and `usb` drivers
      → scrapyard-io/framework protocol managers, transports, the circuit catalog
        → dept-of-scrapyard-robotics/spectra6   ← this package
```

## Requirements

- PHP 8.4 or newer
- A Venusian 0.10 application with the `scrapyard-io/framework` 0.10 components (`gpio/spi`, `gpio/digital`, `gpio/integrated-circuits`)
- `venusian-surface/contracts` 0.10, for the `FormatSpec` the panel describes its bytes with
- An adapter: `microscrap/scrapyard-linux` (driver `native`, needs `ext-posi`) or `microscrap/scrapyard-usb` (driver `usb`, needs `ext-ftdi`)
- `venusian-surface/framebuffers` 0.10 if you want Surface to pack your frames

## Installation

```bash
composer require dept-of-scrapyard-robotics/spectra6
```

The service provider is discovered automatically. It merges the wiring config under `circuits.spectra6` and registers the panel with the circuit catalog. To publish the config, run:

```bash
php computer vendor:publish --tag=spectra6-config
```

## Quick start

Waveshare's 4 inch e-Paper HAT+ (E), 600 × 400, on a Raspberry Pi: DC on GPIO25, RST on GPIO17, BUSY on GPIO24, chip select on CE0, and a power switch on GPIO18.

```php
// config/circuits/spectra6.php
return [
    'default_config' => 'spi',
    'configs' => [
        'spi' => [
            'driver' => 'native',
            'device' => 0,
            'chip_select' => 0,
            'dc' => ['driver' => 'native', 'device' => 0, 'pin' => 25],
            'rst' => ['driver' => 'native', 'device' => 0, 'pin' => 17],
            'busy' => ['driver' => 'native', 'device' => 0, 'pin' => 24],
            'pwr' => ['enabled' => true, 'driver' => 'native', 'device' => 0, 'pin' => 18],
        ],
    ],
];
```

```php
use Surface\Framebuffers\Native\NativeFramebufferDriver;

$panel = app('circuit')->conjure('spectra6');   // powered, reset and booted

$spec = $panel->formatSpec();
$fb = (new NativeFramebufferDriver)->epaper($spec, $panel->width(), $panel->height());   // starts white
$fb->writeRgba8($rgba, $panel->width(), $panel->height());   // the six inks

$panel->transmit(0, 0, $fb->flush($spec, true));
$panel->refresh();   // about 20 seconds
```

The controller scans the panel 400 wide and 600 tall, so draw portrait or rotate a landscape image. On a Raspberry Pi 5, boot takes about 0.5 s, sending a frame about 0.1 s, and a refresh about 20 s.

## Connecting

`conjure('spectra6')` reads `circuits.spectra6`, picks `default_config`, and calls `Spectra6::spi()` with that entry's keys. You can call it directly:

```php
use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6;

$panel = Spectra6::spi(
    'native', 0,
    dc: ['driver' => 'native', 'device' => 0, 'pin' => 25],
    rst: ['driver' => 'native', 'device' => 0, 'pin' => 17],
    busy: ['driver' => 'native', 'device' => 0, 'pin' => 24],
    pwr: ['enabled' => true, 'driver' => 'native', 'device' => 0, 'pin' => 18],
);
```

A bus or pin device that isn't connected yet is connected by the factory; one your app already connected is shared. The factory opens an unconnected bus in `mode` (0 by default), refuses a bus already open in a different mode, and clocks this chip select at `speed` (10 MHz by default). DC, RST, BUSY and PWR are opened after the bus. Leave `pwr` disabled when the panel has no power switch. Pass `width` and `height` for another panel size, and `boot_now: false` to build without booting.

### Building the transport yourself

```php
use DeptOfScrapyardRobotics\Displays\Spectra6\Transports\Spectra6SPITransport;

$spi = app('gpio.spi')->driver('native')->connectTo(0)->mode(0)->speed(10_000_000)->register()->device(0, 0);
$pins = app('gpio.digital')->driver('native')->connectTo(0)->register();

$panel = new Spectra6(new Spectra6SPITransport($spi, $pins->output(0, 25), $pins->output(0, 17), $pins->input(0, 24), $pins->output(0, 18)), boot_now: true);
```

## Drawing

The panel's RAM holds four bits a pixel, two pixels to a byte with the left one in the high nibble. The colour codes are black `0`, white `1`, yellow `2`, red `3`, blue `5`, green `6` (`Spectra6Ink`).

`transmit()` takes whole frames only: origin (0, 0) and the full width and height, ceil(width / 2) bytes a row. The panel doesn't change until `refresh()`.

## Refreshing and sleep

```php
$panel->refresh();   // power on, refresh, power off; blocks until done
$panel->sleep();     // deep sleep; the image stays
$panel->boot();      // wakes it with a hardware reset
```

Each refresh powers the panel's drivers on, writes the refresh booster setting, refreshes, and powers off again. The panel has no partial refresh: `refresh(RefreshMode::PARTIAL)` throws. BUSY reads low while the panel works, and every wait lets it settle 200 ms after BUSY rises, as the vendor does; if BUSY stays low past `busy_timeout_ms` (60 s by default) the call throws.

## Configuration object

`Spectra6Configuration` holds the geometry and the values boot and refresh write; the defaults are Waveshare's.

| Argument | Default |
|---|---|
| `width`, `height` | `400`, `600` |
| `panel_setting` | `0x5F 0x69` |
| `power_setting` | `0x3F` |
| `booster_soft_start` | `0x6F 0x1F 0x17 0x17` (boot) |
| `refresh_booster` | `0x6F 0x1F 0x17 0x27` (each refresh) |
| `power_off_sequence` | `0x00 0x54 0x00 0x44` |
| `tcon` | `0x02 0x00` |
| `pll_control` | `0x08` |
| `vcom_data_interval` | `0x3F` |
| `max_packet_size` | `4096` |
| `busy_timeout_ms` | `60000` |

Each register is a breakout in `Breakouts\` that checks every byte. Any configuration key reads as a property, and the register keys can be written, reaching the chip straight away (`refresh_booster` is written at the next refresh). The vendor registers the bring-up also writes (`0xAA`, `0x05`, `0x08`, `0xE3`, `0x84`) have fixed values in `Spectra6VendorRegister`.

## Errors

Everything throws `Spectra6Exception`, which descends from `GeneralPurposeIO\Contracts\IntegratedCircuits\CircuitException`: BUSY timeouts, failed writes, `transmit()` with anything but a whole frame or the wrong byte count, a partial refresh, register values out of range, a size the resolution register can't hold, unknown properties, and SPI or pin settings it can't use. SPI has no acknowledge, so a missing panel shows up as a BUSY timeout.

## Closing

`close()` releases DC, RST, BUSY and PWR. The SPI connection stays with its driver, and the panel keeps its image.

## Configuration file

`config/circuits/spectra6.php`: `default_config` (`'spi'`); `configs.spi.driver`, `device`, `chip_select` (0), `mode` (0), `speed` (10 MHz), `width` / `height` (null keeps 400 × 600), `dc` / `rst` / `busy` (`driver`, `device`, `pin`; pins 25 / 17 / 24), `pwr` (`enabled` false, pin 18), and `boot_now` (true).

## Testing

```bash
composer install
vendor/bin/pest
```

The suite runs against recording fakes of the SPI bus, the pins and a scripted BUSY line, so it needs no hardware. Waveshare's 4 inch (E) HAT on a Raspberry Pi 5 was also exercised for this release, with someone watching: six colour bands, a white marker and a black border after a 20-second refresh.

## Security

The driver writes commands and image data to hardware the PHP process can open. See [SECURITY.md](SECURITY.md).

## License

MIT. See [LICENSE](LICENSE).
