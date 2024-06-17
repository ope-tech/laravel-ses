<?php

namespace OpeTech\LaravelSes\Actions\Sns;

use Aws\Sns\Message;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class ConfirmSubscription
{
    use AsAction;

    public function handle(Message $message)
    {
        Http::get($message['SubscribeURL']);
    }
}
