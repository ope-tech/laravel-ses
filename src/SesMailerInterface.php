<?php

namespace Juhasev\LaravelSes;

use Juhasev\LaravelSes\Models\SentEmail;

interface SesMailerInterface
{
    public function initMessage($message);

    public function statsForBatch(string $batchName): array;

    public function statsForEmail(string $batchName): array;

    public function setupTracking($setupTracking, SentEmail $sentEmail);

    public function setBatch(string $batch): SesMailerInterface;

    public function getBatch();

    public function enableOpenTracking(): SesMailerInterface;

    public function enableLinkTracking(): SesMailerInterface;

    public function enableBounceTracking(): SesMailerInterface;

    public function enableComplaintTracking(): SesMailerInterface;

    public function enableDeliveryTracking(): SesMailerInterface;

    public function disableOpenTracking(): SesMailerInterface;

    public function disableLinkTracking(): SesMailerInterface;

    public function disableBounceTracking(): SesMailerInterface;

    public function disableComplaintTracking(): SesMailerInterface;

    public function disableDeliveryTracking(): SesMailerInterface;

    public function enableAllTracking(): SesMailerInterface;

    public function disableAllTracking(): SesMailerInterface;

    public function trackingSettings(): array;
}
