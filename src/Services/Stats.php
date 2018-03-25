<?php

namespace oliveready7\LaravelSes\Services;

use oliveready7\LaravelSes\Models\SentEmail;
use oliveready7\LaravelSes\Models\EmailLink;
use oliveready7\LaravelSes\Models\EmailBounce;
use oliveready7\LaravelSes\Models\EmailComplaint;
use oliveready7\LaravelSes\Models\EmailOpen;

class Stats
{
    public static function statsForEmail($email)
    {
        return [
            'counts' => [
                'sent_emails' => SentEmail::whereEmail($email)->count(),
                'deliveries' => SentEmail::whereEmail($email)->whereNotNull('delivered_at')->count(),
                'opens' => EmailOpen::whereEmail($email)->whereNotNull('opened_at')->count(),
                'bounces' => EmailBounce::whereEmail($email)->whereNotNull('bounced_at')->count(),
                'complaints' => EmailComplaint::whereEmail($email)->whereNotNull('complained_at')->count(),
                'click_throughs' => EmailLink::join(
                        'laravel_ses_sent_emails',
                        'laravel_ses_sent_emails.id',
                        'laravel_ses_email_links.sent_email_id'
                    )
                    ->where('laravel_ses_sent_emails.email', '=', $email)
                    ->whereClicked(true)
                    ->count(\DB::raw('DISTINCT(laravel_ses_sent_emails.id)')) // if a user clicks two different links on one campaign, only one is counted
            ],
            'data' => [
                'sent_emails' => SentEmail::whereEmail($email)->get(),
                'deliveries' => SentEmail::whereEmail($email)->whereNotNull('delivered_at')->get(),
                'opens' => EmailOpen::whereEmail($email)->whereNotNull('opened_at')->get(),
                'bounces' => EmailComplaint::whereEmail($email)->whereNotNull('bounced_at')->get(),
                'complaints' => EmailComplaint::whereEmail($email)->whereNotNull('complained_at')->get(),
                'click_throughs' => EmailLink::join(
                    'laravel_ses_sent_emails',
                    'laravel_ses_sent_emails.id',
                    'laravel_ses_email_links.sent_email_id'
                )
                ->where('laravel_ses_sent_emails.email', '=', $email)
                ->whereClicked(true)
                ->get()
            ]
        ];
    }

    public static function statsForBatch($batchName)
    {
        return [
            'send_count' => SentEmail::numberSentForBatch($batchName),
            'deliveries' => SentEmail::deliveriesForBatch($batchName),
            'opens' => SentEmail::opensForBatch($batchName),
            'bounces' => SentEmail::bouncesForBatch($batchName),
            'complaints' => SentEmail::complaintsForBatch($batchName),
            'click_throughs' => SentEmail::getAmountOfUsersThatClickedAtLeastOneLink($batchName),
            'link_popularity' => SentEmail::getLinkPopularityOrder($batchName)
        ];
    }
}
