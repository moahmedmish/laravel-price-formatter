<?php

namespace MoahmedMish\PriceFormatter;

use MoahmedMish\PriceFormatter\Helpers\SpellOutHelper;
use MoahmedMish\PriceFormatter\Exceptions\CurrencyNotFoundException;
use MoahmedMish\PriceFormatter\Exceptions\InvalidFormatException;
use MoahmedMish\PriceFormatter\Exceptions\InvalidRoundingModeException;
use Illuminate\Support\Facades\Http;

class PriceFormatter
{
    /**
     * All world currencies data
     *
     * @var array
     */
    protected $allCurrencies = null;

    /**
     * Eastern Arabic numerals mapping
     *
     * @var array
     */
    protected $easternArabicNumerals = [
        '0' => '٠',
        '1' => '١',
        '2' => '٢',
        '3' => '٣',
        '4' => '٤',
        '5' => '٥',
        '6' => '٦',
        '7' => '٧',
        '8' => '٨',
        '9' => '٩',
        '.' => '٫',
        ',' => '٬',
    ];

    /**
     * Format a price based on country code and language
     *
     * @param float $amount The price amount
     * @param string|null $countryCode The ISO country code
     * @param string|null $language The language code (en, ar, etc.)
     * @param array $options Additional formatting options
     * @return string Formatted price
     * @throws CurrencyNotFoundException
     * @throws InvalidFormatException
     * @throws InvalidRoundingModeException
     */
    public function format($amount, $countryCode = null, $language = null, array $options = [])
    {
        // Get configuration
        $config = config('price-formatter');
        
        // Use app locale if enabled and language not specified
        if (is_null($language) && ($config['locale']['use_app_locale'] ?? false)) {
            $language = app()->getLocale();
        }
        
        // If country code is not specified but language is, try to map language to country
        if (is_null($countryCode) && !is_null($language) && isset($config['locale']['locale_to_country_map'][$language])) {
            $countryCode = $config['locale']['locale_to_country_map'][$language];
        }
        
        // Default to first available country if still null
        if (is_null($countryCode)) {
            $countryCode = array_key_first($config['currencies']);
        }
        
        // Default to English if language is still null
        if (is_null($language)) {
            $language = 'en';
        }
        
        // Load all currencies if needed
        $this->loadAllCurrencies();
        
        // Default formatting settings
        $defaultSettings = $config['default'];
        
        // Get country-specific settings if available
        $countrySettings = $config['currencies'][$countryCode] ?? null;
        
        // If country not found in default config, try to find it in all currencies
        if (!$countrySettings && isset($this->allCurrencies[$countryCode])) {
            $currencyCode = $this->getCurrencyCodeFromCountry($countryCode);
            if ($currencyCode) {
                $countrySettings = [
                    'code' => $currencyCode,
                    'formats' => $this->getFormatsForCurrency($currencyCode, $language)
                ];
            }
        }
        
        if (!$countrySettings) {
            // If country not found and fallback is disabled, throw exception
            if (isset($options['throw_if_not_found']) && $options['throw_if_not_found']) {
                throw new CurrencyNotFoundException($countryCode);
            }
            
            // Otherwise, use default formatting
            return $this->applyFormatting($amount, $defaultSettings, $language, $options);
        }
        
        // Get language-specific format if available
        $formatSettings = $countrySettings['formats'][$language] ?? null;
        
        if (!$formatSettings) {
            // If language not found for this country, try to use English format
            $formatSettings = $countrySettings['formats']['en'] ?? null;
            
            // If still not found, use default formatting
            if (!$formatSettings) {
                return $this->applyFormatting($amount, $defaultSettings, $language, $options);
            }
        }
        
        // Merge country-language specific settings with defaults
        $settings = array_merge($defaultSettings, $formatSettings);
        
        // Override settings with options if provided
        if (!empty($options)) {
            $settings = array_merge($settings, $options);
        }
        
        return $this->applyFormatting($amount, $settings, $language, $options);
    }
    
