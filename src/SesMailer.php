<?php

namespace oliveready7\LaravelSes;

use Illuminate\Mail\Mailer;
use oliveready7\LaravelSes\Exceptions\TooManyEmails;
use oliveready7\LaravelSes\MailProcessor;
use oliveready7\LaravelSes\Models\SentEmail;
use Carbon\Carbon;


class SesMailer extends Mailer {

    private $openTracking = false;
    private $linkTracking = false;
    private $bounceTracking = false;
    private $complaintTracking = false;
    private $deliveryTracking = false;
    private $batch;

    protected function sendSwiftMessage($message) {

        $sentEmail = $this->initMessage($message); //adds database record for the email
        $newBody = $this->setupTracking($message->getBody(), $sentEmail); //parses email body and adds tracking functionality
        $message->setBody($newBody); //sets the new parsed body as email body

        parent::sendSwiftMessage($message);
    }

    //this will be called every time
    public function initMessage($message) {
        //open tracking etc won't work if emails are sent to more than one recepient at a time
        if(sizeOf($message->getTo()) > 1)
            throw new TooManyEmails("Tried to send to too many emails only one email may be set");

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

    public function statsForBatch($batchName) {
        return SentEmail::statsForBatch($batchName);
    }

    public function setupTracking($emailBody, SentEmail $sentEmail) {
        $mailProcessor = new MailProcessor($sentEmail, $emailBody);

        if($this->openTracking) $mailProcessor->openTracking();
        if($this->linkTracking) $mailProcessor->linkTracking();

        return $mailProcessor->getEmailBody();
    }

    public function setBatch($batch) {
        $this->batch = $batch;
        return $this;
    }

    public function getBatch() {
        return $this->batch;
    }

    public function enableOpenTracking() {
        $this->openTracking = true;
        return $this;
    }

    public function enableLinkTracking() {
        $this->linkTracking = true;
        return $this;
    }

    public function enableBounceTracking() {
        $this->bounceTracking = true;
        return $this;
    }

    public function enableComplaintTracking() {
        $this->complaintTracking = true;
        return $this;
    }

    public function enableDeliveryTracking() {
        $this->deliveryTracking = true;
        return $this;
    }

    public function disableOpenTracking() {
        $this->openTracking = false;
        return $this;
    }

    public function disableLinkTracking() {
        $this->linkTracking = false;
        return $this;
    }

    public function disableBounceTracking() {
        $this->bounceTracking = false;
        return $this;
    }

    public function disableComplaintTracking() {
        $this->complaintTracking = false;
        return $this;
    }

    public function disableDeliveryTracking() {
        $this->deliveryTracking = false;
        return $this;
    }

    public function enableAllTracking() {
        return $this->enableOpenTracking()
            ->enableLinkTracking()
            ->enableBounceTracking()
            ->enableComplaintTracking()
            ->enableDeliveryTracking();
    }

    public function disableAllTracking() {
        return $this->disableOpenTracking()
            ->disableLinkTracking()
            ->disableBounceTracking()
            ->disableComplaintTracking()
            ->disableDeliveryTracking();
    }



    public function trackingSettings() {
        return [
            'openTracking' => $this->openTracking,
            'linkTracking' => $this->linkTracking,
            'bounceTracking' => $this->bounceTracking,
            'complaintTracking' => $this->complaintTracking,
            'deliveryTracking' => $this->deliveryTracking
         ];
    }
}
