<?php

namespace DeptOfScrapyardRobotics\Displays\Spectra6\Concerns;

use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6BoosterSoftStart;
use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6PanelSetting;
use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6PLLControl;
use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6PowerOffSequence;
use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6PowerSetting;
use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6TCON;
use DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts\Spectra6VcomDataInterval;
use DeptOfScrapyardRobotics\Displays\Spectra6\Enums\Spectra6DeepSleepCheck;
use DeptOfScrapyardRobotics\Displays\Spectra6\Enums\Spectra6OpCode;
use DeptOfScrapyardRobotics\Displays\Spectra6\Enums\Spectra6VendorRegister;
use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6Configuration;
use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6Exception;
use DeptOfScrapyardRobotics\Displays\Spectra6\Transports\Spectra6DataTransport;

/** Register setters: each writes the chip, then the configuration. */
trait Spectra6API
{
    abstract public function config(): Spectra6Configuration;

    abstract public function transport(): Spectra6DataTransport;

    protected function sendCommand(Spectra6OpCode $register, array $command_data = []): int
    {
        return $this->transport()->command($register->value, $command_data);
    }

    /** One vendor register with its fixed payload. */
    public function writeVendorRegister(Spectra6VendorRegister $register): void
    {
        $this->transport()->command($register->value, $register->payload());
    }

    /** @throws Spectra6Exception when BUSY stays low past busy_timeout_ms */
    public function waitUntilIdle(): void
    {
        $this->transport()->waitUntilIdle($this->config()->get('busy_timeout_ms'));
    }

    public function setPanelSetting(Spectra6PanelSetting $setting): void
    {
        $this->sendCommand(Spectra6OpCode::PANEL_SETTING, $setting->toBytes());
        $this->config()->set('panel_setting', $setting);
    }

    public function setPowerSetting(Spectra6PowerSetting $setting): void
    {
        $this->sendCommand(Spectra6OpCode::POWER_SETTING, $setting->toBytes());
        $this->config()->set('power_setting', $setting);
    }

    public function setBoosterSoftStart(Spectra6BoosterSoftStart $booster): void
    {
        $this->sendCommand(Spectra6OpCode::BOOSTER_SOFT_START, $booster->toBytes());
        $this->config()->set('booster_soft_start', $booster);
    }

    public function setPowerOffSequence(Spectra6PowerOffSequence $sequence): void
    {
        $this->sendCommand(Spectra6OpCode::POWER_OFF_SEQUENCE, $sequence->toBytes());
        $this->config()->set('power_off_sequence', $sequence);
    }

    public function setTCON(Spectra6TCON $tcon): void
    {
        $this->sendCommand(Spectra6OpCode::TCON, $tcon->toBytes());
        $this->config()->set('tcon', $tcon);
    }

    public function setPLLControl(Spectra6PLLControl $pll): void
    {
        $this->sendCommand(Spectra6OpCode::PLL_CONTROL, $pll->toBytes());
        $this->config()->set('pll_control', $pll);
    }

    public function setVcomDataInterval(Spectra6VcomDataInterval $interval): void
    {
        $this->sendCommand(Spectra6OpCode::VCOM_DATA_INTERVAL, $interval->toBytes());
        $this->config()->set('vcom_data_interval', $interval);
    }

    /** Resolution (TRES, 0x61): sources then gates, each 16-bit big-endian. */
    public function setResolution(int $width, int $height): void
    {
        $this->sendCommand(Spectra6OpCode::RESOLUTION_SETTING, [($width >> 8) & 0xFF, $width & 0xFF, ($height >> 8) & 0xFF, $height & 0xFF]);
    }

    public function powerOn(): void
    {
        $this->sendCommand(Spectra6OpCode::POWER_ON);
        $this->waitUntilIdle();
    }

    public function powerOff(): void
    {
        $this->sendCommand(Spectra6OpCode::POWER_OFF, [0x00]);
        $this->waitUntilIdle();
    }

    /**
     * Deep sleep. The panel keeps showing its image; only a hardware reset wakes the controller, so the next boot()
     * runs the whole sequence again. Each refresh already ends powered off.
     */
    public function sleep(): void
    {
        $this->sendCommand(Spectra6OpCode::DEEP_SLEEP, [Spectra6DeepSleepCheck::CODE->value]);
        $this->booted = false;
    }
}
