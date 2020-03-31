<?php

namespace Juhasev\LaravelSes\Services;

use Juhasev\LaravelSes\ModelResolver;

class Stats
{
    public static function statsForEmail($email)
    {

        return [
            'counts' => [
                'sent_emails' => ModelResolver::get('SentEmail')::whereEmail($email)->count(),
                'deliveries' => ModelResolver::get('SentEmail')::whereEmail($email)->whereNotNull('delivered_at')->count(),
                'opens' => ModelResolver::get('EmailOpen')::whereEmail($email)->whereNotNull('opened_at')->count(),
                'bounces' => ModelResolver::get('EmailBounce')::whereEmail($email)->whereNotNull('bounced_at')->count(),
                'complaints' => ModelResolver::get('EmailComplaint')::whereEmail($email)->whereNotNull('complained_at')->count(),
                'rejects' => ModelResolver::get('EmailReject')::whereEmail($email)->whereNotNull('rejected_at')->count(),
                'click_throughs' => ModelResolver::get('EmailLink')::join(
                    'laravel_ses_sent_emails',
                    'laravel_ses_sent_emails.id',
                    'laravel_ses_email_links.sent_email_id'
                )
                    ->where('laravel_ses_sent_emails.email', '=', $email)
                    ->whereClicked(true)
                    ->count(\DB::raw('DISTINCT(laravel_ses_sent_emails.id)')) // if a user clicks two different links on one campaign, only one is counted
            ],
            'data' => [
                'sent_emails' => ModelResolver::get('SentEmail')::whereEmail($email)->get()->toArray(),
                'deliveries' => ModelResolver::get('SentEmail')::whereEmail($email)->whereNotNull('delivered_at')->get()->toArray(),
                'opens' => ModelResolver::get('EmailOpen')::whereEmail($email)->whereNotNull('opened_at')->get()->toArray(),
                'bounces' => ModelResolver::get('EmailComplaint')::whereEmail($email)->whereNotNull('bounced_at')->get()->toArray(),
                'complaints' => ModelResolver::get('EmailComplaint')::whereEmail($email)->whereNotNull('complained_at')->get()->toArray(),
                'rejects' => ModelResolver::get('EmailReject')::whereEmail($email)->whereNotNull('rejected_at')->get()->toArray(),
                'click_throughs' => ModelResolver::get('EmailLink')::join(
                    'laravel_ses_sent_emails',
                    'laravel_ses_sent_emails.id',
                    'laravel_ses_email_links.sent_email_id'
                )
                    ->where('laravel_ses_sent_emails.email', '=', $email)
                    ->whereClicked(true)
                    ->get()
                    ->toArray()
            ]
        ];
    }

    public static function statsForBatch($batchName)
    {
        return [
            'send_count' => ModelResolver::get('SentEmail')::numberSentForBatch($batchName),
            'deliveries' => ModelResolver::get('SentEmail')::deliveriesForBatch($batchName),
            'opens' => ModelResolver::get('SentEmail')::opensForBatch($batchName),
            'bounces' => ModelResolver::get('SentEmail')::bouncesForBatch($batchName),
            'complaints' => ModelResolver::get('SentEmail')::complaintsForBatch($batchName),
            'rejects' => ModelResolver::get('SentEmail')::rejectsForBatch($batchName),
            'click_throughs' => ModelResolver::get('SentEmail')::getAmountOfUsersThatClickedAtLeastOneLink($batchName),
            'link_popularity' => ModelResolver::get('SentEmail')::getLinkPopularityOrder($batchName)
        ];
    }
}
