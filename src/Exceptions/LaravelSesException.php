<?php

namespace OpeTech\LaravelSes\Exceptions;

use Exception;

class LaravelSesException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
