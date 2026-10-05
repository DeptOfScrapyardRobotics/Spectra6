---
type: Guide
title: Drawing and refreshing
description: The four-bit FormatSpec, transmit(), the refresh sequence, sleep, timings.
tags: [drawing, formatspec, transmit, refresh, epaper]
status: draft
generated: { by: claude-opus/5.5, at: 2026-10-05T05:30:00Z }
sources:
  - id: panel
    resource: src/Spectra6.php
    title: Spectra6
  - id: ink
    resource: src/Enums/Spectra6Ink.php
    title: Spectra6Ink
---

# FormatSpec

`ROW_MAJOR`, `B4`, `MSB_FIRST`, palette with codes: BLACK 0, WHITE 1, YELLOW 2, RED 3, BLUE 5, GREEN 6.[^ink] Two pixels a byte, left in the high nibble, rows padded to a byte. Surface's `epaper()` framebuffer in this spec stores exactly that and starts as paper (`11`); checked: `K W Y R B G` → `01 23 56`.

# transmit()

Whole frames only (origin 0, 0; full size), else `wholeFrameOnly`; bytes = ceil(w / 2) × h; `10` + frame.[^panel]

# refresh() / sleep()

`refresh()`: `04` + BUSY → `06` refresh booster (`6F 1F 17 27`) → 200 ms → `12 00` + BUSY → `02 00` + BUSY. Every BUSY wait settles 200 ms after BUSY rises, as the vendor does. PARTIAL → `unsupportedRefreshMode`. `sleep()` → `07 A5`; next `boot()` resets.

# Live reference

Pi 5, 10 MHz, 400 × 600: frame built and packed through Surface 377 ms (120 000 bytes), transmit 107 ms, refresh 20.1 s; six colour bands, white marker and black border as drawn.

[^panel]: Spectra6
[^ink]: Spectra6Ink
