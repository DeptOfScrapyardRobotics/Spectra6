<?php

namespace DeptOfScrapyardRobotics\Displays\Spectra6;

use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6BoosterSoftStart;
use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6PanelSetting;
use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6PLLControl;
use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6PowerOffSequence;
use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6PowerSetting;
use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6TCON;
use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6VcomDataInterval;

/**
 * The panel's geometry and the values boot and refresh write; the defaults are Waveshare's 4 inch (E) 400 × 600
 * panel (landscape 600 × 400, driven portrait as the controller scans it).
 */
class Spectra6Configuration
{
    protected Spectra6PanelSetting $panel_setting;

    protected Spectra6PowerSetting $power_setting;

    protected Spectra6BoosterSoftStart $booster_soft_start;

    protected Spectra6BoosterSoftStart $refresh_booster;

    protected Spectra6PowerOffSequence $power_off_sequence;

    protected Spectra6TCON $tcon;

    protected Spectra6PLLControl $pll_control;

    protected Spectra6VcomDataInterval $vcom_data_interval;

    /**
     * @param  int  $busy_timeout_ms  longest BUSY may stay low before a command or refresh fails; a refresh takes
     *                                about 30 s
     */
    public function __construct(
        protected int $width = 400,
        protected int $height = 600,
        ?Spectra6PanelSetting $panel_setting = null,
        ?Spectra6PowerSetting $power_setting = null,
        ?Spectra6BoosterSoftStart $booster_soft_start = null,
        ?Spectra6BoosterSoftStart $refresh_booster = null,
        ?Spectra6PowerOffSequence $power_off_sequence = null,
        ?Spectra6TCON $tcon = null,
        ?Spectra6PLLControl $pll_control = null,
        ?Spectra6VcomDataInterval $vcom_data_interval = null,
        protected int $max_packet_size = 4096,
        protected int $busy_timeout_ms = 60_000,
    ) {
        if ($width < 1 || $width > 65535 || $height < 1 || $height > 65535) {
            throw Spectra6Exception::invalidGeometry($width, $height);
        }

        if ($busy_timeout_ms < 1) {
            throw Spectra6Exception::invalidRegisterValue('busy_timeout_ms', $busy_timeout_ms, 1, PHP_INT_MAX);
        }

        $this->panel_setting = $panel_setting ?? new Spectra6PanelSetting;
        $this->power_setting = $power_setting ?? new Spectra6PowerSetting;
        $this->booster_soft_start = $booster_soft_start ?? new Spectra6BoosterSoftStart;
        $this->refresh_booster = $refresh_booster ?? new Spectra6BoosterSoftStart(byte3: 0x27);
        $this->power_off_sequence = $power_off_sequence ?? new Spectra6PowerOffSequence;
        $this->tcon = $tcon ?? new Spectra6TCON;
        $this->pll_control = $pll_control ?? new Spectra6PLLControl;
        $this->vcom_data_interval = $vcom_data_interval ?? new Spectra6VcomDataInterval;
    }

    public function get(string $var): mixed
    {
        if (isset($this->$var)) {
            return $this->$var;
        }

        throw Spectra6Exception::invalidProperty($var, static::class);
    }

    public function set(string $var, mixed $value): void
    {
        if (isset($this->$var)) {
            $this->$var = $value;

            return;
        }

        throw Spectra6Exception::invalidProperty($var, static::class);
    }
}
