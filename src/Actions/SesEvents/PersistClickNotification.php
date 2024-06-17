<?php

namespace OpeTech\LaravelSes\Actions\SesEvents;

use Aws\Sns\Message;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\JobDecorator;
use OpeTech\LaravelSes\Actions\SentEmails\GetSentEmail;
use OpeTech\LaravelSes\Events\Ses\Click;
use OpeTech\LaravelSes\Models\LaravelSesEmailClick;

class PersistClickNotification
{
    use AsAction;

    public function handle(Message $message): void
    {
        $messageId = $message['Message']['mail']['messageId'];

        $sentEmail = GetSentEmail::run($messageId);

        $bounce = LaravelSesEmailClick::create([
            'sent_email_id' => $sentEmail->id,
            'message_id' => $message['Message']['mail']['messageId'],
            'clicked_at' => Carbon::parse($message['Message']['click']['timestamp']),
            'link' => $message['Message']['click']['link'],
            'link_tags' => $message['Message']['click']['linkTags'],
            'sns_raw_data' => config('laravelses.log_raw_data.clicks') ? $message->toArray() : null,
        ]);

        //dispatch the event
        Click::dispatch($bounce);
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