    /**
     * Apply formatting to the amount based on settings
     *
     * @param float $amount
     * @param array $settings
     * @param string $language
     * @param array $options
     * @return string
     * @throws InvalidRoundingModeException
     */
    protected function applyFormatting($amount, $settings, $language = 'en', array $options = [])
    {
        // Apply rounding mode if specified
        $roundingMode = $settings['rounding_mode'] ?? 'half_up';
        $amount = $this->applyRoundingMode($amount, $roundingMode, $settings['decimals'] ?? 2);
        
        // Check if we should use accounting format for negative numbers
        $isNegative = $amount < 0;
        $useAccountingFormat = $settings['accounting_format'] ?? false;
        
        // Make amount positive for formatting
        $amount = abs($amount);
        
        // Check if we should use compact formatting
        $useCompactFormat = $settings['compact_format']['enabled'] ?? false;
        if ($useCompactFormat) {
            return $this->applyCompactFormatting($amount, $settings, $language, $isNegative, $useAccountingFormat);
        }
        
        // Format the number with decimal and thousand separators
        $formattedNumber = number_format(
            $amount,
            $settings['decimals'] ?? 2,
            $settings['decimal_separator'] ?? '.',
            $settings['thousand_separator'] ?? ','
        );
        
        // Convert to Eastern Arabic numerals if needed
        if (($settings['use_eastern_arabic_numerals'] ?? false) || 
            (($language === 'ar' || $language === 'fa' || $language === 'ur') && 
             ($settings['use_eastern_arabic_numerals'] ?? null) !== false)) {
            $formattedNumber = $this->convertToEasternArabicNumerals($formattedNumber, $settings);
        }
        
        // Apply symbol based on position
        $position = $settings['position'] ?? 'before';
        $separator = $settings['separator'] ?? '';
        $symbol = $settings['symbol'] ?? '';
        
        if ($position === 'before') {
            $result = $symbol . $separator . $formattedNumber;
        } else {
            $result = $formattedNumber . $separator . $symbol;
        }
        
        // Apply negative formatting
        if ($isNegative) {
            if ($useAccountingFormat) {
                $result = '(' . $result . ')';
            } else {
                $result = '-' . $result;
            }
        }
        
        return $result;
    }
    
    /**
     * Apply compact formatting to large numbers (e.g., 1K, 1M, 1B)
     *
     * @param float $amount
     * @param array $settings
     * @param string $language
     * @param bool $isNegative
     * @param bool $useAccountingFormat
     * @return string
     */
    protected function applyCompactFormatting($amount, $settings, $language, $isNegative, $useAccountingFormat)
    {
        $compactSettings = $settings['compact_format'] ?? [];
        $thresholds = $compactSettings['thresholds'] ?? [
            'thousand' => 1000,
            'million' => 1000000,
            'billion' => 1000000000,
        ];
        $symbols = $compactSettings['symbols'] ?? [
            'thousand' => 'K',
            'million' => 'M',
            'billion' => 'B',
        ];
        $precision = $compactSettings['precision'] ?? 1;
        
        $suffix = '';
        $divisor = 1;
        
        if ($amount >= $thresholds['billion']) {
            $suffix = $symbols['billion'];
            $divisor = $thresholds['billion'];
        } elseif ($amount >= $thresholds['million']) {
            $suffix = $symbols['million'];
            $divisor = $thresholds['million'];
        } elseif ($amount >= $thresholds['thousand']) {
            $suffix = $symbols['thousand'];
            $divisor = $thresholds['thousand'];
        }
        
        $compactAmount = $amount / $divisor;
        
        // Format the compact number
        $formattedNumber = number_format(
            $compactAmount,
            $precision,
            $settings['decimal_separator'] ?? '.',
            $settings['thousand_separator'] ?? ','
        );
        
        // Convert to Eastern Arabic numerals if needed
        if (($settings['use_eastern_arabic_numerals'] ?? false) || 
            (($language === 'ar' || $language === 'fa' || $language === 'ur') && 
             ($settings['use_eastern_arabic_numerals'] ?? null) !== false)) {
            $formattedNumber = $this->convertToEasternArabicNumerals($formattedNumber, $settings);
        }
        
        // Apply symbol and suffix based on position
        $position = $settings['position'] ?? 'before';
        $separator = $settings['separator'] ?? '';
        $symbol = $settings['symbol'] ?? '';
        
        if ($position === 'before') {
            $result = $symbol . $separator . $formattedNumber . $suffix;
        } else {
            $result = $formattedNumber . $suffix . $separator . $symbol;
        }
        
        // Apply negative formatting
        if ($isNegative) {
            if ($useAccountingFormat) {
                $result = '(' . $result . ')';
            } else {
                $result = '-' . $result;
            }
        }
        
        return $result;
    }
    
