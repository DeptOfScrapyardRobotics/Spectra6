---
okf_version: "0.2"
---

# dept-of-scrapyard-robotics/spectra6

E Ink Spectra 6 ePaper driver (black, white, yellow, red, blue, green) for `scrapyard-io/framework` 0.10, over SPI with DC, RST, BUSY and an optional PWR line. Conjured from config, whole frames of four-bit palette codes, full refresh.

Read this index first, open only concepts task needs. Every concept `status: draft` until human verifies.

# Concepts

* [Package](overview.md) - Spectra 6 ePaper driver for scrapyard-io/framework 0.10 — identity, requires, boot, errors.
* [Connecting](connecting.md) - conjure() and the spi() factory, DC / RST / BUSY / PWR, the Waveshare HAT pinout, by hand.
* [Drawing and refreshing](drawing.md) - the four-bit FormatSpec, transmit(), refresh() sequence, sleep, timings.
* [Configuration](configuration.md) - Spectra6Configuration, register breakouts, vendor registers, config keys.

# Runbooks

* [Hardware smoke](runbooks/hardware-smoke.md) - six colour bands on the Waveshare 4 inch (E) HAT on a Pi.

# Log

* [log.md](log.md)
