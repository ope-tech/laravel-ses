<?php

namespace Juhasev\LaravelSes\Repositories;

use Illuminate\Support\Facades\DB;
use Juhasev\LaravelSes\ModelResolver;

class BatchStatRepository
{
    /**
     * Get sent count for batch
     * 
     * @param string $batchName
     * @return mixed
     * @throws \Exception
     */
    public static function getSentCount(string $batchName)
    {
        return ModelResolver::get('SentEmail')::whereBatch($batchName)->count();
    }

    /**
     * Get opened count for batch
     * 
     * @param string $batchName
     * @return int
     * @throws \Exception
     */
    public static function getOpenedCount(string $batchName): int
    {
        return ModelResolver::get('SentEmail')::join(
            'laravel_ses_email_opens',
            'laravel_ses_sent_emails.id',
            'laravel_ses_email_opens.sent_email_id'
        )
            ->where('laravel_ses_sent_emails.batch', $batchName)
            ->whereNotNull('laravel_ses_email_opens.opened_at')
            ->count();
    }

    /**
     * Get bounced count for batch
     * 
     * @param string $batchName
     * @return int
     * @throws \Exception
     */
    public static function getBouncedCount(string $batchName): int
    {
        return ModelResolver::get('SentEmail')::join(
            'laravel_ses_email_bounces',
            'laravel_ses_sent_emails.id',
            'laravel_ses_email_bounces.sent_email_id'
        )
            ->where('laravel_ses_sent_emails.batch', $batchName)
            ->whereNotNull('laravel_ses_email_bounces.bounced_at')
            ->count();
    }

    /**
     * Get complained count for batch
     * 
     * @param string $batchName
     * @return int
     * @throws \Exception
     */
    public static function getComplaintsCount(string $batchName): int
    {
        return ModelResolver::get('SentEmail')::join(
            'laravel_ses_email_complaints',
            'laravel_ses_sent_emails.id',
            'laravel_ses_email_complaints.sent_email_id'
        )
            ->where('laravel_ses_sent_emails.batch', $batchName)
            ->whereNotNull('laravel_ses_email_complaints.complained_at')
            ->count();
    }

    /**
     * Get reject count for batch
     * 
     * @param string $batchName
     * @return int
     * @throws \Exception
     */
    public static function getRejectsCount(string $batchName): int
    {
        return ModelResolver::get('SentEmail')::join(
            'laravel_ses_email_rejects',
            'laravel_ses_sent_emails.id',
            'laravel_ses_email_rejects.sent_email_id'
        )
            ->where('laravel_ses_sent_emails.batch', $batchName)
            ->whereNotNull('laravel_ses_email_rejects.rejected_at')
            ->count();
    }

    /**
     * Get deliveries count for barch
     * 
     * @param string $batchName
     * @return int
     * @throws \Exception
     */
    public static function getDeliveriesCount(string $batchName): int
    {
        return ModelResolver::get('SentEmail')::whereBatch($batchName)
            ->whereNotNull('delivered_at')
            ->count();
    }

    /**
     * Get clicks count for batch
     * 
     * @param string $batchName
     * @return int
     * @throws \Exception
     */
    public static function getClicksCount(string $batchName): int
    {
        return ModelResolver::get('SentEmail')::where('laravel_ses_sent_emails.batch', $batchName)
            ->join('laravel_ses_email_links', function ($join) {
                $join
                    ->on('laravel_ses_sent_emails.id', '=', 'sent_email_id')
                    ->where('laravel_ses_email_links.clicked', '=', true);
            })
            ->select('email')
            ->count(DB::raw('DISTINCT(email)'));
    }

    /**
     * Get link popularity sorted (most popular first)
     * 
     * @param string $batchName
     * @return array
     * @throws \Exception
     */
    public static function getLinkPopularity(string $batchName): array
    {
        return ModelResolver::get('SentEmail')::where('laravel_ses_sent_emails.batch', $batchName)
            ->join('laravel_ses_email_links', function ($join) {
                $join
                    ->on('laravel_ses_sent_emails.id', '=', 'sent_email_id')
                    ->where('laravel_ses_email_links.clicked', '=', true);
            })
            ->get()
            ->groupBy('original_url')
            ->map(function ($linkClicks) {
                return ['clicks' => $linkClicks->count()];
            })
            ->sortByDesc('clicks')
            ->toArray();
    }
}