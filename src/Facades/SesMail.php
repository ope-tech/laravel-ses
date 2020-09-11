<?php

namespace Juhasev\LaravelSes\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Container\Container;
use Juhasev\LaravelSes\SesMailFake;

/**
 * @see \Illuminate\Mail\Mailer
 */
class SesMail extends Facade
{
    public static function fake()
    {
        $swiftMailer = app('mailer')->getSwiftMailer();

        static::swap(
            new SesMailFake(
                'ses-mailer-fake',
                Container::getInstance()['view'],
                app('mailer')->getSwiftMailer(),
                Container::getInstance()['events']
            )
        );
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
