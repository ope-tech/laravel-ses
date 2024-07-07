<?php

namespace OpeTech\LaravelSes\Actions\SesEvents;

use Aws\Sns\Message;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\JobDecorator;
use OpeTech\LaravelSes\Actions\SentEmails\GetSentEmail;
use OpeTech\LaravelSes\Events\Ses\Complaint;
use OpeTech\LaravelSes\Models\LaravelSesEmailComplaint;

class PersistComplaintNotification
{
    use AsAction;

    public function handle(Message $message): void
    {
        $messageId = $message['Message']['mail']['messageId'];

        $sentEmail = GetSentEmail::run($messageId);

        $complaint = LaravelSesEmailComplaint::create([
            'type' => $message['Message']['complaint']['complaintFeedbackType']
                ?? $message['Message']['complaint']['complaintSubType']
                ?? null,
            'sent_email_id' => $sentEmail->id,
            'message_id' => $message['Message']['mail']['messageId'],
            'complained_at' => Carbon::parse($message['Message']['complaint']['timestamp']),
            'sns_raw_data' => config('laravelses.log_raw_data.complaints') ? $message->toArray() : null,
        ]);

        //dispatch the event
        Complaint::dispatch($complaint);
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
