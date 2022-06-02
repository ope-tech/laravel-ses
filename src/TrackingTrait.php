<?php

namespace Juhasev\LaravelSes;

use Exception;
use Juhasev\LaravelSes\Contracts\BatchContract;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\CurlException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;
use Symfony\Component\Mime\Part\AbstractPart;

trait TrackingTrait
{
    /**
     * @var bool
     */
    private $openTracking = false;

    /**
     * @var bool
     */
    private $linkTracking = false;

    /**
     * @var bool
     */
    private $bounceTracking = false;

    /**
     * @var bool
     */
    private $complaintTracking = false;

    /**
     * @var bool
     */
    private $deliveryTracking = false;

    /**
     * @var bool
     */
    private $rejectTracking = false;

    /**
     * @var BatchContract
     */
    private $batch;

    /**
     * Set tracking
     *
     * @param AbstractPart $setupTracking
     * @param SentEmailContract $sentEmail
     *
     * @return string
     *
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws NotLoadedException
     * @throws StrictException
     * @throws Exception
     */
    public function setupTracking(AbstractPart $setupTracking, SentEmailContract $sentEmail): string
    {
        $this->batch = null;

        $mailProcessor = new MailProcessor($sentEmail, $setupTracking->toString());

        if ($this->openTracking) {
            $mailProcessor->openTracking();
        }

        if ($this->linkTracking) {
            $mailProcessor->linkTracking();
        }

        return $mailProcessor->getEmailBody();
    }

    /**
     * Set batch identifier
     *
     * @param string $batch
     * @return SesMailerInterface
     * @throws Exception
     */
    public function setBatch(string $batch): SesMailerInterface
    {
        $batchModel = ModelResolver::get('Batch')::query();

        $batchResult = $batchModel->where('name', $batch)->first();

        if (! $batchResult) {
            $batchResult = $batchModel->create(['name' => $batch]);
        }

        $this->batch = $batchResult;

        return $this;
    }

    /**
     * Get batch identifier
     *
     * @return BatchContract|null
     */
    public function getBatch(): ?BatchContract
    {
        return $this->batch;
    }

    /**
     * Get batch ID
     * 
     * @return int|null
     */
    public function getBatchId(): ?int 
    {
        return $this->batch?->getId();
    }
    
    /**
     * Enable open tracking
     *
     * @return SesMailerInterface
     */
    public function enableOpenTracking(): SesMailerInterface
    {
        $this->openTracking = true;

        return $this;
    }

    /**
     * Enable link tracking
     *
     * @return SesMailerInterface
     */
    public function enableLinkTracking(): SesMailerInterface
    {
        $this->linkTracking = true;

        return $this;
    }

    /**
     * Enable bounce tracking
     *
     * @return SesMailerInterface
     */
    public function enableBounceTracking(): SesMailerInterface
    {
        $this->bounceTracking = true;

        return $this;
    }

    /**
     * Enable complaint tracking
     *
     * @return SesMailerInterface
     */
    public function enableComplaintTracking(): SesMailerInterface
    {
        $this->complaintTracking = true;

        return $this;
    }

    /**
     * Enable delivery tracking
     *
     * @return SesMailerInterface
     */
    public function enableDeliveryTracking(): SesMailerInterface
    {
        $this->deliveryTracking = true;

        return $this;
    }

    /**
     * Enable reject tracking
     *
     * @return SesMailerInterface
     */
    public function enableRejectTracking(): SesMailerInterface
    {
        $this->rejectTracking = true;

        return $this;
    }

    /**
     * Disable open tracking
     *
     * @return SesMailerInterface
     */
    public function disableOpenTracking(): SesMailerInterface
    {
        $this->openTracking = false;

        return $this;
    }

    /**
     * Disable link tracking
     *
     * @return SesMailerInterface
     */
    public function disableLinkTracking(): SesMailerInterface
    {
        $this->linkTracking = false;

        return $this;
    }

    /**
     * Disable bounce tracking
     *
     * @return SesMailerInterface
     */
    public function disableBounceTracking(): SesMailerInterface
    {
        $this->bounceTracking = false;

        return $this;
    }

    /**
     * Disable complaint tracking
     *
     * @return SesMailerInterface
     */
    public function disableComplaintTracking(): SesMailerInterface
    {
        $this->complaintTracking = false;

        return $this;
    }

    /**
     * Disable delivery tracking
     *
     * @return SesMailerInterface
     */
    public function disableDeliveryTracking(): SesMailerInterface
    {
        $this->deliveryTracking = false;

        return $this;
    }

    /**
     * Disable reject tracking
     *
     * @return SesMailerInterface
     */
    public function disableRejectTracking(): SesMailerInterface
    {
        $this->rejectTracking = false;

        return $this;
    }

    /**
     * Enable all tracking
     *
     * @return SesMailerInterface
     */
    public function enableAllTracking(): SesMailerInterface
    {
        return $this->enableOpenTracking()
            ->enableLinkTracking()
            ->enableBounceTracking()
            ->enableComplaintTracking()
            ->enableDeliveryTracking();
    }

    /**
     * Disable all tracking
     *
     * @return SesMailerInterface
     */
    public function disableAllTracking(): SesMailerInterface
    {
        return $this->disableOpenTracking()
            ->disableLinkTracking()
            ->disableBounceTracking()
            ->disableComplaintTracking()
            ->disableDeliveryTracking();
    }

    /**
     * Get tracking settings
     *
     * @return array<string, bool>
     */
    public function trackingSettings(): array
    {
        return [
            'openTracking' => $this->openTracking,
            'linkTracking' => $this->linkTracking,
            'bounceTracking' => $this->bounceTracking,
            'complaintTracking' => $this->complaintTracking,
            'deliveryTracking' => $this->deliveryTracking
        ];
    }
}
