<?php

namespace Juhasev\LaravelSes\Facades;

use Illuminate\Support\Facades\Facade;
use Juhasev\LaravelSes\SesMailFake;

/**
 * @see \Illuminate\Mail\Mailer
 */
class SesMail extends Facade
{
    public static function fake()
    {
        static::swap(new SesMailFake());
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'SesMailer';
    }
}
