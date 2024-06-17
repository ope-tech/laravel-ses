<?php

namespace OpeTech\LaravelSes\Actions\Sns;

use Illuminate\Support\Facades\App;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTopicName
{
    use AsAction;

    public function handle(): string
    {
        return config('laravelses.prefix').'-'.App::environment().'-topic';
    }
}
