<?php

namespace Juhasev\LaravelSes\Repositories;

use Exception;
use Illuminate\Support\Facades\DB;
use Juhasev\LaravelSes\Contracts\BatchContract;
use Juhasev\LaravelSes\ModelResolver;

class BatchStatRepository
{
    /**
     * Get sent count for batch
     *
     * @param BatchContract $batch
     * @return mixed
     * @throws Exception
     */
    public static function getSentCount(BatchContract $batch)
    {
        return ModelResolver::get('SentEmail')::where('batch_id', $batch->id)->count();
    }

    /**
     * Get opened count for batch
     *
     * @param BatchContract $batch
     * @return int
     * @throws Exception
     */
    public static function getOpenedCount(BatchContract $batch): int
    {
        return ModelResolver::get('SentEmail')::where('batch_id', $batch->id)
            ->join(
                'laravel_ses_email_opens',
                'laravel_ses_sent_emails.id',
                'laravel_ses_email_opens.sent_email_id'
            )
            ->whereNotNull('opened_at')
            ->count();
    }

    /**
     * Get bounced count for batch
     *
     * @param BatchContract $batch
     * @return int
     * @throws Exception
     */
    public static function getBouncedCount(BatchContract $batch): int
    {
        return ModelResolver::get('SentEmail')::where('batch_id', $batch->id)
            ->join(
                'laravel_ses_email_bounces',
                'laravel_ses_sent_emails.id',
                'laravel_ses_email_bounces.sent_email_id'
            )
            ->whereNotNull('bounced_at')
            ->count();
    }

    /**
     * Get complained count for batch
     *
     * @param BatchContract $batch
     * @return int
     * @throws Exception
     */
    public static function getComplaintsCount(BatchContract $batch): int
    {
        return ModelResolver::get('SentEmail')::where('batch_id', $batch->id)
        ->join(
            'laravel_ses_email_complaints',
            'laravel_ses_sent_emails.id',
            'laravel_ses_email_complaints.sent_email_id'
        )
            ->whereNotNull('complained_at')
            ->count();
    }

    /**
     * Get deliveries count for barch
     *
     * @param BatchContract $batch
     * @return int
     * @throws Exception
     */
    public static function getDeliveriesCount(BatchContract $batch): int
    {
        return ModelResolver::get('SentEmail')::where('batch_id', $batch->id)
            ->whereNotNull('delivered_at')
            ->count();
    }

    /**
     * Get clicks count for batch
     *
     * @param BatchContract $batch
     * @return int
     * @throws Exception
     */
    public static function getClicksCount(BatchContract $batch): int
    {
        return ModelResolver::get('SentEmail')::where('laravel_ses_sent_emails.batch_id', $batch->id)
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
     * @param BatchContract $batch
     * @return array
     * @throws Exception
     */
    public static function getLinkPopularity(BatchContract $batch): array
    {
        return ModelResolver::get('SentEmail')::where('laravel_ses_sent_emails.batch_id', $batch->id)
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