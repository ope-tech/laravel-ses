<?php

namespace OpeTech\LaravelSes\Actions\Sns;

use Illuminate\Support\Facades\App;
use Lorisleiva\Actions\Concerns\AsAction;

class GetConfigurationSetName
{
    use AsAction;

    //TODO, allow a custom prefix to be set, like Horizon does. So multiple apps can use the same install.
    public function handle(): string
    {
        return config('laravelses.prefix').'-'.App::environment().'-configuration-set';
    }
}
