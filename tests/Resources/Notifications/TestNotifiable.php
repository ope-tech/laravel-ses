<?php

namespace OpeTech\LaravelSes\Tests\Resources\Notifications;

use Illuminate\Notifications\Notifiable;

class TestNotifiable
{
    use Notifiable;

    public function __construct(public string $email)
    {
    }
}
