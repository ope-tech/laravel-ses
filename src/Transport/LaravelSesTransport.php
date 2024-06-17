<?php

namespace OpeTech\LaravelSes\Transport;

use Aws\Exception\AwsException;
use Aws\SesV2\SesV2Client;
use OpeTech\LaravelSes\Actions\Sns\GetConfigurationSetName;
use OpeTech\LaravelSes\Models\LaravelSesBatch;
use OpeTech\LaravelSes\Models\LaravelSesSentEmail;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class LaravelSesTransport extends AbstractTransport
{
    protected $batch = null;

    /**
     * Create a new SES transport instance.
     *
     * @param  array  $options
     * @return void
     */
    public function __construct(protected SesV2Client $ses)
    {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $recepients = collect($message->getEnvelope()->getRecipients())
            ->map
            ->toString()
            ->values()
            ->all();

        try {
            $result = $this->ses->sendEmail([
                'Source' => $message->getEnvelope()->getSender()->toString(),
                'Destination' => [
                    'ToAddresses' => $recepients,
                ],
                'Content' => [
                    'Raw' => [
                        'Data' => $message->toString(),
                    ],
                ],
                'ConfigurationSetName' => GetConfigurationSetName::run(),
            ]);
        } catch (AwsException $e) {
            $reason = $e->getAwsErrorMessage() ?? $e->getMessage();

            throw new TransportException(
                sprintf('Request to AWS SES V2 API failed. Reason: %s.', $reason),
                is_int($e->getCode()) ? $e->getCode() : 0,
                $e
            );
        }

        $messageId = $result->get('MessageId');

        //TODO: throw exception if recepients is more than 1. We cannot send to more than one email
        //at a time. Otherwise you won't be able to Identify which email has opened the email
        //delivery/open/click rates etc won't be accurate either.
        //You can override this if you want to. Maybe you want to send a report with multiple
        //recipients. However, be aware that we will only take the first email for tracking purposes.

        foreach ($recepients as $recipient) {

            //setup batch record if applicable
            if ($this->batch) {
                $batch = LaravelSesBatch::firstOrCreate([
                    'name' => $this->batch,
                ]);
            }

            LaravelSesSentEmail::create([
                'message_id' => $messageId,
                'email' => $recipient,
                'sent_at' => now(),
                'batch_id' => $batch->id ?? null,
            ]);
        }

        $message->getOriginalMessage()->getHeaders()->addHeader('X-Message-ID', $messageId);
        $message->getOriginalMessage()->getHeaders()->addHeader('X-SES-Message-ID', $messageId);

        $this->setBatch(null);
    }

    /**
     * Get the Amazon SES client for the SesTransport instance.
     *
     * @return \Aws\Ses\SesClient
     */
    public function ses()
    {
        return $this->ses;
    }

    /**
     * Get the transmission options being used by the transport.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the transmission options being used by the transport.
     *
     * @return array
     */
    public function setOptions(array $options)
    {
        return $this->options = $options;
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'laravel-ses';
    }

    public function setBatch(?string $batch)
    {
        $this->batch = $batch;

        return $this;
    }
}
