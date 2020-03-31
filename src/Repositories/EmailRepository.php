<?php

namespace Juhasev\LaravelSes\Repositories;

use Illuminate\Support\Collection;
use Juhasev\LaravelSes\ModelResolver;

class EmailRepository
{
    public static function getSent($email): Collection
    {
        return ModelResolver::get('SentEmail')::whereEmail($email)->get();
    }

    public static function getDeliveries($email): Collection
    {
        return ModelResolver::get('SentEmail')::whereEmail($email)->whereNotNull('delivered_at')->get();
    }

    public static function getOpens($email): Collection
    {
        return ModelResolver::get('EmailOpen')::whereEmail($email)->whereNotNull('opened_at')->get();
    }

    public static function getBounces($email): Collection
    {
        return ModelResolver::get('EmailComplaint')::whereEmail($email)->whereNotNull('bounced_at')->get();
    }

    public static function getComplaints($email): Collection
    {
        return ModelResolver::get('EmailComplaint')::whereEmail($email)->whereNotNull('complained_at')->get();
    }

    public static function getRejects($email): Collection
    {
        return ModelResolver::get('EmailReject')::whereEmail($email)->whereNotNull('rejected_at')->get();
    }

    public static function getClicks($email): Collection
    {
        return ModelResolver::get('EmailLink')::join(
            'laravel_ses_sent_emails',
            'laravel_ses_sent_emails.id',
            'laravel_ses_email_links.sent_email_id'
        )
            ->where('laravel_ses_sent_emails.email', '=', $email)
            ->whereClicked(true)
            ->get();
    }
}