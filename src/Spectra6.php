<?php

namespace DeptOfScrapyardRobotics\Displays\Spectra6;

use DeptOfScrapyardRobotics\Displays\Spectra6\Concerns\ConjuresSpectra6;
use DeptOfScrapyardRobotics\Displays\Spectra6\Concerns\Spectra6Bootstrap;
use DeptOfScrapyardRobotics\Displays\Spectra6\Enums\Spectra6Ink;
use DeptOfScrapyardRobotics\Displays\Spectra6\Enums\Spectra6OpCode;
use DeptOfScrapyardRobotics\Displays\Spectra6\Transports\Spectra6DataTransport;
use GeneralPurposeIO\Contracts\IntegratedCircuits\DisplayPanel;
use GeneralPurposeIO\Contracts\IntegratedCircuits\RefreshesOnCommand;
use GeneralPurposeIO\Contracts\IntegratedCircuits\RefreshMode;
use GeneralPurposeIO\IntegratedCircuits\Bootable;
use Surface\Contracts\Framebuffers\BitDepth;
use Surface\Contracts\Framebuffers\BitOrder;
use Surface\Contracts\Framebuffers\ChannelPalette;
use Surface\Contracts\Framebuffers\ChannelSpec;
use Surface\Contracts\Framebuffers\EInkColor;
use Surface\Contracts\Framebuffers\FormatSpec;
use Surface\Contracts\Framebuffers\PixelFormat;
use Surface\Contracts\Framebuffers\ScanDirection;

/**
 * An E Ink Spectra 6 panel: black, white, yellow, red, blue and green. One RAM holds the frame at four bits a pixel,
 * two pixels to a byte with the left one in the high nibble. The panel takes whole frames only: it is not window
 * addressable, and it has one full refresh, which powers the panel on, refreshes and powers it off again.
 */
class Spectra6 extends Bootable implements DisplayPanel, RefreshesOnCommand
{
    use ConjuresSpectra6;
    use Spectra6Bootstrap;

    protected FormatSpec $format_spec;

    public function __construct(
        protected readonly Spectra6DataTransport $transport,
        protected Spectra6Configuration $props = new Spectra6Configuration,
        bool $boot_now = false,
    ) {
        $this->format_spec = $this->generateFormatSpec();

        parent::__construct($boot_now);
    }

    public function width(): int
    {
        return $this->props->get('width');
    }

    public function height(): int
    {
        return $this->props->get('height');
    }

    public function transport(): Spectra6DataTransport
    {
        return $this->transport;
    }

    public function config(): Spectra6Configuration
    {
        return $this->props;
    }

    public function formatSpec(): FormatSpec
    {
        return $this->format_spec;
    }

    public function setFormatSpec(FormatSpec $format_spec): void
    {
        $this->format_spec = $format_spec;
    }

    /** Packed four-bit palette codes, rows padded to a byte: what a Surface ePaper framebuffer in this spec stores. */
    public function generateFormatSpec(): FormatSpec
    {
        return new FormatSpec(
            PixelFormat::ROW_MAJOR,
            BitDepth::B4,
            ScanDirection::TOP_TO_BOTTOM,
            BitOrder::MSB_FIRST,
            palette: new ChannelPalette(
                new ChannelSpec(EInkColor::BLACK->value, code: Spectra6Ink::BLACK->value),
                new ChannelSpec(EInkColor::WHITE->value, code: Spectra6Ink::WHITE->value),
                new ChannelSpec(EInkColor::YELLOW->value, code: Spectra6Ink::YELLOW->value),
                new ChannelSpec(EInkColor::RED->value, code: Spectra6Ink::RED->value),
                new ChannelSpec(EInkColor::BLUE->value, code: Spectra6Ink::BLUE->value),
                new ChannelSpec(EInkColor::GREEN->value, code: Spectra6Ink::GREEN->value),
            ),
        );
    }

    /**
     * Write a whole frame packed per formatSpec(): rows of ceil(width / 2) bytes. The panel shows it on refresh().
     *
     * @param  list<int>  $raw_data
     *
     * @throws Spectra6Exception for anything but the whole panel, or the wrong byte count
     */
    public function transmit(int $origin_x, int $origin_y, array $raw_data, ?int $frame_width = null, ?int $frame_height = null): void
    {
        $width = $frame_width ?? $this->width();
        $height = $frame_height ?? $this->height();

        if ($origin_x !== 0 || $origin_y !== 0 || $width !== $this->width() || $height !== $this->height()) {
            throw Spectra6Exception::wholeFrameOnly($origin_x, $origin_y, $width, $height, $this->width(), $this->height());
        }

        $expected = intdiv($width + 1, 2) * $height;

        if (count($raw_data) !== $expected) {
            throw Spectra6Exception::wrongByteCount($expected, count($raw_data));
        }

        $this->sendCommand(Spectra6OpCode::DATA_START_TRANSMISSION, array_values($raw_data));
    }

    /**
     * Power on, the refresh booster setting, display refresh (0x12 0x00), power off; BUSY is waited out after each
     * step. FULL only.
     */
    public function refresh(RefreshMode $mode = RefreshMode::FULL): void
    {
        if ($mode !== RefreshMode::FULL) {
            throw Spectra6Exception::unsupportedRefreshMode($mode);
        }

        $this->powerOn();
        $this->sendCommand(Spectra6OpCode::BOOSTER_SOFT_START, $this->config()->get('refresh_booster')->toBytes());
        usleep(200_000);
        $this->sendCommand(Spectra6OpCode::DISPLAY_REFRESH, [0x00]);
        $this->waitUntilIdle();
        $this->powerOff();
    }

    /** Release DC, RST, BUSY and PWR. The bus belongs to its driver and stays open; the panel keeps its image. */
    public function close(): void
    {
        $this->transport->close();
    }
}
