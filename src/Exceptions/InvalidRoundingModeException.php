<?php

namespace MoahmedMish\PriceFormatter\Exceptions;

use Exception;

class InvalidRoundingModeException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $mode
     * @return void
     */
    public function __construct($mode)
    {
        parent::__construct("Invalid rounding mode '{$mode}'. Supported modes are: 'ceil', 'floor', 'half_up', 'half_down'.");
    }
}
