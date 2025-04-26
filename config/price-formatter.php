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
                    'use_eastern_arabic_numerals' => true,
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
                    'use_eastern_arabic_numerals' => true,
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
                    'use_eastern_arabic_numerals' => true,
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
                    'use_eastern_arabic_numerals' => true,
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
                    'use_eastern_arabic_numerals' => true,
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
                    'use_eastern_arabic_numerals' => true,
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
        'use_eastern_arabic_numerals' => false, // Set to true to use Eastern Arabic numerals (١٢٣) by default
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Numeral Format Settings
    |--------------------------------------------------------------------------
    |
    | Settings for numeral formatting across different languages
    |
    */
    'numerals' => [
        // Automatically use Eastern Arabic numerals for these languages
        'eastern_arabic_languages' => ['ar', 'fa', 'ur'],
        
        // Override automatic language detection
        'force_eastern_arabic' => false, // Set to true to force Eastern Arabic numerals for all languages
        'force_western_arabic' => false, // Set to true to force Western Arabic numerals for all languages
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Custom Currencies Path
    |--------------------------------------------------------------------------
    |
    | Path to custom currencies configuration file that will override the defaults
    |
    */
    'custom_currencies_path' => null,
];
