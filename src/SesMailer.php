<?php

namespace Juhasev\LaravelSes;

use Illuminate\Support\Carbon;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Juhasev\LaravelSes\Exceptions\LaravelLaravelSesDailyQuotaExceededException;
use Juhasev\LaravelSes\Exceptions\LaravelSesInvalidSenderAddressException;
use Juhasev\LaravelSes\Exceptions\LaravelSesMaximumSendingRateExceeded;
use Juhasev\LaravelSes\Exceptions\LaravelSesSendFailedException;
use Juhasev\LaravelSes\Exceptions\LaravelSesTemporaryServiceFailureException;
use Juhasev\LaravelSes\Exceptions\LaravelSesTooManyRecipientsException;
use Juhasev\LaravelSes\Factories\EventFactory;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\CurlException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;
use Swift_TransportException;

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
            throw new LaravelSesTooManyRecipientsException("Tried to send to too many emails only one email may be set");
        }
    }

    public function send($view, array $data = [], $callback = null)
    {
       try {
           parent::send($view, $data, $callback);
       } catch (Swift_TransportException $e) {
           $this->throwException($e);
       }
    }

    /**
     * Throw SampleNinja exceptions
     *
     * @param Swift_TransportException $e
     * @throws LaravelLaravelSesDailyQuotaExceededException
     * @throws LaravelSesInvalidSenderAddressException
     * @throws LaravelSesMaximumSendingRateExceeded
     * @throws LaravelSesTemporaryServiceFailureException|LaravelSesSendFailedException
     */
    protected function throwException(Swift_TransportException $e) {

        $errorMessage = $this->parseErrorFromSwiftTransportException($e->getMessage());
        $errorCode = $this->parseErrorCode($errorMessage);

        Log::error('SES Error: ' . $errorMessage);

        if (Str::contains($errorMessage, '454 Throttling failure: Maximum sending rate exceeded')) {
            throw new LaravelSesMaximumSendingRateExceeded($errorMessage, $errorCode);
        }

        if (Str::contains($errorMessage, '454 Throttling failure: Daily message quota exceeded')) {
            throw new LaravelLaravelSesDailyQuotaExceededException($errorMessage, $errorCode);
        }

        if (Str::contains($errorMessage, '554 Message rejected: Email address is not verified')) {
            throw new LaravelSesInvalidSenderAddressException($errorMessage, $errorCode);
        }

        if (Str::contains($errorMessage, '451 Temporary service failure')) {
            throw new LaravelSesTemporaryServiceFailureException($errorMessage, $errorCode);
        }

        throw new LaravelSesSendFailedException($errorMessage, $errorCode);
    }

    /**
     * Resolve error code
     *
     * @param $message
     * @return string
     */
    protected function parseErrorFromSwiftTransportException($message): string
    {
        $message = Str::after($message, ' with message "');
        return Str::beforeLast($message, '"');
    }

    /**
     * Parse error code
     *
     * @param string $smtpError
     * @return int
     */
    protected function parseErrorCode(string $smtpError): int
    {
        return (int) Str::before($smtpError, ' Message');
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
        $configurationSetName=App::environment() ."-ses-".config('services.ses.region');
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
