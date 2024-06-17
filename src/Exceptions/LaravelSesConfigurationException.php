<?php

namespace OpeTech\LaravelSes\Exceptions;

class LaravelSesConfigurationException extends LaravelSesException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
