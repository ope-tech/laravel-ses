<?php

namespace Juhasev\LaravelSes;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Closure;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Testing\Fakes\MailFake;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Juhasev\LaravelSes\Exceptions\LaravelSesTooManyRecipientsException;
use Juhasev\LaravelSes\Factories\EventFactory;

class SesMailFake extends MailFake implements SesMailerInterface
{
    use TrackingTrait;

    /**
     * Init message this will be called everytime
     *
     * @param Mailable $message
     * @return SentEmailContract
     * @throws Exception
     * @psalm-suppress NoInterfaceProperties
     */
    public function initMessage($message): SentEmailContract
    {
        $this->checkNumberOfRecipients($message);

        return ModelResolver::get('SentEmail')::create([
            'message_id' => rand(1, 999999),
            'email' => Arr::get($message->to, '0.address'),
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
     * @param $message
     * @throws LaravelSesTooManyRecipientsException
     */
    protected function checkNumberOfRecipients($message)
    {
        if (count($message->to) > 1) {
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

        $sentEmail = $this->initMessage($view);

        $emailBody = $this->setupTracking($view->render(), $sentEmail);

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
