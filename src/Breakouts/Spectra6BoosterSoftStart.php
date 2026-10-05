<?php

namespace DeptOfScrapyardRobotics\Displays\Spectra6\Breakouts;

use DeptOfScrapyardRobotics\Displays\Spectra6\Spectra6Exception;

/**
 * Booster Soft Start (BTST, 0x06), four bytes. Boot writes (0x6F, 0x1F, 0x17, 0x17); every refresh writes
 * (0x6F, 0x1F, 0x17, 0x27) first, both from Waveshare's 4 inch (E) driver.
 */
readonly class Spectra6BoosterSoftStart
{
    public function __construct(
        public int $byte0 = 0x6F,
        public int $byte1 = 0x1F,
        public int $byte2 = 0x17,
        public int $byte3 = 0x17,
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
