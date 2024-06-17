<?php

namespace OpeTech\LaravelSes\Events\Ses;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use OpeTech\LaravelSes\Contracts\SesSnsEventContract;
use OpeTech\LaravelSes\Models\LaravelSesEmailOpen;

class Open implements SesSnsEventContract
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public LaravelSesEmailOpen $open)
    {
        //
    }
}
