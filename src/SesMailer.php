<?php

namespace Juhasev\LaravelSes;

use Illuminate\Support\Carbon;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\App;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Juhasev\LaravelSes\Exceptions\TooManyEmails;
use Juhasev\LaravelSes\Factories\EventFactory;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\CurlException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;

class SesMailer extends Mailer implements SesMailerInterface
{
    use TrackingTrait;

    /**
     * Init message (this is always called)
     * Creates database entry for the sent email
     *
     * @param $message
     * @return mixed
     * @throws \Exception
     */
    public function initMessage($message)
    {
        $this->checkNumberOfRecipients($message);

        return ModelResolver::get('SentEmail')::create([
            'message_id' => $message->getId(),
            'email' => key($message->getTo()),
            'batch_id' => $this->getBatchId(),
            'sent_at' => Carbon::now()->toDateTimeString(),
            'delivery_tracking' => $this->deliveryTracking,
            'complaint_tracking' => $this->complaintTracking,
            'bounce_tracking' => $this->bounceTracking
        ]);
    }

    /**
     * Check message recipient for tracking
     * Open tracking etc won't work if emails are sent to more than one recipient at a time
     *
     * @param $message
     */
    protected function checkNumberOfRecipients($message)
    {
        if (sizeOf($message->getTo()) > 1) {
            throw new TooManyEmails("Tried to send to too many emails only one email may be set");
        }
    }

    /**
     * Send swift message
     *
     * @param $message
     * @return void
     *
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws NotLoadedException
     * @throws StrictException
     */
    protected function sendSwiftMessage($message): void
    {
        $headers = $message->getHeaders();

        # staging-ses-complaint-us-west-2
        $configurationSetName="ses-".App::environment()."-".config('services.ses.region');
        $headers->addTextHeader('X-SES-CONFIGURATION-SET', $configurationSetName);

        $sentEmail = $this->initMessage($message);
        $newBody = $this->setupTracking($message->getBody(), $sentEmail);
        $message->setBody($newBody);

        $this->sendEvent($sentEmail);

        parent::sendSwiftMessage($message);
    }

    /**
     * Send event
     *
     * @param SentEmailContract $sentEmail
     */
    protected function sendEvent(SentEmailContract $sentEmail)
    {
        event(EventFactory::create('Sent', 'SentEmail', $sentEmail->id));
    }
}
