---
type: Runbook
title: Hardware smoke
description: Six colour bands on the Waveshare 4 inch (E) HAT on a Pi, through the real providers and a Surface ePaper framebuffer.
tags: [hardware, smoke, raspberry-pi, epaper]
status: draft
generated: { by: claude-opus/5.5, at: 2026-10-05T05:30:00Z }
---

# Rule

Suite stays hardware-free; panels proven by scratch scripts outside the repo, someone watching.

# Bench

Pi 5 (`fnk`), driver `native`: spidev0.0, DC 25, RST 17, BUSY 24, PWR 18 (enabled).

# Scratch project

This package and Surface `contracts`, `nuts-and-bolts`, `framebuffers` by path repo, `microscrap/scrapyard-linux`, `gpio/digital`, `gpio/spi`, `gpio/i2c`, `venusian-voyager/io-pools`, `venusian-voyager/config`. Boot the GPIO providers, the adapter's and `Spectra6ServiceProvider`, then `conjure('spectra6')`. Announce with `say`.

# Check

400 × 600 frame: six bands top to bottom (black, yellow, red, blue, green, white), white 40 × 40 square in the black band top-left, 4-pixel black border; one refresh (≈ 20 s); `sleep()`. Wrong colours → `Spectra6Ink` codes.
