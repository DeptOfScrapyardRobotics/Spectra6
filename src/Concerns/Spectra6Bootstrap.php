<?php

namespace DeptOfScrapyardRobotics\Displays\Spectra6\Concerns;

use DeptOfScrapyardRobotics\Displays\Spectra6\Enums\Spectra6VendorRegister;
use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6Exception;

trait Spectra6Bootstrap
{
    use Spectra6API;

    /**
     * Any configuration key reads as a property.
     *
     * @throws Spectra6Exception
     */
    public function __get(string $name): mixed
    {
        return $this->config()->get($name);
    }

    /**
     * @throws Spectra6Exception
     */
    public function __set(string $name, mixed $value): void
    {
        match ($name) {
            'panel_setting' => $this->setPanelSetting($value),
            'power_setting' => $this->setPowerSetting($value),
            'booster_soft_start' => $this->setBoosterSoftStart($value),
            'refresh_booster' => $this->config()->set('refresh_booster', $value),
            'power_off_sequence' => $this->setPowerOffSequence($value),
            'tcon' => $this->setTCON($value),
            'pll_control' => $this->setPLLControl($value),
            'vcom_data_interval' => $this->setVcomDataInterval($value),
            default => throw Spectra6Exception::invalidProperty($name, static::class),
        };
    }

    /**
     * Waveshare's bring-up, in its order: power, reset, wait, CMDH unlock, power / panel / booster / timing
     * registers, resolution, power saving, temperature sensor.
     */
    protected function _boot(): void
    {
        $config = $this->config();

        $this->transport()->maxPacketSize($config->get('max_packet_size'));
        $this->transport()->power(true);
        $this->transport()->reset();
        $this->waitUntilIdle();
        usleep(30_000);

        $this->writeVendorRegister(Spectra6VendorRegister::CMDH);
        $this->setPowerSetting($config->get('power_setting'));
        $this->setPanelSetting($config->get('panel_setting'));
        $this->writeVendorRegister(Spectra6VendorRegister::POWER_SETTING_EXTENDED);
        $this->writeVendorRegister(Spectra6VendorRegister::POWER_OFF_SEQUENCE_EXTENDED);
        $this->setBoosterSoftStart($config->get('booster_soft_start'));
        $this->setPowerOffSequence($config->get('power_off_sequence'));
        $this->setTCON($config->get('tcon'));
        $this->setPLLControl($config->get('pll_control'));
        $this->setVcomDataInterval($config->get('vcom_data_interval'));
        $this->setResolution($this->width(), $this->height());
        $this->writeVendorRegister(Spectra6VendorRegister::POWER_SAVING);
        $this->writeVendorRegister(Spectra6VendorRegister::TEMPERATURE_SENSOR_ENABLE);
        $this->waitUntilIdle();
    }
}
