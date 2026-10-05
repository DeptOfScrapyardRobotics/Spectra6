<?php

namespace DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts;

use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6Exception;

/**
 * Panel Setting (PSR, 0x00), two bytes. Defaults (0x5F, 0x69) from Waveshare's 4 inch (E) driver.
 */
readonly class Spectra6PanelSetting
{
    public function __construct(
        public int $byte0 = 0x5F,
        public int $byte1 = 0x69,
    ) {
        foreach (['byte0' => $byte0, 'byte1' => $byte1] as $field => $value) {
            if ($value < 0 || $value > 0xFF) {
                throw Spectra6Exception::invalidRegisterValue($field, $value, 0, 0xFF);
            }
        }
    }

    /** @return list<int> */
    public function toBytes(): array
    {
        return [$this->byte0, $this->byte1];
    }

    /** @param  list<int>  $bytes */
    public static function fromBytes(array $bytes): static
    {
        return new static(...array_values($bytes));
    }
}
