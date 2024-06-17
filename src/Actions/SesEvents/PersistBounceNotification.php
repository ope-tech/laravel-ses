<?php

namespace OpeTech\LaravelSes\Actions\SesEvents;

use Aws\Sns\Message;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\JobDecorator;
use OpeTech\LaravelSes\Actions\SentEmails\GetSentEmail;
use OpeTech\LaravelSes\Events\Ses\Bounce;
use OpeTech\LaravelSes\Models\LaravelSesEmailBounce;

class PersistBounceNotification
{
    use AsAction;

    public function handle(Message $message): void
    {
        $messageId = $message['Message']['mail']['messageId'];

        $sentEmail = GetSentEmail::run($messageId);

        $bounce = LaravelSesEmailBounce::create([
            'type' => $message['Message']['bounce']['bounceType'],
            'sent_email_id' => $sentEmail->id,
            'message_id' => $message['Message']['mail']['messageId'],
            'bounced_at' => Carbon::parse($message['Message']['bounce']['timestamp']),
            'sns_raw_data' => config('laravelses.log_raw_data.bounces') ? $message->toArray() : null,
        ]);

        //dispatch the event
        Bounce::dispatch($bounce);
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
