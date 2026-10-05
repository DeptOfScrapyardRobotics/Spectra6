<?php

return [
    'default_config' => 'spi',
    'configs' => [
        'spi' => [
            'driver' => 'none',
            'device' => '',
            'chip_select' => 0,
            'mode' => 0,
            'speed' => 10_000_000,  // Hz, this chip select's clock
            'width' => null,        // null keeps 400
            'height' => null,       // null keeps 600
            'dc' => [
                'driver' => 'none',
                'device' => '',
                'pin' => 25,
            ],
            'rst' => [
                'driver' => 'none',
                'device' => '',
                'pin' => 17,
            ],
            'busy' => [
                'driver' => 'none',
                'device' => '',
                'pin' => 24,
            ],
            'pwr' => [              // the HAT's supply switch; leave disabled when the panel is always powered
                'enabled' => false,
                'driver' => 'none',
                'device' => '',
                'pin' => 18,
            ],
        ],
    ],
];
