<?php

namespace OpeTech\LaravelSes\Tests;

use Illuminate\Config\Repository;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Throwable;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;

    protected $enablesPackageDiscoveries = true;

    protected function getEnvironmentSetUp($app)
    {
        //makes errors much eaiser to see in the CLI when testing
        $app->make('Illuminate\Contracts\Debug\ExceptionHandler')->renderable(function (Throwable $e) {
            return throw $e;
        });
    }

    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('database.default', 'testing');

            $config->set('mail.mailers.laravel-ses', [
                'transport' => 'laravel-ses',
            ]);

            $config->set('mail.default', 'laravel-ses');

            $config->set('services.ses', [
                'key' => 'testkey',
                'secret' => 'testsecret',
                'region' => 'eu-west-2',
                'version' => 'latest',
            ]);

        });
    }
}
