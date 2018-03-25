<?php

namespace oliveready7\LaravelSes;

use Illuminate\Support\Testing\Fakes\MailFake;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use oliveready7\LaravelSes\SesMailInterface;
use Illuminate\Container\Container;
use Illuminate\Mail\Message;

class SesMailFake extends SesMailer
{
    protected function sendSwiftMessage($message)
    {
        $sentEmail = $this->initMessage($message);
        $newBody = $this->setupTracking($message->getBody(), $sentEmail);
        $message->setBody($newBody);
    }

    //COPY FAKE METHODS SO THINGS LIKE ASSERT SENT ETC WORK
}
