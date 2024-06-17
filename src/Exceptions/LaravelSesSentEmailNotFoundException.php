<?php

namespace OpeTech\LaravelSes\Exceptions;

class LaravelSesSentEmailNotFoundException extends LaravelSesException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
