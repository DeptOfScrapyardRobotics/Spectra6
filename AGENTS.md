# Agent guidelines — dept-of-scrapyard-robotics/spectra6

## Knowledge Bundle (OKF)

This package ships an Open Knowledge Format bundle at [`.okf/`](.okf/) (excluded from the Composer dist via `.gitattributes` `export-ignore`). Before changing code or advising on this package: read [`.okf/index.md`](.okf/index.md) first, open only the concepts the task needs, prefer `status: stable` over `draft`. When you learn something durable, update the affected concept(s), bump `generated.at`, and append [`.okf/log.md`](.okf/log.md); new or changed concepts stay `status: draft` until a human verifies them. The bundle documents the package, never a session.

Do **not** create `.okf` folders under `src/*`. Catalog, transport and adapter semantics belong to `scrapyard-io/framework`'s bundle, framebuffer packing to Surface's.

## Package rules (quick) — 0.10.x

- Composer: `dept-of-scrapyard-robotics/spectra6` **0.10.0**. PHP `^8.4|^8.5|^8.6`. Namespace `DeptOfScrapyardRobotics\Displays\Spectra6\` → `src/`.
- **Requires split components only**: `gpio/contracts`, `gpio/integrated-circuits`, `venusian-surface/contracts`, `venusian-voyager/nuts-and-bolts`, `venusian-voyager/vessel`. Never `scrapyard-io/framework`, `venusian/surface` or `venusian/framework`.
- **Panel = `Bootable` + `DisplayPanel` + `RefreshesOnCommand`.** Not `WindowAddressable` (whole frames only), not `Switchable`, full refresh only. Keep the three `formatSpec` methods as plain methods (Surface 0.10 has no `FormatSpecification`).
- **Boot** is the reference bring-up in its order, CMDH (`0xAA`) first; vendor registers carry fixed payloads in `Spectra6VendorRegister`. BUSY is active low.
- **Frames** are four-bit palette codes (`Spectra6Ink`), ceil(w/2) bytes a row. **Refresh** = power on, refresh booster, `12 00`, power off; every BUSY wait settles 200 ms.
- **The factory is the config shape.** `ConjuresSpectra6::spi()` parameters are exactly a `circuits.spectra6.configs.*` entry's keys. Bus first, then DC, RST, BUSY, PWR (when enabled).
- **Every write is checked; every BUSY wait has a timeout.** Breakouts range-check, never mask.
- Enums int-backed, FULLY UPPERCASE cases. No class constants. `is_null($x)` over `$x === null`.

## Verification

```bash
vendor/bin/pest            # recording fake bus, pins and scripted BUSY; no hardware
```

Run it under NTS and ZTS PHP before every commit. Suites stay hardware-free. Hardware truth: Waveshare's 4 inch (E) HAT (400 × 600 scan) on a Raspberry Pi 5 — spidev0.0, DC 25, RST 17, BUSY 24, PWR 18 — proven by watching six colour bands.
