<?php

namespace Juhasev\LaravelSes\Factories;

class BaseEvent implements EventInterface
{
    public function send()
    {
        event($this);
    }
}