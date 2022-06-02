<?php

namespace Juhasev\LaravelSes;

use Exception;
use Illuminate\Mail\Message;
use Illuminate\Support\Carbon;
use Closure;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Testing\Fakes\MailFake;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Juhasev\LaravelSes\Exceptions\LaravelSesTooManyRecipientsException;
use Juhasev\LaravelSes\Factories\EventFactory;
use Symfony\Component\Mime\Email;

class SesMailFake extends MailFake implements SesMailerInterface
{
    use TrackingTrait;

    /**
     * Init message this will be called everytime
     *
     * @param Email $message
     * @return SentEmailContract
     * @throws Exception
     * @psalm-suppress NoInterfaceProperties
     */
    public function initMessage(Email $message): SentEmailContract
    {
        $this->checkNumberOfRecipients($message);

        return ModelResolver::get('SentEmail')::create([
            'message_id' => $message->generateMessageId(),
            'email' => $message->getTo()[0]->getAddress(),
            'batch_id' => $this->getBatchId(),
            'sent_at' => Carbon::now(),
            'delivery_tracking' => $this->deliveryTracking,
            'complaint_tracking' => $this->complaintTracking,
            'bounce_tracking' => $this->bounceTracking
        ]);
    }

    /**
     * Check message recipient for tracking
     * Open tracking etc won't work if emails are sent to more than one recipient at a time
     *
     * @param Email $message
     * @throws LaravelSesTooManyRecipientsException
     */
    protected function checkNumberOfRecipients(Email $message)
    {
        if (sizeOf($message->getTo()) > 1) {
            throw new LaravelSesTooManyRecipientsException("Tried to send to too many emails only one email may be set");
        }
    }

    /**
     * Send a new message using a view.
     *
     * @param Mailable|string|array $view
     * @param array $data
     * @param Closure|string|null $callback
     * @return void
     * @throws Exception
     * @psalm-suppress UndefinedInterfaceMethod
     * @psalm-suppress NoInterfaceProperties
     * @psalm-suppress InvalidArgument
     */
    public function send($view, array $data = [], $callback = null): void
    {
        if (! $view instanceof Mailable) {
            return;
        }

        $message = new Message(new Email());
        $message->from('sender@example.com', 'John Doe');
        $message->to(collect($view->to)->pluck('address')->all(), null, true);
        $message->html(' ');

        $symfonyMessage = $message->getSymfonyMessage();
        $sentEmail = $this->initMessage($symfonyMessage);
        $emailBody = $this->setupTracking($message->getBody(), $sentEmail);

        $view->sesBody = $emailBody;

        $view->mailer($this->currentMailer);
        $this->currentMailer = null;

        if ($view instanceof ShouldQueue) {
            /** @var Mailable $view */
            $this->queue($view, $data);
        }

        $this->mailables[] = $view;

        $this->sendEvent($sentEmail);
    }

    /**
     * Get the array of failed recipients.
     *
     * @return array
     */
    public function failures()
    {
        return [];
    }

    /**
     * Send event
     *
     * @param SentEmailContract $sentEmail
     */
    protected function sendEvent(SentEmailContract $sentEmail)
    {
        event(EventFactory::create('Sent', 'SentEmail', $sentEmail->getId()));
    }
}
