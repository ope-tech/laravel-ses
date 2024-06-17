<?php

namespace OpeTech\LaravelSes\Actions\Sns;

use Illuminate\Support\Facades\App;
use Lorisleiva\Actions\Concerns\AsAction;

class GetEventDestinationName
{
    use AsAction;

    public function handle(): string
    {
        return config('laravelses.prefix').'-'.App::environment().'-event-destination';
    }
}
