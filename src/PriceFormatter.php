<?php

namespace YourName\PriceFormatter;

class PriceFormatter
{
    /**
     * All world currencies data
     *
     * @var array
     */
    protected $allCurrencies = null;

    /**
     * Format a price based on country code and language
     *
     * @param float $amount The price amount
     * @param string $countryCode The ISO country code
     * @param string $language The language code (en, ar, etc.)
     * @return string Formatted price
     */
    public function format($amount, $countryCode, $language)
    {
        // Get configuration
        $config = config('price-formatter');
        
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
            // If country not found, use default formatting
            return $this->applyFormatting($amount, $defaultSettings);
        }
        
        // Get language-specific format if available
        $formatSettings = $countrySettings['formats'][$language] ?? null;
        
        if (!$formatSettings) {
            // If language not found for this country, try to use English format
            $formatSettings = $countrySettings['formats']['en'] ?? null;
            
            // If still not found, use default formatting
            if (!$formatSettings) {
                return $this->applyFormatting($amount, $defaultSettings);
            }
        }
        
        // Merge country-language specific settings with defaults
        $settings = array_merge($defaultSettings, $formatSettings);
        
        return $this->applyFormatting($amount, $settings);
    }
    
    /**
     * Apply formatting to the amount based on settings
     *
     * @param float $amount
     * @param array $settings
     * @return string
     */
    protected function applyFormatting($amount, $settings)
    {
        // Format the number with decimal and thousand separators
        $formattedNumber = number_format(
            $amount,
            $settings['decimals'] ?? 2,
            $settings['decimal_separator'] ?? '.',
            $settings['thousand_separator'] ?? ','
        );
        
        // Apply symbol based on position
        if (($settings['position'] ?? 'before') === 'before') {
            return $settings['symbol'] . ($settings['separator'] ?? '') . $formattedNumber;
        } else {
            return $formattedNumber . ($settings['separator'] ?? '') . $settings['symbol'];
        }
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