    /**
     * Apply rounding mode to amount
     *
     * @param float $amount
     * @param string $mode
     * @param int $decimals
     * @return float
     * @throws InvalidRoundingModeException
     */
    protected function applyRoundingMode($amount, $mode, $decimals)
    {
        $factor = pow(10, $decimals);
        
        switch ($mode) {
            case 'ceil':
                return ceil($amount * $factor) / $factor;
            case 'floor':
                return floor($amount * $factor) / $factor;
            case 'half_up':
                return round($amount, $decimals, PHP_ROUND_HALF_UP);
            case 'half_down':
                return round($amount, $decimals, PHP_ROUND_HALF_DOWN);
            default:
                throw new InvalidRoundingModeException($mode);
        }
    }
    
    /**
     * Convert Western Arabic numerals to Eastern Arabic numerals
     *
     * @param string $number
     * @param array $settings
     * @return string
     */
    protected function convertToEasternArabicNumerals($number, $settings)
    {
        $decimalSeparator = $settings['decimal_separator'] ?? '.';
        $thousandSeparator = $settings['thousand_separator'] ?? ',';
        
        // Replace decimal and thousand separators
        if ($decimalSeparator === '.') {
            $number = str_replace('.', '٫', $number);
        } else {
            $number = str_replace($decimalSeparator, '٫', $number);
        }
        
        if ($thousandSeparator === ',') {
            $number = str_replace(',', '٬', $number);
        } else {
            $number = str_replace($thousandSeparator, '٬', $number);
        }
        
        // Replace digits
        $number = strtr($number, $this->easternArabicNumerals);
        
        return $number;
    }
    
    /**
     * Helper method to get currency code for a country
     *
     * @param string $countryCode
     * @return string|null
     */
    public function getCurrencyCode($countryCode)
    {
        $config = config('price-formatter');
        
        // First check in the config
        if (isset($config['currencies'][$countryCode]['code'])) {
            return $config['currencies'][$countryCode]['code'];
        }
        
        // If not found, try to get from all currencies
        return $this->getCurrencyCodeFromCountry($countryCode);
    }
    
    /**
     * Helper method to get currency symbol for a country and language
     *
     * @param string $countryCode
     * @param string $language
     * @return string|null
     */
    public function getCurrencySymbol($countryCode, $language)
    {
        $config = config('price-formatter');
        
        // First check in the config
        if (isset($config['currencies'][$countryCode]['formats'][$language]['symbol'])) {
            return $config['currencies'][$countryCode]['formats'][$language]['symbol'];
        }
        
        // If not found, try to get from all currencies
        $this->loadAllCurrencies();
        
        $currencyCode = $this->getCurrencyCodeFromCountry($countryCode);
        if (!$currencyCode || !isset($this->allCurrencies[$currencyCode])) {
            return null;
        }
        
        // Try to get symbol for the specified language
        if (isset($this->allCurrencies[$currencyCode]['symbol'][$language])) {
            return $this->allCurrencies[$currencyCode]['symbol'][$language];
        }
        
        // Fallback to native symbol
        return $this->allCurrencies[$currencyCode]['symbol']['native'] ?? null;
    }
    
    /**
     * Format a price using the current application locale
     *
     * @param float $amount The price amount
     * @param string|null $countryCode The ISO country code (optional)
     * @param array $options Additional formatting options
     * @return string Formatted price
     */
    public function formatLocalized($amount, $countryCode = null, array $options = [])
    {
        $language = app()->getLocale();
        
        // If country code is not specified, try to map language to country
        if (is_null($countryCode)) {
            $config = config('price-formatter');
            if (isset($config['locale']['locale_to_country_map'][$language])) {
                $countryCode = $config['locale']['locale_to_country_map'][$language];
            }
        }
        
        return $this->format($amount, $countryCode, $language, $options);
    }
    
