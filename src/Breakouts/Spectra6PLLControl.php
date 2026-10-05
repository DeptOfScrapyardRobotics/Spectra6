<?php

namespace DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts;

use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6Exception;

/**
 * PLL Control (0x30), one byte: the frame rate. Default 0x08 from Waveshare's 4 inch (E) driver.
 */
readonly class Spectra6PLLControl
{
    public function __construct(
        public int $frame_rate = 0x08,
    ) {
        foreach (['frame_rate' => $frame_rate] as $field => $value) {
            if ($value < 0 || $value > 0xFF) {
                throw Spectra6Exception::invalidRegisterValue($field, $value, 0, 0xFF);
            }
        }
    }

    /** @return list<int> */
    public function toBytes(): array
    {
        return [$this->frame_rate];
    }

    /** @param  list<int>  $bytes */
    public static function fromBytes(array $bytes): static
    {
        return new static(...array_values($bytes));
    }
}
