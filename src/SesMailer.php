<?php

namespace oliveready7\LaravelSes;

use Illuminate\Mail\Mailer;
use oliveready7\LaravelSes\Exceptions\TooManyEmails;
use oliveready7\LaravelSes\MailProcessor;
use oliveready7\LaravelSes\Models\SentEmail;
use oliveready7\LaravelSes\Models\EmailLink;
use oliveready7\LaravelSes\Models\EmailBounce;
use oliveready7\LaravelSes\Models\EmailComplaint;
use oliveready7\LaravelSes\Models\EmailOpen;
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

    public function statsForEmail($email) {
        return [
            'counts' => [
                'sent_emails' => SentEmail::whereEmail($email)->count(),
                'deliveries' => SentEmail::whereEmail($email)->whereNotNull('delivered_at')->count(),
                'opens' => EmailOpen::whereEmail($email)->whereNotNull('opened_at')->count(),
                'bounces' => EmailBounce::whereEmail($email)->whereNotNull('bounced_at')->count(),
                'complaints' => EmailComplaint::whereEmail($email)->whereNotNull('complained_at')->count(),
                'click_throughs' => EmailLink::join(
                        'laravel_ses_sent_emails',
                        'laravel_ses_sent_emails.id',
                        'laravel_ses_email_links.sent_email_id'
                    )
                    ->where('laravel_ses_sent_emails.email', '=', $email)
                    ->whereClicked(true)
                    ->count(\DB::raw('DISTINCT(laravel_ses_sent_emails.id)')) // if a user clicks two different links on one campaign, only one is counted
            ],
            'data' => [
                'sent_emails' => SentEmail::whereEmail($email)->get(),
                'deliveries' => SentEmail::whereEmail($email)->whereNotNull('delivered_at')->get(),
                'opens' => EmailOpen::whereEmail($email)->whereNotNull('opened_at')->get(),
                'bounces' => EmailComplaint::whereEmail($email)->whereNotNull('bounced_at')->get(),
                'complaints' => EmailComplaint::whereEmail($email)->whereNotNull('complained_at')->get(),
                'click_throughs' => EmailLink::join(
                    'laravel_ses_sent_emails',
                    'laravel_ses_sent_emails.id',
                    'laravel_ses_email_links.sent_email_id'
                )
                ->where('laravel_ses_sent_emails.email', '=', $email)
                ->whereClicked(true)
                ->get()
            ]
        ];
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
