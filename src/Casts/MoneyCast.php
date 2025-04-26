<?php

namespace MoahmedMish\PriceFormatter\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use MoahmedMish\PriceFormatter\Facades\PriceFormatter;

class MoneyCast implements CastsAttributes
{
    /**
     * The currency code.
     *
     * @var string|null
     */
    protected $currencyCode;

    /**
     * The language code.
     *
     * @var string|null
     */
    protected $language;

    /**
     * Create a new cast class instance.
     *
     * @param  string|null  $currencyCode
     * @param  string|null  $language
     * @return void
     */
    public function __construct($currencyCode = null, $language = null)
    {
        $this->currencyCode = $currencyCode;
        $this->language = $language;
    }

    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        return PriceFormatter::format(
            $value,
            $this->currencyCode,
            $this->language
        );
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        // If the value is already a number, just return it
        if (is_numeric($value)) {
            return $value;
        }

        // If the value is a formatted string, try to extract the numeric value
        return $this->extractNumericValue($value);
    }

    /**
     * Extract numeric value from a formatted money string.
     *
     * @param  string  $value
     * @return float
     */
    protected function extractNumericValue($value)
    {
        // Remove all non-numeric characters except decimal point
        $value = preg_replace('/[^0-9.-]/', '', $value);
        
        // Convert to float
        return (float) $value;
    }
}
