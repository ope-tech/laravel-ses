<?php

namespace OpeTech\LaravelSes\Notifications;

use Illuminate\Container\Container;
use Illuminate\Notifications\Messages\MailMessage;

class MailMessageWithBatching extends MailMessage
{
    public function batch(string $batch): self
    {
        $mailer = Container::getInstance()->make('mailer');
        $mailer->getSymfonyTransport()->setBatch($batch);

        return $this;
    }
}
