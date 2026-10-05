---
type: Package
title: dept-of-scrapyard-robotics/spectra6
description: Spectra 6 ePaper driver for scrapyard-io/framework 0.10 — identity, requires, boot, errors.
resource: composer.json
tags: [spectra6, e6, epaper, e-ink, display, spi, package]
status: draft
generated: { by: claude-opus/5.5, at: 2026-10-05T05:30:00Z }
sources:
  - id: panel
    resource: src/Spectra6.php
    title: Spectra6
  - id: bootstrap
    resource: src/Concerns/Spectra6Bootstrap.php
    title: Spectra6Bootstrap
  - id: vendor
    resource: https://files.waveshare.com/wiki/4inch-e-Paper-HAT%2B-(E)/4inch_e-Paper_E.zip
    title: Waveshare 4inch_e-Paper_E (epd4in0e.py)
  - id: old
    resource: PSS/Extensions/Embedded/Spectra6 (0.1.0)
    title: 0.1.0 package
---

# Identity

`dept-of-scrapyard-robotics/spectra6` **0.10.0**, alias `dev-main` → `0.10.x-dev`, namespace `DeptOfScrapyardRobotics\Displays\Spectra6`, provider `Providers\Spectra6ServiceProvider`, catalog slug `spectra6`. New in 0.10, from the 0.1.0 package (7.3 inch sequence)[^old] and Waveshare's 4 inch (E) driver.[^vendor] The scaffold's `ScrapyardIO\Displays\ePaper\Spectra6` namespace was replaced with the DOSR one.

Requires split components only: `gpio/contracts`, `gpio/integrated-circuits`, `venusian-surface/contracts`, `venusian-voyager/nuts-and-bolts`, `venusian-voyager/vessel`.

# Shape

`Spectra6` = `Bootable` + `DisplayPanel` + `RefreshesOnCommand`.[^panel] Not `WindowAddressable`, not `Switchable`. Full refresh only.

# Boot

PWR on → RST high 20 / low 2 / high 20 ms → BUSY (+200 ms settle) → 30 ms → `AA 49 55 20 08 09 18` → `01 3F` → `00 5F 69` → `05 40 1F 1F 2C` → `08 6F 1F 1F 22` → `06 6F 1F 17 17` → `03 00 54 00 44` → `60 02 00` → `30 08` → `50 3F` → `61 01 90 02 58` → `E3 2F` → `84 01` → BUSY.[^bootstrap] BUSY low = busy. Pi: 485 ms.

# Errors

`Spectra6Exception` → `CircuitException`: `busyTimeout`, `spiWriteFailed`, `wholeFrameOnly`, `wrongByteCount`, `unsupportedRefreshMode`, `invalidGeometry`, `invalidRegisterValue`, `invalidProperty`, `notConnected`, `incompletePin`, `invalidSpiMode`, `invalidSpiClock`, `wrongSpiMode`, `invalidPacketSize`.

[^panel]: Spectra6
[^bootstrap]: Spectra6Bootstrap
[^vendor]: Waveshare 4inch_e-Paper_E (epd4in0e.py)
[^old]: 0.1.0 package
