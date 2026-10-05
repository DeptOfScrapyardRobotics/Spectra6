<?php

namespace DeptOfScrapyardRobotics\Displays\Spectra6\Enums;

/** Deep Sleep (0x07) takes this check code; any other byte is ignored. */
enum Spectra6DeepSleepCheck: int
{
    case CODE = 0xA5;
}
