<?php

namespace Juhasev\LaravelSes;

use Carbon\Carbon;
use Illuminate\Mail\Mailer;
use Juhasev\LaravelSes\Exceptions\TooManyEmails;
use Juhasev\LaravelSes\Models\SentEmail;
use Juhasev\LaravelSes\Services\Stats;

class SesMailer extends Mailer implements SesMailerInterface
{
    use TrackingTrait;

    //this will be called every time
    public function initMessage($message)
    {
        //open tracking etc won't work if emails are sent to more than one recepient at a time
        if (sizeOf($message->getTo()) > 1) {
            throw new TooManyEmails("Tried to send to too many emails only one email may be set");
        }

        $sentEmail = SentEmail::create([
            'message_id' => $message->getId(),
            'email' => key($message->getTo()),
            'batch' => $this->getBatch(),
            'sent_at' => Carbon::now(),
            'delivery_tracking' => $this->deliveryTracking,
            'complaint_tracking' => $this->complaintTracking,
            'bounce_tracking' => $this->bounceTracking
        ]);

        return $sentEmail;
    }

    public function statsForBatch(string $batchName): array
    {
        return Stats::statsForBatch($batchName);
    }

    public function statsForEmail(string $email): array
    {
        return Stats::statsForEmail($email);
    }

    protected function sendSwiftMessage($message)
    {
        $sentEmail = $this->initMessage($message); //adds database record for the email
        $newBody = $this->setupTracking($message->getBody(), $sentEmail); //parses email body and adds tracking functionality
        $message->setBody($newBody); //sets the new parsed body as email body

        parent::sendSwiftMessage($message);
    }
}
