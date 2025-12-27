<?php

namespace ScrapyardIO\Displays\ePaper\Spectra6\Concerns;

use ScrapyardIO\Displays\ePaper\Spectra6\Enums\Spectra6Command;

trait Spectra6BootSequence
{
    // Display resolution
    protected int $display_width = 0;
    protected int $display_height = 0;

    // CMDH magic initialization bytes
    protected array $cmdh_magic = [
        0x49,
        0x55,
        0x20,
        0x08,
        0x09,
        0x18
    ];

    // Power setting
    protected int $power_setting = 0x3F;

    // Panel setting register values
    protected int $panel_setting_1 = 0x5F;
    protected int $panel_setting_2 = 0x69;

    // Power setting extended (0x05 command)
    protected array $power_setting_extended = [
        0x40,
        0x1F,
        0x1F,
        0x2C
    ];

    // Power off sequence extended (0x08 command)
    protected array $power_off_sequence_extended = [
        0x6F,
        0x1F,
        0x1F,
        0x22
    ];

    // Booster soft start timing configuration
    protected array $booster_soft_start = [
        0x6F,
        0x1F,
        0x17,
        0x17
    ];

    // Power off sequence
    protected array $power_off_sequence = [
        0x00,
        0x54,
        0x00,
        0x44
    ];

    // TCON setting
    protected array $tcon_setting = [
        0x02,
        0x00
    ];

    // PLL Control
    protected int $pll_control = 0x08;

    // CDI VCOM setting
    protected int $cdi_vcom = 0x3F;

    // Extended config
    protected int $extended_config = 0x2F;

    // Temperature sensor enable
    protected int $temp_sensor = 0x01;

    abstract public function wait(int $ms): void;
    abstract public function sendData(array $bytes): void;
    abstract public function sendCommand(array $bytes): void;
    abstract public function busyWait(int $timeout_ms = 0): bool;

    protected function setCMDH(): void
    {
        $this->sendCommand([
            Spectra6Command::CMDH->value,
            ...$this->cmdh_magic
        ]);
    }

    protected function setPowerSetting(): void
    {
        $this->sendCommand([
            Spectra6Command::POWER_SETTING->value,
            $this->power_setting
        ]);
    }

    protected function setPanelSetting(): void
    {
        $this->sendCommand([
            Spectra6Command::PANEL_SETTING->value,
            $this->panel_setting_1,
            $this->panel_setting_2
        ]);
    }

    protected function setPowerSettingExtended(): void
    {
        $this->sendCommand([
            Spectra6Command::SECRET_SETTING1->value,
            ...$this->power_setting_extended
        ]);
    }

    protected function setPowerOffSequenceExtended(): void
    {
        $this->sendCommand([
            Spectra6Command::SECRET_SETTING2->value,
            ...$this->power_off_sequence_extended
        ]);
    }

    protected function setBoosterSoftStart(): void
    {
        $this->sendCommand([
            Spectra6Command::BOOSTER_SOFT_START->value,
            ...$this->booster_soft_start
        ]);
    }

    protected function setPowerOffSequence(): void
    {
        $this->sendCommand([
            Spectra6Command::POWER_OFF_SEQUENCE->value,
            ...$this->power_off_sequence
        ]);
    }

    protected function setTcon(): void
    {
        $this->sendCommand([
            Spectra6Command::TCON_SETTING->value,
            ...$this->tcon_setting
        ]);
    }

    protected function setPllControl(): void
    {
        $this->sendCommand([
            Spectra6Command::PLL_CONTROL->value,
            $this->pll_control
        ]);
    }

    protected function setCdiVcom(): void
    {
        $this->sendCommand([
            Spectra6Command::CDI_VCOM_SETTING->value,
            $this->cdi_vcom
        ]);
    }

    protected function setResolution(): void
    {
        $this->sendCommand([
            Spectra6Command::RESOLUTION_SETTING->value,
            ($this->display_width >> 8) & 0xFF,   // Width high byte
            $this->display_width & 0xFF,           // Width low byte
            ($this->display_height >> 8) & 0xFF,  // Height high byte
            $this->display_height & 0xFF           // Height low byte
        ]);
    }

    protected function setExtendedConfig(): void
    {
        $this->sendCommand([
            Spectra6Command::SECRET_SETTING6->value,
            $this->extended_config
        ]);
    }

    protected function setTemperatureSensor(): void
    {
        $this->sendCommand([
            Spectra6Command::SECRET_SETTING3->value,
            $this->temp_sensor
        ]);
    }

    protected function powerOn(): void
    {
        $this->sendCommand([Spectra6Command::POWER_ON->value]);
        $this->busyWait(25000);
    }

    protected function powerOff(): void
    {
        $this->sendCommand([
            Spectra6Command::POWER_OFF->value,
            0x00
        ]);
        $this->busyWait(25000);
    }

    protected function deepSleep(): void
    {
        $this->sendCommand([
            Spectra6Command::DEEP_SLEEP->value,
            0xA5
        ]);
    }

    public function display(): static
    {
        $this->startWrite();

        $payload = $this->wire->toRows();

        foreach(array_chunk($payload, $this->max_packet_size) as $chunk)
        {
            $this->sendData($chunk);
        }

        $this->endWrite();

        return $this;
    }

    protected function startWrite(): void
    {
        $this->sendCommand([Spectra6Command::DATA_START_TRANSMIT->value]);
    }

    protected function endWrite(): void
    {
        $this->sendCommand([Spectra6Command::POWER_ON->value]);
        $this->busyWait(25000);
        $this->wait(200);

        $this->sendCommand([
            Spectra6Command::BOOSTER_SOFT_START->value,
            0x6F,
            0x1F,
            0x17,
            0x27
        ]);
        $this->wait(200);

        $this->sendCommand([
            Spectra6Command::DISPLAY_REFRESH->value,
            0x00
        ]);
        $this->busyWait(25000);
        $this->wait(200);

        $this->sendCommand([
            Spectra6Command::POWER_OFF->value,
            0x00
        ]);
        $this->busyWait(25000);
        $this->wait(200);
    }
}
