<?php

namespace ScrapyardIO\Displays\ePaper\Spectra6\Concerns;

use ScrapyardIO\Transports\SPITransport;
use ScrapyardIO\Transports\Concerns\BusyPin;
use ScrapyardIO\Transports\Concerns\ResetPin;
use ScrapyardIO\Transports\Concerns\DataCommandPin;
use ScrapyardIO\Transports\Concerns\PowerPin;

trait Spectra6SPIChip
{
    use BusyPin, DataCommandPin, ResetPin, PowerPin;

    protected ?SPITransport $spectra6_spi = null;
    protected int $spectra6_spi_bus = 0;
    protected int $spi_spectra6_chip_select = 0;
    protected int $max_packet_size = 4096;

    protected function spi_spectra6_bus(?int $bus = null): int
    {
        if(!is_null($bus))
        {
            $this->spectra6_spi_bus = $bus;
        }
        return $this->spectra6_spi_bus;
    }

    protected function spi_spectra6_chip_select(?int $cs = null): int
    {
        if($cs)
        {
            $this->spi_spectra6_chip_select = $cs;
        }
        return $this->spi_spectra6_chip_select;
    }

    protected function spectra6_spi(): ?SPITransport
    {
        if(empty($this->spectra6_spi))
        {
            $this->spectra6_spi = new SPITransport(
                $this->spi_spectra6_bus(),
                $this->spi_spectra6_chip_select(),
                0,
                25000000,
                0
            );
        }

        return $this->spectra6_spi;
    }

    public function sendData(array $bytes): void
    {
        $this->dcHigh();
        $this->spectra6_spi()->send($bytes);
    }

    public function sendCommand(array $bytes): void
    {
        $this->dcLow();
        if(count($bytes) > 1)
        {
            $command = $bytes[0];
            $this->spectra6_spi()->send([$command]);
            unset($bytes[0]);
            $payload = array_values($bytes);
            $this->sendData($payload);
        }
        else
        {
            $this->spectra6_spi()->send($bytes);
        }
    }

    protected function resetSequence(): void
    {
        $this->rstHigh();
        $this->wait(20);

        $this->rstLow();
        $this->wait(2);

        $this->rstHigh();
        $this->wait(20);

        $this->dcLow();
    }

    public function isBusy(): bool
    {
        return $this->busy_gpio()->read() === 0;
    }
}
