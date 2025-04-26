<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Currency Configurations
    |--------------------------------------------------------------------------
    |
    | This file contains the configurations for currency formatting based on
    | country code and language. Each country has its own currency symbol
    | and formatting rules for different languages.
    |
    */

    'currencies' => [
        'EG' => [
            'code' => 'EGP',
            'formats' => [
                'en' => [
                    'symbol' => 'LE',
                    'position' => 'after', // Symbol position: before or after
                    'separator' => ' ', // Space between amount and symbol
                ],
                'ar' => [
                    'symbol' => 'ج م',
                    'position' => 'after',
                    'separator' => ' ',
                ],
            ],
        ],
        'US' => [
            'code' => 'USD',
            'formats' => [
                'en' => [
                    'symbol' => '$',
                    'position' => 'before',
                    'separator' => '',
                ],
                'ar' => [
                    'symbol' => 'دولار',
                    'position' => 'after',
                    'separator' => ' ',
                ],
            ],
        ],
        'GB' => [
            'code' => 'GBP',
            'formats' => [
                'en' => [
                    'symbol' => '£',
                    'position' => 'before',
                    'separator' => '',
                ],
                'ar' => [
                    'symbol' => 'جنيه استرليني',
                    'position' => 'after',
                    'separator' => ' ',
                ],
            ],
        ],
        'EU' => [
            'code' => 'EUR',
            'formats' => [
                'en' => [
                    'symbol' => '€',
                    'position' => 'after',
                    'separator' => ' ',
                ],
                'ar' => [
                    'symbol' => 'يورو',
                    'position' => 'after',
                    'separator' => ' ',
                ],
            ],
        ],
        'SA' => [
            'code' => 'SAR',
            'formats' => [
                'en' => [
                    'symbol' => 'SAR',
                    'position' => 'after',
                    'separator' => ' ',
                ],
                'ar' => [
                    'symbol' => 'ر.س',
                    'position' => 'after',
                    'separator' => ' ',
                ],
            ],
        ],
        'AE' => [
            'code' => 'AED',
            'formats' => [
                'en' => [
                    'symbol' => 'AED',
                    'position' => 'after',
                    'separator' => ' ',
                ],
                'ar' => [
                    'symbol' => 'د.إ',
                    'position' => 'after',
                    'separator' => ' ',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Default settings to use when a specific country or language is not found
    |
    */
    'default' => [
        'symbol' => '$',
        'position' => 'before',
        'separator' => '',
        'decimal_separator' => '.',
        'thousand_separator' => ',',
        'decimals' => 2,
    ],
];
