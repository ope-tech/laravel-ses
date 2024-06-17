<?php

namespace OpeTech\LaravelSes;

use Aws\SesV2\SesV2Client;
use Aws\Sns\SnsClient;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use OpeTech\LaravelSes\Actions\Sns\CreateConfigurationSet;
use OpeTech\LaravelSes\Actions\Sns\CreateConfigurationSetEventDestination;
use OpeTech\LaravelSes\Actions\Sns\CreateSnsTopicWithHttpSubscription;
use OpeTech\LaravelSes\Actions\Sns\GetTopicArn;
use OpeTech\LaravelSes\Commands\SetupConfigurationAndSnsEvents;
use OpeTech\LaravelSes\Transport\LaravelSesTransport;
use Throwable;

class LaravelSesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SetupConfigurationAndSnsEvents::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/laravelses.php' => config_path('laravelses.php'),
        ], 'config');

        $this->loadRoutesFrom(__DIR__.'/Http/routes.php');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->app->when([
            CreateConfigurationSet::class,
            CreateConfigurationSetEventDestination::class,
            self::class,
        ])
            ->needs(SesV2Client::class)
            ->give(function ($app) {

                return new SesV2Client([
                    'credentials' => [
                        'key' => config('services.ses.key'),
                        'secret' => config('services.ses.secret'),
                    ],
                    'region' => config('services.ses.region'),
                    'version' => 'latest',
                ]);
            });

        $this->app->when([
            CreateSnsTopicWithHttpSubscription::class,
            GetTopicArn::class,
        ])
            ->needs(SnsClient::class)
            ->give(function ($app) {
                return new SnsClient([
                    'credentials' => [
                        'key' => config('services.ses.key'),
                        'secret' => config('services.ses.secret'),
                    ],
                    'region' => config('services.ses.region'),
                    'version' => 'latest',
                ]);
            });

        Mail::extend('laravel-ses', function (array $config = []) {

            $sesClient = Container::getInstance()->make(SesV2Client::class);

            return new LaravelSesTransport($sesClient);
        });

        //if no mailers are set, this can error out.
        try {
            Mail::macro('withBatch', function (string $batch) {

                $this->transport->setBatch($batch);

                return $this;
            });
        } catch (Throwable $e) {
            //do nothing
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravelses.php',
            'laravelses'
        );

    }
}