    /**
     * Format a price with accounting format for negative numbers
     *
     * @param float $amount The price amount
     * @param string $countryCode The ISO country code
     * @param string $language The language code (en, ar, etc.)
     * @param array $options Additional formatting options
     * @return string Formatted price
     */
    public function formatAccounting($amount, $countryCode = null, $language = null, array $options = [])
    {
        $options['accounting_format'] = true;
        return $this->format($amount, $countryCode, $language, $options);
    }
    
    /**
     * Format a price with compact notation (e.g., 1K, 1M, 1B)
     *
     * @param float $amount The price amount
     * @param string $countryCode The ISO country code
     * @param string $language The language code (en, ar, etc.)
     * @param array $options Additional formatting options
     * @return string Formatted price
     */
    public function formatCompact($amount, $countryCode = null, $language = null, array $options = [])
    {
        $options['compact_format']['enabled'] = true;
        return $this->format($amount, $countryCode, $language, $options);
    }
    
    /**
     * Format a percentage value
     *
     * @param float $value The percentage value (e.g., 0.25 for 25%)
     * @param int $decimals Number of decimal places
     * @param string $language The language code (en, ar, etc.)
     * @param array $options Additional formatting options
     * @return string Formatted percentage
     */
    public function formatPercentage($value, $decimals = 2, $language = null, array $options = [])
    {
        // Convert to percentage (multiply by 100)
        $percentage = $value * 100;
        
        // Get configuration
        $config = config('price-formatter');
        
        // Use app locale if enabled and language not specified
        if (is_null($language) && ($config['locale']['use_app_locale'] ?? false)) {
            $language = app()->getLocale();
        }
        
        // Default to English if language is still null
        if (is_null($language)) {
            $language = 'en';
        }
        
        // Default settings
        $settings = [
            'decimals' => $decimals,
            'decimal_separator' => $config['default']['decimal_separator'] ?? '.',
            'thousand_separator' => $config['default']['thousand_separator'] ?? ',',
            'symbol' => '%',
            'position' => 'after',
            'separator' => '',
        ];
        
        // Override settings with options if provided
        if (!empty($options)) {
            $settings = array_merge($settings, $options);
        }
        
        // Format the percentage
        $formattedNumber = number_format(
            $percentage,
            $settings['decimals'],
            $settings['decimal_separator'],
            $settings['thousand_separator']
        );
        
        // Convert to Eastern Arabic numerals if needed
        if (($settings['use_eastern_arabic_numerals'] ?? false) || 
            (($language === 'ar' || $language === 'fa' || $language === 'ur') && 
             ($settings['use_eastern_arabic_numerals'] ?? null) !== false)) {
            $formattedNumber = $this->convertToEasternArabicNumerals($formattedNumber, $settings);
        }
        
        // Apply symbol based on position
        if ($settings['position'] === 'before') {
            return $settings['symbol'] . $settings['separator'] . $formattedNumber;
        } else {
            return $formattedNumber . $settings['separator'] . $settings['symbol'];
        }
    }
    
    /**
     * Convert a number to words
     *
     * @param float $amount The amount to convert
     * @param string $language The language code
     * @param string|null $currencyCode The currency code (optional)
     * @return string The amount in words
     * @throws InvalidFormatException
     */
    public function spellOut($amount, $language = 'en', $currencyCode = null)
    {
        return SpellOutHelper::spellOut($amount, $language, $currencyCode);
    }
    
    /**
     * Convert a price from one currency to another using exchange rates
     *
     * @param float $amount The price amount
     * @param string $fromCurrency The source currency code
     * @param string $toCurrency The target currency code
     * @param string|null $language The language code (en, ar, etc.)
     * @param array $options Additional formatting options
     * @return string Formatted price in target currency
     * @throws InvalidFormatException
     */
    public function convert($amount, $fromCurrency, $toCurrency, $language = null, array $options = [])
    {
        // Get exchange rate
        $rate = $this->getExchangeRate($fromCurrency, $toCurrency);
        
        // Convert amount
        $convertedAmount = $amount * $rate;
        
        // Format the converted amount
        return $this->format($convertedAmount, $toCurrency, $language, $options);
    }
    
