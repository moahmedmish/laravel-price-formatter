<?php

namespace YourName\PriceFormatter\Facades;

use Illuminate\Support\Facades\Facade;

class PriceFormatter extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'price-formatter';
    }
}
