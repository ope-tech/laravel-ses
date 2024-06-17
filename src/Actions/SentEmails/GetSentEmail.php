<?php

namespace OpeTech\LaravelSes\Actions\SentEmails;

use Lorisleiva\Actions\Concerns\AsAction;
use OpeTech\LaravelSes\Exceptions\LaravelSesSentEmailNotFoundException;
use OpeTech\LaravelSes\Models\LaravelSesSentEmail;

class GetSentEmail
{
    use AsAction;

    /**
     * @throws LaravelSesSentEmailNotFoundException
     */
    public function handle(string $messageId): LaravelSesSentEmail
    {
        $sentEmail = LaravelSesSentEmail::whereMessageId($messageId)->first();

        if (! $sentEmail) {
            throw (new LaravelSesSentEmailNotFoundException("Sent Email with message id: {$messageId} not found."));
        }

        return $sentEmail;
    }
}
