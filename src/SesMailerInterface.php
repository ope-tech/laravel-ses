<?php

namespace Juhasev\LaravelSes;

use Juhasev\LaravelSes\Contracts\SentEmailContract;

interface SesMailerInterface
{
    public function initMessage($message);

    public function setupTracking($setupTracking, SentEmailContract $sentEmail);

    public function setBatch(string $batch): SesMailerInterface;

    public function getBatch();

    public function enableOpenTracking(): SesMailerInterface;

    public function enableLinkTracking(): SesMailerInterface;

    public function enableBounceTracking(): SesMailerInterface;

    public function enableComplaintTracking(): SesMailerInterface;

    public function enableDeliveryTracking(): SesMailerInterface;

    public function enableRejectTracking(): SesMailerInterface;

    public function disableOpenTracking(): SesMailerInterface;

    public function disableLinkTracking(): SesMailerInterface;

    public function disableBounceTracking(): SesMailerInterface;

    public function disableComplaintTracking(): SesMailerInterface;

    public function disableDeliveryTracking(): SesMailerInterface;

    public function disableRejectTracking(): SesMailerInterface;

    public function enableAllTracking(): SesMailerInterface;

    public function disableAllTracking(): SesMailerInterface;

    public function trackingSettings(): array;
}
