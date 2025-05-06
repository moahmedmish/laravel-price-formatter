<?php

namespace MoahmedMish\PriceFormatter\Helpers;

use NumberFormatter;
use MoahmedMish\PriceFormatter\Exceptions\InvalidFormatException;

class SpellOutHelper
{
    /**
     * Convert a number to words
     *
     * @param float $amount The amount to convert
     * @param string $language The language code
     * @param string|null $currencyCode The currency code (e.g., 'USD', 'EUR'). If null, no currency is appended.
     * @return string The amount in words, with currency code appended if provided.
     * @throws InvalidFormatException
     */
    public static function spellOut($amount, $language = 'en', $currencyCode = null)
    {
        // Check if intl extension is available
        if (!extension_loaded('intl')) {
            throw new InvalidFormatException('The intl extension is required for spelling out amounts.');
        }
        
        // Create formatter for the specified language
        $formatter = new NumberFormatter($language, NumberFormatter::SPELLOUT);
        
        // Split amount into integer and decimal parts
        $integerPart = floor(abs($amount));
        // Calculate decimal part as an integer (e.g., 0.50 becomes 50 for two decimal places)
        $decimalPart = round((abs($amount) - $integerPart) * 100); 
        
        // Format the integer part
        $result = $formatter->format($integerPart);
        
        // Add decimal part if it exists
        if ($decimalPart > 0) {
            // For example, "ten point fifty"
            $result .= ' point ' . $formatter->format($decimalPart);
        }
        
        // Add negative prefix if needed
        if ($amount < 0) {
            $result = 'negative ' . $result;
        }

        // If currency code is provided, append it to the spelled-out amount
        if ($currencyCode) {
            $result .= ' ' . strtoupper($currencyCode); // Standardize currency code to uppercase
        }
        
        return $result;
    }
}

