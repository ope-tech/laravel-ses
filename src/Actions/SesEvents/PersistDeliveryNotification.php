<?php

namespace OpeTech\LaravelSes\Actions\SesEvents;

use Aws\Sns\Message;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\JobDecorator;
use OpeTech\LaravelSes\Actions\SentEmails\GetSentEmail;
use OpeTech\LaravelSes\Events\Ses\Delivery;
use OpeTech\LaravelSes\Models\LaravelSesEmailDelivery;

class PersistDeliveryNotification
{
    use AsAction;

    public function handle(Message $message): void
    {
        $messageId = $message['Message']['mail']['messageId'];

        $sentEmail = GetSentEmail::run($messageId);

        $delivery = LaravelSesEmailDelivery::create([
            'sent_email_id' => $sentEmail->id,
            'message_id' => $message['Message']['mail']['messageId'],
            'delivered_at' => Carbon::parse($message['Message']['delivery']['timestamp']),
            'sns_raw_data' => config('laravelses.log_raw_data.deliveries') ? $message->toArray() : null,
        ]);

        //update the delivered_at timestamp on the sent email
        $sentEmail->delivered_at = Carbon::parse($message['Message']['delivery']['timestamp']);
        $sentEmail->save();

        //dispatch the event
        Delivery::dispatch($delivery);

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
