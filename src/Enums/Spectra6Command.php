<?php

namespace ScrapyardIO\Displays\ePaper\Spectra6\Enums;

enum Spectra6Command: int
{
    // === Core Display Commands ===
    case PANEL_SETTING = 0x00;
    case POWER_SETTING = 0x01;
    case POWER_OFF = 0x02;
    case POWER_OFF_SEQUENCE = 0x03;
    case POWER_ON = 0x04;
    case BOOSTER_SOFT_START = 0x06;
    case DEEP_SLEEP = 0x07;
    case DATA_START_TRANSMIT = 0x10;
    case DISPLAY_REFRESH = 0x12;

    // === Configuration Commands ===
    case PLL_CONTROL = 0x30;
    case CDI_VCOM_SETTING = 0x50;
    case TCON_SETTING = 0x60;
    case RESOLUTION_SETTING = 0x61;

    // === Magic Init Command ===
    case CMDH = 0xAA;

    // === Extended/Proprietary Commands ===
    case SECRET_SETTING1 = 0x05;      // Power setting (extended)
    case SECRET_SETTING2 = 0x08;      // Power off sequence (extended)
    case SECRET_SETTING3 = 0x84;      // Temperature sensor enable
    case SECRET_SETTING4 = 0xB4;      // Unknown - used in init
    case SECRET_SETTING5 = 0xB5;      // Unknown - used in init
    case SECRET_SETTING6 = 0xE3;      // Extended config
    case SECRET_SETTING7 = 0xE7;      // Unknown - used in init
    case SECRET_SETTING8 = 0xE9;      // Unknown - used in init
}
