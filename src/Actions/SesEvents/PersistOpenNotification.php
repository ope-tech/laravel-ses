<?php

namespace OpeTech\LaravelSes\Actions\SesEvents;

use Aws\Sns\Message;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\JobDecorator;
use OpeTech\LaravelSes\Actions\SentEmails\GetSentEmail;
use OpeTech\LaravelSes\Events\Ses\Open;
use OpeTech\LaravelSes\Models\LaravelSesEmailOpen;

class PersistOpenNotification
{
    use AsAction;

    public function handle(Message $message): void
    {
        $messageId = $message['Message']['mail']['messageId'];

        $sentEmail = GetSentEmail::run($messageId);

        $open = LaravelSesEmailOpen::create([
            'sent_email_id' => $sentEmail->id,
            'message_id' => $message['Message']['mail']['messageId'],
            'opened_at' => Carbon::parse($message['Message']['open']['timestamp']),
            'sns_raw_data' => config('laravelses.log_raw_data.opens') ? $message->toArray() : null,
        ]);

        //dispatch the event
        Open::dispatch($open);
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
