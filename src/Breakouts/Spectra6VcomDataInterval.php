<?php

namespace DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts;

use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6Exception;

/**
 * VCOM and Data Interval (CDI, 0x50), one byte. Default 0x3F from Waveshare's 4 inch (E) driver.
 */
readonly class Spectra6VcomDataInterval
{
    public function __construct(
        public int $interval = 0x3F,
    ) {
        foreach (['interval' => $interval] as $field => $value) {
            if ($value < 0 || $value > 0xFF) {
                throw Spectra6Exception::invalidRegisterValue($field, $value, 0, 0xFF);
            }
        }
    }

    /** @return list<int> */
    public function toBytes(): array
    {
        return [$this->interval];
    }

    /** @param  list<int>  $bytes */
    public static function fromBytes(array $bytes): static
    {
        return new static(...array_values($bytes));
    }
}
