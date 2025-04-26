<?php

namespace YourName\PriceFormatter;

class PriceFormatter
{
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
        
        // Default formatting settings
        $defaultSettings = $config['default'];
        
        // Get country-specific settings if available
        $countrySettings = $config['currencies'][$countryCode] ?? null;
        
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
        return $config['currencies'][$countryCode]['code'] ?? null;
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
        
        if (!isset($config['currencies'][$countryCode]['formats'][$language])) {
            return null;
        }
        
        return $config['currencies'][$countryCode]['formats'][$language]['symbol'] ?? null;
    }
}
