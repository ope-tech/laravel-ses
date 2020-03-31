<?php
namespace Juhasev\LaravelSes\Tests\Unit;

use Illuminate\Foundation\Application;
use Juhasev\LaravelSes\Facades\SesMail;
use Juhasev\LaravelSes\LaravelSesServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class UnitTestCase extends OrchestraTestCase
{
    /**
     * Setup test case
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'testbench']);
    }

    /**
     * Load package service provider
     *
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [LaravelSesServiceProvider::class];
    }

    /**
     * Load package alias
     * @param  Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'SesMail' => SesMail::class,
        ];
    }

    /**
     * Set up test bench environment
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('app.url', 'https://laravel-ses.com');
    }
}
