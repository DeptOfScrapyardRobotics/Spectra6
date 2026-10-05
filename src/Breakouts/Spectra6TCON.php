<?php

namespace DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts;

use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6Exception;

/**
 * TCON (0x60), two bytes. Defaults (0x02, 0x00) from Waveshare's 4 inch (E) driver.
 */
readonly class Spectra6TCON
{
    public function __construct(
        public int $s2g = 0x02,
        public int $g2s = 0x00,
    ) {
        foreach (['s2g' => $s2g, 'g2s' => $g2s] as $field => $value) {
            if ($value < 0 || $value > 0xFF) {
                throw Spectra6Exception::invalidRegisterValue($field, $value, 0, 0xFF);
            }
        }
    }

    /** @return list<int> */
    public function toBytes(): array
    {
        return [$this->s2g, $this->g2s];
    }

    /** @param  list<int>  $bytes */
    public static function fromBytes(array $bytes): static
    {
        return new static(...array_values($bytes));
    }
}
