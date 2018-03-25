<?php

namespace oliveready7\LaravelSes;

use Ramsey\Uuid\Uuid;
use oliveready7\LaravelSes\Models\EmailOpen;
use oliveready7\LaravelSes\Models\SentEmail;
use oliveready7\LaravelSes\Models\EmailLink;
use PHPHtmlParser\Dom;

class MailProcessor
{
    protected $emailBody;
    protected $batch;
    protected $sentEmail;

    public function __construct(SentEmail $sentEmail, $emailBody)
    {
        $this->setEmailBody($emailBody);
        $this->setSentEmail($sentEmail);
    }

    public function getEmailBody()
    {
        return $this->emailBody;
    }

    private function setEmailBody($body)
    {
        $this->emailBody = $body;
    }

    private function setSentEmail(SentEmail $email)
    {
        $this->sentEmail = $email;
    }

    public function openTracking()
    {
        $beaconIdentifier = Uuid::uuid4()->toString();
        $beaconUrl = config('app.url') . "/laravel-ses/beacon/$beaconIdentifier";

        EmailOpen::create([
            'sent_email_id' => $this->sentEmail->id,
            'email' => $this->sentEmail->email,
            'batch' => $this->sentEmail->batch,
            'beacon_identifier' => $beaconIdentifier,
            'url' => $beaconUrl,
        ]);

        $this->setEmailBody($this->getEmailBody() . "<img src=\"$beaconUrl\""
        . " alt=\"\" style=\"width:1px;height:1px;\"/>");
        return $this;
    }

    public function linkTracking()
    {
        $dom = new Dom;
        $dom->load($this->getEmailBody());
        $anchors = $dom->find('a');
        foreach ($anchors as $anchor) {
            $originalUrl = $anchor->getAttribute('href');
            $anchor->setAttribute('href', $this->createAppLink($originalUrl));
        }
        $this->setEmailBody($dom->innerHtml);
        return $this;
    }

    private function createAppLink(string $originalUrl)
    {
        $linkIdentifier = Uuid::uuid4()->toString();

        //first create the link
        $link = EmailLink::create([
            'sent_email_id' => $this->sentEmail->id,
            'batch' => $this->sentEmail->batch,
            'link_identifier' => $linkIdentifier,
            'original_url' => $originalUrl
        ]);

        return config('app.url') . "/laravel-ses/link/$linkIdentifier";
    }
}
