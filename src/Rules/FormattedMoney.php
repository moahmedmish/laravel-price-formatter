<?php

namespace MoahmedMish\PriceFormatter\Rules;

use Illuminate\Contracts\Validation\Rule;
use MoahmedMish\PriceFormatter\Facades\PriceFormatter;

class FormattedMoney implements Rule
{
    /**
     * The currency code.
     *
     * @var string|null
     */
    protected $currencyCode;

    /**
     * Create a new rule instance.
     *
     * @param  string|null  $currencyCode
     * @return void
     */
    public function __construct($currencyCode = null)
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // If the value is numeric, it's valid
        if (is_numeric($value)) {
            return true;
        }

        // If the value is a string, try to extract the numeric value
        if (is_string($value)) {
            $numericValue = $this->extractNumericValue($value);
            return is_numeric($numericValue);
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $currency = $this->currencyCode ? " in {$this->currencyCode} format" : '';
        return "The :attribute must be a valid monetary value{$currency}.";
    }

    /**
     * Extract numeric value from a formatted money string.
     *
     * @param  string  $value
     * @return float|null
     */
    protected function extractNumericValue($value)
    {
        // Remove all non-numeric characters except decimal point
        $value = preg_replace('/[^0-9.-]/', '', $value);
        
        // Check if the result is a valid number
        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }
}
