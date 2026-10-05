<?php

namespace DeptOfScrapyardRobotics\Displays\Spectra6\Enums;

/** The panel's 4-bit pixel codes. Codes 4 and 7 to 15 are not inks. */
enum Spectra6Ink: int
{
    case BLACK = 0x0;
    case WHITE = 0x1;
    case YELLOW = 0x2;
    case RED = 0x3;
    case BLUE = 0x5;
    case GREEN = 0x6;
}
