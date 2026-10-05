<?php

namespace DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts;

use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6Exception;

/**
 * Power Off Sequence (POFS, 0x03), four bytes. Defaults (0x00, 0x54, 0x00, 0x44) from Waveshare's 4 inch (E) driver.
 */
readonly class Spectra6PowerOffSequence
{
    public function __construct(
        public int $byte0 = 0x00,
        public int $byte1 = 0x54,
        public int $byte2 = 0x00,
        public int $byte3 = 0x44,
    ) {
        foreach (['byte0' => $byte0, 'byte1' => $byte1, 'byte2' => $byte2, 'byte3' => $byte3] as $field => $value) {
            if ($value < 0 || $value > 0xFF) {
                throw Spectra6Exception::invalidRegisterValue($field, $value, 0, 0xFF);
            }
        }
    }

    /** @return list<int> */
    public function toBytes(): array
    {
        return [$this->byte0, $this->byte1, $this->byte2, $this->byte3];
    }

    /** @param  list<int>  $bytes */
    public static function fromBytes(array $bytes): static
    {
        return new static(...array_values($bytes));
    }
}
