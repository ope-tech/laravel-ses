<?php

namespace Juhasev\LaravelSes;

use Carbon\Carbon;
use Illuminate\Mail\Mailer;
use Juhasev\LaravelSes\Exceptions\TooManyEmails;
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
            'batch' => $this->getBatch(),
            'sent_at' => Carbon::now(),
            'delivery_tracking' => $this->deliveryTracking,
            'complaint_tracking' => $this->complaintTracking,
            'bounce_tracking' => $this->bounceTracking,
            'reject_tracking' => $this->rejectTracking
        ]);
    }

    /**
     * Check message recipient for tracking
     * Open tracking etc won't work if emails are sent to more than one recipient at a time
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
     * @return int|void|null
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws NotLoadedException
     * @throws StrictException
     */
    protected function sendSwiftMessage($message)
    {
        $sentEmail = $this->initMessage($message); //adds database record for the email
        $newBody = $this->setupTracking($message->getBody(), $sentEmail); //parses email body and adds tracking functionality
        $message->setBody($newBody); //sets the new parsed body as email body

        parent::sendSwiftMessage($message);
    }
}
