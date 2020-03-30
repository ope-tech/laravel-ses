<?php

namespace Juhasev\LaravelSes\Contracts;

interface SentEmailContract
{
    public function setDeliveredAt($time);
    public function emailOpen();
    public function emailLinks();
    public function emailBounce();
    public function emailComplaint();
    public static function numberSentForBatch(string $batchName);
    public static function opensForBatch(string $batchName);
    public static function bouncesForBatch(string $batchName);
    public static function complaintsForBatch(string $batchName);
    public static function deliveriesForBatch(string $batchName);
    public static function getAmountOfUsersThatClickedAtLeastOneLink(string $batchName);
    public static function getLinkPopularityOrder(string $batchName): array;
}