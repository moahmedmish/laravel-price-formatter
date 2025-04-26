# Laravel Price Formatter

A Laravel package for formatting prices based on country code and language. This package allows you to easily format monetary values according to different country standards and language preferences.

## Features

- Format prices based on country code and language
- Support for all world currencies with ISO 4217 codes
- Customizable formatting options (symbol position, separators, decimals)
- Easy to use with Laravel Facade
- Fully configurable through config file
- Support for custom currency configurations

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
    
    // Path to custom currencies configuration file
    'custom_currencies_path' => null,
];
```

### Customizing Currencies

The package includes all world currencies by default, but you can customize them in two ways:

1. **Using the configuration file**: Add or modify entries in the `currencies` array in the config file.

2. **Using a custom currencies JSON file**: Create a JSON file with your custom currency definitions and set the path in the config:

```php
// config/price-formatter.php
'custom_currencies_path' => storage_path('app/currencies.json'),
```

The custom currencies JSON file should follow this format:

```json
{
  "currencies": {
    "EGP": {
      "name": "Egyptian Pound",
      "country": "EGYPT",
      "symbol": {
        "en": "EGP",
        "ar": "ج.م",
        "native": "ج.م"
      }
    },
    "USD": {
      "name": "US Dollar",
      "country": "UNITED STATES",
      "symbol": {
        "en": "$",
        "ar": "دولار",
        "native": "$"
      }
    }
  }
}
```

Custom currency definitions will override the built-in ones with the same code.

## Adding New Currencies

To add support for a new currency, you can either:

1. Add a new entry to the `currencies` array in the configuration file:

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

2. Or add it to your custom currencies JSON file:

```json
"JPY": {
  "name": "Yen",
  "country": "JAPAN",
  "symbol": {
    "en": "¥",
    "ja": "円",
    "native": "¥"
  }
}
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
