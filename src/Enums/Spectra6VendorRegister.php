<?php

namespace DeptOfScrapyardRobotics\Displays\Spectra6\Enums;

/**
 * The vendor-tuned registers Waveshare's driver writes, with their fixed payloads. Their meaning is not published.
 * CMDH (0xAA) comes first after reset.
 */
enum Spectra6VendorRegister: int
{
    case CMDH = 0xAA;
    case POWER_SETTING_EXTENDED = 0x05;
    case POWER_OFF_SEQUENCE_EXTENDED = 0x08;
    case TEMPERATURE_SENSOR_ENABLE = 0x84;
    case POWER_SAVING = 0xE3;

    /** @return list<int> */
    public function payload(): array
    {
        return match ($this) {
            self::CMDH => [0x49, 0x55, 0x20, 0x08, 0x09, 0x18],
            self::POWER_SETTING_EXTENDED => [0x40, 0x1F, 0x1F, 0x2C],
            self::POWER_OFF_SEQUENCE_EXTENDED => [0x6F, 0x1F, 0x1F, 0x22],
            self::TEMPERATURE_SENSOR_ENABLE => [0x01],
            self::POWER_SAVING => [0x2F],
        };
    }
}
