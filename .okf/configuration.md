---
type: Configuration
title: Configuration
description: Spectra6Configuration, register breakouts, vendor registers, circuits.spectra6 keys.
resource: config/spectra6.php
tags: [config, configuration, registers]
status: draft
generated: { by: claude-opus/5.5, at: 2026-10-05T05:30:00Z }
sources:
  - id: config
    resource: src/Spectra6Configuration.php
    title: Spectra6Configuration
  - id: vendor
    resource: src/Enums/Spectra6VendorRegister.php
    title: Spectra6VendorRegister
---

# Spectra6Configuration

`width` 400, `height` 600, `panel_setting` (5F 69), `power_setting` (3F), `booster_soft_start` (6F 1F 17 17), `refresh_booster` (6F 1F 17 27, written each refresh), `power_off_sequence` (00 54 00 44), `tcon` (02 00), `pll_control` (08), `vcom_data_interval` (3F), `max_packet_size` 4096, `busy_timeout_ms` 60 000.[^config] Breakouts range-check every byte. Properties: any key reads; register keys write chip then config; `refresh_booster` writes config only.

Vendor registers (meaning unpublished), fixed payloads in `Spectra6VendorRegister`:[^vendor] AA (CMDH, first), 05, 08, E3, 84.

# Config file

`config/spectra6.php` → `circuits.spectra6`, tag `spectra6-config`, catalog `spectra6`. Keys: `default_config` 'spi'; `driver`, `device`, `chip_select`, `mode`, `speed`, `width`, `height`, `dc` / `rst` / `busy` (pins 25 / 17 / 24), `pwr` (`enabled` false, pin 18), `boot_now`.

[^config]: Spectra6Configuration
[^vendor]: Spectra6VendorRegister
