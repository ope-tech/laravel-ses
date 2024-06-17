<?php

namespace OpeTech\LaravelSes\Actions\SesEvents;

use Aws\Sns\Message;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\JobDecorator;
use OpeTech\LaravelSes\Actions\SentEmails\GetSentEmail;
use OpeTech\LaravelSes\Events\Ses\Reject;
use OpeTech\LaravelSes\Models\LaravelSesEmailReject;

class PersistRejectNotification
{
    use AsAction;

    public function handle(Message $message): void
    {
        $messageId = $message['Message']['mail']['messageId'];

        $sentEmail = GetSentEmail::run($messageId);

        $bounce = LaravelSesEmailReject::create([
            'sent_email_id' => $sentEmail->id,
            'message_id' => $message['Message']['mail']['messageId'],
            'sns_raw_data' => config('laravelses.log_raw_data.rejects') ? $message->toArray() : null,
            'reason' => $message['Message']['reject']['reason'],
        ]);

        //dispatch the event
        Reject::dispatch($bounce);
    }

    public function asJob(Message $message)
    {
        $this->handle($message);
    }

    public function configureJob(JobDecorator $job): void
    {
        ConfigureJob::run($job);
    }
}
