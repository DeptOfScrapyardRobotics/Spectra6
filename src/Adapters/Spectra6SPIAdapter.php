<?php

namespace ScrapyardIO\Displays\ePaper\Spectra6\Adapters;

use ScrapyardIO\Displays\ePaper\Spectra6\Concerns\Spectra6SPIChip;
use ScrapyardIO\Displays\Adapters\ThreeBitColorEPaperDisplayAdapter;
use ScrapyardIO\Displays\ePaper\Spectra6\Concerns\Spectra6BootSequence;

class Spectra6SPIAdapter extends ThreeBitColorEPaperDisplayAdapter
{
    use Spectra6SPIChip;
    use Spectra6BootSequence;

    public function bus(int $bus):static
    {
        $this->spi_spectra6_bus($bus);
        return $this;
    }

    public function chipSelect(int $cs):static
    {
        $this->spi_spectra6_chip_select($cs);
        return $this;
    }

    public function dcPin(int $chip, int $line): static
    {
        $this->dc_chip($chip);
        $this->dc_line($line);
        $this->dc_gpio();

        return $this;
    }

    public function rstPin(int $chip, int $line): static
    {
        $this->rst_chip($chip);
        $this->rst_line($line);
        $this->rst_gpio();

        return $this;
    }

    public function busyPin(int $chip, int $line): static
    {
        $this->busy_chip($chip);
        $this->busy_line($line);
        $this->busy_gpio();

        return $this;
    }

    public function pwrPin(int $chip, int $line): static
    {
        $this->pwr_chip($chip);
        $this->pwr_line($line);
        $this->pwr_gpio();
        $this->pwrOn();

        return $this;
    }

    public function boot(): static
    {
        $this->spectra6_spi();

        $this->display_width = $this->width;
        $this->display_height = $this->height;

        $this->resetSequence();
        $this->busyWait(25000);
        $this->wait(30);

        $this->setCMDH();
        $this->setPowerSetting();
        $this->setPanelSetting();
        $this->setPowerSettingExtended();
        $this->setPowerOffSequenceExtended();
        $this->setBoosterSoftStart();
        $this->setPowerOffSequence();
        $this->setTcon();
        $this->setPllControl();
        $this->setCdiVcom();
        $this->setResolution();
        $this->setExtendedConfig();
        $this->setTemperatureSensor();
        $this->busyWait(25000);
        $this->wait(200);

        return $this;
    }
}
