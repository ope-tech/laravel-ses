<?php

namespace Juhasev\LaravelSes;

use Juhasev\LaravelSes\Models\SentEmail;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\CurlException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;

trait TrackingTrait
{
    private $openTracking = false;
    private $linkTracking = false;
    private $bounceTracking = false;
    private $complaintTracking = false;
    private $deliveryTracking = false;
    private $batch;

    /**
     * Set tracking
     *
     * @param $emailBody
     * @param SentEmail $sentEmail
     *
     * @return string
     *
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws CurlException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public function setupTracking($emailBody, SentEmail $sentEmail)
    {
        $mailProcessor = new MailProcessor($sentEmail, $emailBody);

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
     * @return TrackingTrait
     */
    public function setBatch(string $batch): TrackingTrait
    {
        $this->batch = $batch;
        return $this;
    }

    /**
     * Get batch identifier
     *
     * @return string
     */
    public function getBatch(): string
    {
        return $this->batch;
    }

    /**
     * Enable open tracking
     *
     * @return TrackingTrait
     */
    public function enableOpenTracking() : TrackingTrait
    {
        $this->openTracking = true;
        return $this;
    }

    /**
     * Enable link tracking
     *
     * @return TrackingTrait
     */
    public function enableLinkTracking(): TrackingTrait
    {
        $this->linkTracking = true;
        return $this;
    }

    /**
     * Enable bounce tracking
     *
     * @return TrackingTrait
     */
    public function enableBounceTracking(): TrackingTrait
    {
        $this->bounceTracking = true;
        return $this;
    }

    /**
     * Enable complaint tracking
     *
     * @return TrackingTrait
     */
    public function enableComplaintTracking(): TrackingTrait
    {
        $this->complaintTracking = true;
        return $this;
    }

    /**
     * Enable delivery tracking
     *
     * @return TrackingTrait
     */
    public function enableDeliveryTracking(): TrackingTrait
    {
        $this->deliveryTracking = true;
        return $this;
    }

    /**
     * Disable open tracking
     *
     * @return TrackingTrait
     */
    public function disableOpenTracking(): TrackingTrait
    {
        $this->openTracking = false;
        return $this;
    }

    /**
     * Disable link tracking
     *
     * @return TrackingTrait
     */
    public function disableLinkTracking(): TrackingTrait
    {
        $this->linkTracking = false;
        return $this;
    }

    /**
     * Disable bounce tracking
     *
     * @return TrackingTrait
     */
    public function disableBounceTracking(): TrackingTrait
    {
        $this->bounceTracking = false;
        return $this;
    }

    /**
     * Disable complaint tracking
     *
     * @return TrackingTrait
     */
    public function disableComplaintTracking(): TrackingTrait
    {
        $this->complaintTracking = false;
        return $this;
    }

    /**
     * Disable delivery tracking
     *
     * @return TrackingTrait
     */
    public function disableDeliveryTracking(): TrackingTrait
    {
        $this->deliveryTracking = false;
        return $this;
    }

    /**
     * Enable all tracking
     *
     * @return TrackingTrait
     */
    public function enableAllTracking(): TrackingTrait
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
     * @return TrackingTrait
     */
    public function disableAllTracking(): TrackingTrait
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
     * @return array
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
