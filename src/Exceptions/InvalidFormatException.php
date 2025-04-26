<?php

namespace MoahmedMish\PriceFormatter\Exceptions;

use Exception;

class InvalidFormatException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $message
     * @return void
     */
    public function __construct($message = "Invalid formatting configuration.")
    {
        parent::__construct($message);
    }
}
