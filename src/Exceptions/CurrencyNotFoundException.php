<?php

namespace MoahmedMish\PriceFormatter\Exceptions;

use Exception;

class CurrencyNotFoundException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $currencyCode
     * @return void
     */
    public function __construct($currencyCode)
    {
        parent::__construct("Currency with code '{$currencyCode}' not found.");
    }
}
