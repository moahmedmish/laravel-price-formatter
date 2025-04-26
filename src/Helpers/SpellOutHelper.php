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
     * @param string $currencyCode The currency code
     * @return string The amount in words
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
        
        if ($currencyCode) {
            // If currency code is provided, use currency spellout
            $currencyFormatter = new NumberFormatter($language, NumberFormatter::CURRENCY_SPELLOUT);
            return $currencyFormatter->formatCurrency($amount, $currencyCode);
        }
        
        // Split amount into integer and decimal parts
        $integerPart = floor(abs($amount));
        $decimalPart = round((abs($amount) - $integerPart) * 100);
        
        // Format the integer part
        $result = $formatter->format($integerPart);
        
        // Add decimal part if it exists
        if ($decimalPart > 0) {
            $result .= ' point ' . $formatter->format($decimalPart);
        }
        
        // Add negative prefix if needed
        if ($amount < 0) {
            $result = 'negative ' . $result;
        }
        
        return $result;
    }
}
