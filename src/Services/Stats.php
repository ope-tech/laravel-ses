<?php

namespace Juhasev\LaravelSes\Services;

use Exception;
use Juhasev\LaravelSes\Contracts\BatchContract;
use Juhasev\LaravelSes\Models\Batch;
use Juhasev\LaravelSes\Repositories\BatchStatRepository;
use Juhasev\LaravelSes\Repositories\EmailRepository;
use Juhasev\LaravelSes\Repositories\EmailStatRepository;

class Stats
{
    /**
     * Get stats for given email
     *
     * @param $email
     * @return array
     * @throws Exception
     */
    public static function statsForEmail($email): array
    {
        return [

            'sent' => EmailStatRepository::getSentCount($email),
            'deliveries' => EmailStatRepository::getDeliveriesCount($email),
            'opens' => EmailStatRepository::getOpenedCount($email),
            'bounces' => EmailStatRepository::getBouncedCount($email),
            'complaints' => EmailStatRepository::getComplaintsCount($email),
            'rejects' => EmailStatRepository::getRejectsCount($email),
            'clicks' => EmailStatRepository::getClicksCount($email)
        ];
    }

    /**
     * Get data for give email
     *
     * @param $email
     * @return array
     * @throws Exception
     */
    public static function dataForEmail($email): array
    {
        return [
            'sent' => EmailRepository::getSent($email),
            'deliveries' => EmailRepository::getDeliveries($email),
            'opens' => EmailRepository::getOpens($email),
            'bounces' => EmailRepository::getBounces($email),
            'complaints' => EmailRepository::getComplaints($email),
            'rejects' => EmailRepository::getRejects($email),
            'clicks' => EmailRepository::getClicks($email)
        ];
    }

    /**
     * Get stats for batch
     *
     * @param Batch $batch
     * @return array
     * @throws Exception
     */
    public static function statsForBatch(BatchContract $batch): array
    {
        return [
            'sent' => BatchStatRepository::getSentCount($batch),
            'deliveries' => BatchStatRepository::getDeliveriesCount($batch),
            'opens' => BatchStatRepository::getOpenedCount($batch),
            'bounces' => BatchStatRepository::getBouncedCount($batch),
            'complaints' => BatchStatRepository::getComplaintsCount($batch),
            'rejects' => BatchStatRepository::getRejectsCount($batch),
            'clicks' => BatchStatRepository::getClicksCount($batch),
            'link_popularity' => BatchStatRepository::getLinkPopularity($batch)
        ];
    }
}
