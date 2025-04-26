# Laravel Price Formatter

A Laravel package for formatting prices based on country code and language. This package allows you to easily format monetary values according to different country standards and language preferences.

## Features

- Format prices based on country code and language
- Support for all world currencies with ISO 4217 codes
- Customizable formatting options (symbol position, separators, decimals)
- Support for Eastern Arabic numerals (١٢٣) for Arabic, Farsi, and Urdu languages
- Locale-based formatting using app()->getLocale()
- Accounting format for negative numbers (e.g., ($10.50) instead of -$10.50)
- Compact formatting for large numbers (e.g., 1K, 1.5M, 1B)
- Custom rounding modes (ceil, floor, half_up, half_down)
- Percentage formatting (e.g., 25%, 3.5%)
- Spell-out amounts in words (e.g., "Ten dollars and fifty cents")
- Dynamic currency conversion using exchange rate APIs
- Blade directives for easy templating
- Eloquent casting for money attributes
- Validation rules for money values
- Easy to use with Laravel Facade
- Fully configurable through config file
- Support for custom currency configurations

## Installation

You can install the package via composer:

```bash
composer require moahmedmish/laravel-price-formatter
```

The package will automatically register its service provider.

You can publish the configuration file with:

```bash
php artisan vendor:publish --provider="MoahmedMish\PriceFormatter\PriceFormatterServiceProvider" --tag="config"
```

## Basic Usage

### Format a price

```php
// Format a price using country code and language
$formattedPrice = PriceFormatter::format(5, 'EG', 'en');
// Returns: "5 LE"

// Format a price for Arabic language
$formattedPrice = PriceFormatter::format(5, 'EG', 'ar');
// Returns: "٥ ج م" (with Eastern Arabic numerals)
```

### Format using current locale

```php
// Format a price using the current application locale
$formattedPrice = PriceFormatter::formatLocalized(10.50, 'US');
// Returns: "$10.50" (if app locale is 'en')
// Returns: "١٠٫٥٠ دولار" (if app locale is 'ar')
```

### Accounting Format

```php
// Format negative numbers with accounting format
$formattedPrice = PriceFormatter::formatAccounting(-10.50, 'US', 'en');
// Returns: "($10.50)" instead of "-$10.50"
```

### Compact Formatting

```php
// Format large numbers in compact form
$formattedPrice = PriceFormatter::formatCompact(1500, 'US', 'en');
// Returns: "$1.5K"

$formattedPrice = PriceFormatter::formatCompact(1500000, 'US', 'en');
// Returns: "$1.5M"
```

### Percentage Formatting

```php
// Format a value as percentage
$formattedPercentage = PriceFormatter::formatPercentage(0.255, 1);
// Returns: "25.5%"
```

### Spell Out Amounts

```php
// Convert amount to words
$amountInWords = PriceFormatter::spellOut(10.50, 'en', 'USD');
// Returns: "ten dollars and fifty cents"
```

### Currency Conversion

```php
// Convert from one currency to another
$convertedPrice = PriceFormatter::convert(10, 'USD', 'EUR', 'en');
// Returns the amount converted to EUR and formatted
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

## Blade Directives

The package provides several Blade directives for easy formatting in your views:

```blade
{{-- Basic formatting --}}
@money(10.50, 'US', 'en')

{{-- Localized formatting --}}
@moneyLocalized(10.50, 'US')

{{-- Accounting format --}}
@moneyAccounting(-10.50, 'US', 'en')

{{-- Compact format --}}
@moneyCompact(1500000, 'US', 'en')
```

## Eloquent Casting

You can use the `MoneyCast` to automatically format money attributes in your Eloquent models:

```php
use MoahmedMish\PriceFormatter\Casts\MoneyCast;

class Product extends Model
{
    protected $casts = [
        'price' => MoneyCast::class.':US,en', // Format as US dollars in English
    ];
}
```

Then when you access the attribute, it will be automatically formatted:

```php
$product = Product::find(1);
echo $product->price; // Returns formatted price like "$10.50"
```

## Validation Rules

The package provides a validation rule for money values:

```php
use MoahmedMish\PriceFormatter\Rules\FormattedMoney;

