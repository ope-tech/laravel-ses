<?php

namespace OpeTech\LaravelSes\Mailables;

trait Batching
{
    public function send($mailer)
    {
        $mailer->getSymfonyTransport()->setBatch($this->getBatch());

        parent::send($mailer);
    }
}