    /**
     * Get exchange rate between two currencies
     *
     * @param string $fromCurrency The source currency code
     * @param string $toCurrency The target currency code
     * @return float The exchange rate
     * @throws InvalidFormatException
     */
    protected function getExchangeRate($fromCurrency, $toCurrency)
    {
        // If currencies are the same, return 1
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }
        
        // Try to get exchange rate from cache
        $cacheKey = "exchange_rate_{$fromCurrency}_{$toCurrency}";
        if (cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }
        
        // Example API call to get exchange rate (replace with your preferred API)
        try {
            $response = Http::get("https://api.exchangerate.host/convert", [
                'from' => $fromCurrency,
                'to' => $toCurrency,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $rate = $data['result'] ?? null;
                
                if ($rate) {
                    // Cache the rate for 1 hour
                    cache()->put($cacheKey, $rate, now()->addHour());
                    return $rate;
                }
            }
            
            throw new InvalidFormatException("Failed to get exchange rate from {$fromCurrency} to {$toCurrency}");
        } catch (\Exception $e) {
            throw new InvalidFormatException("Error fetching exchange rate: " . $e->getMessage());
        }
    }
    
    /**
     * Load all currencies data
     *
     * @return void
     */
    protected function loadAllCurrencies()
    {
        if ($this->allCurrencies !== null) {
            return;
        }
        
        $config = config('price-formatter');
        
        // Start with the built-in currencies data
        $currenciesPath = __DIR__ . '/../resources/currencies.json';
        if (file_exists($currenciesPath)) {
            $this->allCurrencies = json_decode(file_get_contents($currenciesPath), true)['currencies'] ?? [];
        } else {
            $this->allCurrencies = [];
        }
        
        // Load custom currencies if path is specified
        if (!empty($config['custom_currencies_path']) && file_exists($config['custom_currencies_path'])) {
            $customCurrencies = json_decode(file_get_contents($config['custom_currencies_path']), true);
            if (is_array($customCurrencies) && isset($customCurrencies['currencies'])) {
                // Merge custom currencies with built-in ones, giving priority to custom definitions
                $this->allCurrencies = array_merge($this->allCurrencies, $customCurrencies['currencies']);
            }
        }
    }
    
    /**
     * Get currency code from country code
     *
     * @param string $countryCode
     * @return string|null
     */
    protected function getCurrencyCodeFromCountry($countryCode)
    {
        $this->loadAllCurrencies();
        
        // Look for the country code in all currencies
        foreach ($this->allCurrencies as $code => $data) {
            if (isset($data['country']) && $data['country'] === $countryCode) {
                return $code;
            }
        }
        
        return null;
    }
    
    /**
     * Get formats for a currency and language
     *
     * @param string $currencyCode
     * @param string $language
     * @return array
     */
    protected function getFormatsForCurrency($currencyCode, $language)
    {
        $this->loadAllCurrencies();
        
        if (!isset($this->allCurrencies[$currencyCode])) {
            return [];
        }
        
        $currency = $this->allCurrencies[$currencyCode];
        
        // Default format
        $format = [
            'symbol' => $currency['symbol']['native'] ?? $currencyCode,
            'position' => 'after',
            'separator' => ' ',
            'use_eastern_arabic_numerals' => $language === 'ar' || $language === 'fa' || $language === 'ur',
        ];
        
        // Language-specific format if available
        if (isset($currency['symbol'][$language])) {
            $format['symbol'] = $currency['symbol'][$language];
        } elseif (isset($currency['symbol']['en'])) {
            $format['symbol'] = $currency['symbol']['en'];
        }
        
        // For some common currencies, adjust position
        $beforeSymbolCurrencies = ['USD', 'GBP', 'EUR', 'JPY', 'CAD', 'AUD', 'NZD', 'HKD', 'SGD'];
        if (in_array($currencyCode, $beforeSymbolCurrencies)) {
            $format['position'] = 'before';
            $format['separator'] = '';
        }
        
        return [$language => $format, 'en' => $format];
    }
}
