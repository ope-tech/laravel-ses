<?php

namespace OpeTech\LaravelSes\Actions\SesEvents;

use Lorisleiva\Actions\Concerns\AsAction;
use OpeTech\LaravelSes\Enums\SesEvents;

class ResolveSesSnsEventClassName
{
    use AsAction;

    public function handle(SesEvents $eventType): string
    {
        return 'OpeTech\LaravelSes\Events\Ses\\'.ucfirst($eventType->value);
    }
}
