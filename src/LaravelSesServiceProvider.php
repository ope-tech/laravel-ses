<?php

namespace oliveready7\LaravelSes;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LaravelSesServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadViewsFrom(__DIR__.'/Mocking/Views', 'LaravelSes');
        $this->loadMigrationsFrom(__DIR__.'/Migrations');
        $this->publishes([
           __DIR__.'/Assets' => public_path('laravel-ses'),
        ], 'public');

        $this->publishes([
            __DIR__.'/Config/laravelses.php' => config_path('laravelses.php')
        ], 'config');


        if ($this->app->runningInConsole()) {
            $this->commands([
                SetupSns::class
            ]);
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
           __DIR__.'/Config/laravelses.php',
            'laravelses'
       );

        $this->registerIlluminateMailer();
    }

    protected function registerIlluminateMailer()
    {
        $this->app->singleton('SesMailer', function ($app) {
            $config = $app->make('config')->get('mail');

            // Once we have create the mailer instance, we will set a container instance
            // on the mailer. This allows us to resolve mailer classes via containers
            // for maximum testability on said classes instead of passing Closures.
            $mailer = new SesMailer(
                $app['view'],
                $app['swift.mailer'],
                $app['events']
            );

            if ($app->bound('queue')) {
                $mailer->setQueue($app['queue']);
            }

            // Next we will set all of the global addresses on this mailer, which allows
            // for easy unification of all "from" addresses as well as easy debugging
            // of sent messages since they get be sent into a single email address.
            foreach (['from', 'reply_to', 'to'] as $type) {
                $this->setGlobalAddress($mailer, $config, $type);
            }

            return $mailer;
        });
    }

    protected function setGlobalAddress($mailer, array $config, $type)
    {
        $address = Arr::get($config, $type);

        if (is_array($address) && isset($address['address'])) {
            $mailer->{'always'.Str::studly($type)}($address['address'], $address['name']);
        }
    }
}
