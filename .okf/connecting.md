---
type: Guide
title: Connecting a Spectra 6 panel
description: conjure() and the spi() factory, DC / RST / BUSY / PWR, the Waveshare HAT pinout, building the transport by hand.
tags: [spi, transport, dc, rst, busy, pwr, conjure]
status: draft
generated: { by: claude-opus/5.5, at: 2026-10-05T05:30:00Z }
sources:
  - id: factory
    resource: src/Concerns/ConjuresSpectra6.php
    title: ConjuresSpectra6
  - id: config
    resource: https://files.waveshare.com/wiki/4inch-e-Paper-HAT%2B-(E)/4inch_e-Paper_E.zip
    title: Waveshare epdconfig.py
---

# spi()

`Spectra6::spi(driver, device, dc, rst, busy, pwr = [], chip_select = 0, mode = 0, speed = 10_000_000, width = null, height = null, boot_now = true)`[^factory] — mode 0–3 and speed ≥ 1 checked before the bus; unconnected bus opened in `mode`, shared bus in another mode refused; chip select clocked at `speed`; DC, RST, BUSY, then PWR when `enabled`. `conjure('spectra6')` passes `circuits.spectra6` keys.

# Waveshare 4 inch (E) HAT

From the vendor's config:[^config] RST GPIO17, DC GPIO25, CS CE0 (spidev0.0), BUSY GPIO24, PWR GPIO18 (high to power the panel). 600 × 400 landscape; the controller scans 400 wide × 600 tall.

# By hand

```php
$spi = app('gpio.spi')->driver('native')->connectTo(0)->mode(0)->speed(10_000_000)->register()->device(0, 0);
$pins = app('gpio.digital')->driver('native')->connectTo(0)->register();
$panel = new Spectra6(new Spectra6SPITransport($spi, $pins->output(0, 25), $pins->output(0, 17), $pins->input(0, 24), $pins->output(0, 18)), boot_now: true);
```

[^factory]: ConjuresSpectra6
[^config]: Waveshare epdconfig.py
