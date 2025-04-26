# Laravel Price Formatter

A Laravel package for formatting prices based on country code and language. This package allows you to easily format monetary values according to different country standards and language preferences.

## Features

- Format prices based on country code and language
- Support for multiple currencies and languages
- Customizable formatting options (symbol position, separators, decimals)
- Easy to use with Laravel Facade
- Fully configurable through config file

## Installation

You can install the package via composer:

```bash
composer require yourname/laravel-price-formatter
```

The package will automatically register its service provider.

You can publish the configuration file with:

```bash
php artisan vendor:publish --provider="YourName\PriceFormatter\PriceFormatterServiceProvider" --tag="config"
```

## Usage

### Basic Usage

```php
// Format a price using country code and language
$formattedPrice = PriceFormatter::format(5, 'EG', 'en');
// Returns: "5 LE"

// Format a price for Arabic language
$formattedPrice = PriceFormatter::format(5, 'EG', 'ar');
// Returns: "5 ج م"
```

### Helper Methods

```php
// Get currency code for a country
$currencyCode = PriceFormatter::getCurrencyCode('EG');
// Returns: "EGP"

// Get currency symbol for a country and language
$currencySymbol = PriceFormatter::getCurrencySymbol('EG', 'en');
// Returns: "LE"
```

## Configuration

The package comes with a configuration file that allows you to customize the currency formatting for different countries and languages.

```php
// config/price-formatter.php

return [
    'currencies' => [
        'EG' => [
            'code' => 'EGP',
            'formats' => [
                'en' => [
                    'symbol' => 'LE',
                    'position' => 'after',
                    'separator' => ' ',
                ],
                'ar' => [
                    'symbol' => 'ج م',
                    'position' => 'after',
                    'separator' => ' ',
                ],
            ],
        ],
        // Add more countries here
    ],
    
    'default' => [
        'symbol' => '$',
        'position' => 'before',
        'separator' => '',
        'decimal_separator' => '.',
        'thousand_separator' => ',',
        'decimals' => 2,
    ],
];
```

### Adding New Currencies

To add support for a new currency, add a new entry to the `currencies` array in the configuration file:

```php
'JP' => [
    'code' => 'JPY',
    'formats' => [
        'en' => [
            'symbol' => '¥',
            'position' => 'before',
            'separator' => '',
        ],
        'ja' => [
            'symbol' => '円',
            'position' => 'after',
            'separator' => '',
        ],
    ],
],
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