$request->validate([
    'price' => ['required', new FormattedMoney('USD')],
]);
```

## Configuration

The package comes with a comprehensive configuration file that allows you to customize the currency formatting for different countries and languages.

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
                    'use_eastern_arabic_numerals' => true, // Use Eastern Arabic numerals (١٢٣)
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
        'use_eastern_arabic_numerals' => false, // Set to true to use Eastern Arabic numerals by default
        'accounting_format' => false, // Set to true to use accounting format for negative numbers
        'rounding_mode' => 'half_up', // Options: 'ceil', 'floor', 'half_up', 'half_down'
    ],
    
    'numerals' => [
        // Automatically use Eastern Arabic numerals for these languages
        'eastern_arabic_languages' => ['ar', 'fa', 'ur'],
        
        // Override automatic language detection
        'force_eastern_arabic' => false, // Set to true to force Eastern Arabic numerals for all languages
        'force_western_arabic' => false, // Set to true to force Western Arabic numerals for all languages
    ],
    
    'locale' => [
        'use_app_locale' => true, // Set to true to automatically use app()->getLocale() for language
        'locale_to_country_map' => [
            'en' => 'US',
            'ar' => 'EG',
            'fr' => 'FR',
            // Add more mappings here
        ],
    ],
    
    'compact_format' => [
        'enabled' => false, // Set to true to enable compact formatting by default
        'thresholds' => [
            'thousand' => 1000,
            'million' => 1000000,
            'billion' => 1000000000,
        ],
        'symbols' => [
            'thousand' => 'K',
            'million' => 'M',
            'billion' => 'B',
        ],
        'precision' => 1, // Number of decimal places for compact format
    ],
    
    // Path to custom currencies configuration file
    'custom_currencies_path' => null,
];
```

### Eastern Arabic Numerals

The package supports Eastern Arabic numerals (١٢٣٤٥٦٧٨٩٠) which are commonly used in Arabic, Farsi, and Urdu languages. By default, the package will automatically use Eastern Arabic numerals when formatting prices for these languages.

You can control this behavior in several ways:

1. **Global default**: Set `use_eastern_arabic_numerals` in the default settings to `true` to use Eastern Arabic numerals for all languages by default.

2. **Language-specific**: Set `use_eastern_arabic_numerals` in the language-specific format settings to override the default behavior for that language.

3. **Force globally**: Use the `force_eastern_arabic` or `force_western_arabic` settings to override all other settings and force a specific numeral format for all languages.

### Locale-Based Formatting

The package can automatically use the current application locale for formatting:

```php
// In config/price-formatter.php
'locale' => [
    'use_app_locale' => true,
    'locale_to_country_map' => [
        'en' => 'US',
        'ar' => 'EG',
        'fr' => 'FR',
        // Add more mappings here
    ],
],
```

With this configuration, you can simply call:

```php
$formattedPrice = PriceFormatter::formatLocalized(10.50);
```

And it will use the current app locale to determine the language and country for formatting.

### Custom Rounding Modes

The package supports different rounding modes:

- `ceil`: Always rounds up
- `floor`: Always rounds down
- `half_up`: Rounds to nearest, ties away from zero (default)
- `half_down`: Rounds to nearest, ties toward zero

```php
// In config/price-formatter.php
'default' => [
    'rounding_mode' => 'half_up',
    // other settings...
],
```

### Accounting Format

For financial applications, you can use accounting format for negative numbers:

```php
// In config/price-formatter.php
'default' => [
    'accounting_format' => true,
    // other settings...
],
```

This will format negative numbers with parentheses, e.g., `($10.50)` instead of `-$10.50`.

### Compact Formatting

For large numbers, you can enable compact formatting:

```php
// In config/price-formatter.php
'compact_format' => [
    'enabled' => true,
    'thresholds' => [
        'thousand' => 1000,
        'million' => 1000000,
        'billion' => 1000000000,
    ],
    'symbols' => [
        'thousand' => 'K',
        'million' => 'M',
        'billion' => 'B',
    ],
    'precision' => 1,
],
```

This will format large numbers as `1K`, `1.5M`, `2.3B`, etc.

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
        'ar' => [
            'symbol' => 'ين',
            'position' => 'after',
            'separator' => ' ',
            'use_eastern_arabic_numerals' => true,
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
    "ar": "ين",
    "native": "¥"
  }
}
```

## Cryptocurrency Support

You can add cryptocurrency support by adding entries to your configuration:

```php
'currencies' => [
    'BTC' => [
        'code' => 'BTC',
        'formats' => [
            'en' => [
                'symbol' => '₿',
                'position' => 'before',
                'separator' => '',
                'decimals' => 8, // Bitcoin can have up to 8 decimal places
            ],
        ],
    ],
    'ETH' => [
        'code' => 'ETH',
        'formats' => [
            'en' => [
                'symbol' => 'Ξ',
                'position' => 'before',
                'separator' => '',
                'decimals' => 18, // Ethereum can have up to 18 decimal places
            ],
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
