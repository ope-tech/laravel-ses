<?php

namespace Juhasev\LaravelSes\Services;

use Juhasev\LaravelSes\Models\SentEmail;
use Juhasev\LaravelSes\Models\EmailLink;
use Juhasev\LaravelSes\Models\EmailBounce;
use Juhasev\LaravelSes\Models\EmailComplaint;
use Juhasev\LaravelSes\Models\EmailOpen;

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
                'sent_emails' => SentEmail::whereEmail($email)->get()->toArray(),
                'deliveries' => SentEmail::whereEmail($email)->whereNotNull('delivered_at')->get()->toArray(),
                'opens' => EmailOpen::whereEmail($email)->whereNotNull('opened_at')->get()->toArray(),
                'bounces' => EmailComplaint::whereEmail($email)->whereNotNull('bounced_at')->get()->toArray(),
                'complaints' => EmailComplaint::whereEmail($email)->whereNotNull('complained_at')->get()->toArray(),
                'click_throughs' => EmailLink::join(
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
