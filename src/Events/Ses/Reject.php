<?php

namespace OpeTech\LaravelSes\Events\Ses;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use OpeTech\LaravelSes\Contracts\SesSnsEventContract;
use OpeTech\LaravelSes\Models\LaravelSesEmailReject;

class Reject implements SesSnsEventContract
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public LaravelSesEmailReject $reject)
    {
        //
    }
}
