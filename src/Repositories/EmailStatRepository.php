<?php

namespace Juhasev\LaravelSes\Repositories;

use Illuminate\Support\Facades\DB;
use Juhasev\LaravelSes\ModelResolver;

class EmailStatRepository
{
    /**
     * Get sent email count for give address
     *
     * @param $email
     * @return int
     * @throws \Exception
     */
    public static function getSentCount($email): int
    {
        return ModelResolver::get('SentEmail')::whereEmail($email)->count();
    }

    /**
     * Get delivered count for given address
     *
     * @param $email
     * @return int
     * @throws \Exception
     */
    public static function getDeliveriesCount($email): int
    {
        return ModelResolver::get('SentEmail')::whereEmail($email)->whereNotNull('delivered_at')->count();
    }

    /**
     * Get opened count for given address
     *
     * @param $email
     * @return mixed
     * @throws \Exception
     */
    public static function getOpenedCount($email)
    {
        return ModelResolver::get('SentEmail')::whereEmail($email)
            ->withCount(['emailOpen' => function ($query) {
                $query->whereNotNull('opened_at');
            }])->get()->sum('email_open_count');
    }

    /**
     * Get complaint count for give address
     *
     * @param $email
     * @return int
     * @throws \Exception
     */
    public static function getComplaintsCount($email): int
    {
        return ModelResolver::get('SentEmail')::whereEmail($email)
            ->withCount(['emailComplaint' => function ($query) {
                $query->whereNotNull('complained_at');
            }])->get()->sum('email_complaint_count');
    }

    /**
     * Get bounced count for given address
     *
     * @param $email
     * @return int
     * @throws \Exception
     */
    public static function getBouncedCount($email): int
    {
        return ModelResolver::get('SentEmail')::whereEmail($email)
            ->withCount(['emailBounce' => function ($query) {
                $query->whereNotNull('bounced_at');
            }])->get()->sum('email_bounce_count');
    }

    /**
     * Get rejected count for given address
     *
     * @param $email
     * @return int
     * @throws \Exception
     */
    public static function getRejectsCount($email): int
    {
        return ModelResolver::get('SentEmail')::whereEmail($email)
            ->withCount(['emailReject' => function ($query) {
                $query->whereNotNull('rejected_at');
            }])->get()->sum('email_reject_count');
    }

    /**
     * Get click through count
     * If a user clicks two different links on one campaign, only one is counted
     * 
     * @param $email
     * @return int
     * @throws \Exception
     */
    public static function getClicksCount($email): int
    {
        return ModelResolver::get('EmailLink')::join(
            'laravel_ses_sent_emails',
            'laravel_ses_sent_emails.id',
            'laravel_ses_email_links.sent_email_id'
        )
            ->where('laravel_ses_sent_emails.email', '=', $email)
            ->whereClicked(true)
            ->count(DB::raw('DISTINCT(laravel_ses_sent_emails.id)'));
    }
}