<?php

namespace Juhasev\LaravelSes\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Juhasev\LaravelSes\ModelResolver;

class EmailRepository
{
    /**
     * Get all sent emails
     *
     * @param $email
     * @return Collection
     * @throws \Exception
     */
    public static function getSent($email): Collection
    {
        return ModelResolver::get('SentEmail')::whereEmail($email)->get();
    }

    /**
     * Get deliveries
     *
     * @param $email
     * @return Collection
     * @throws \Exception
     */
    public static function getDeliveries($email): Collection
    {
        return ModelResolver::get('SentEmail')::whereEmail($email)->whereNotNull('delivered_at')->get();
    }

    /**
     * Get opens
     *
     * @param $email
     * @return Collection
     * @throws \Exception
     */
    public static function getOpens($email): Collection
    {
        return ModelResolver::get('SentEmail')::whereEmail($email)
            ->with('emailOpen')
            ->whereHas('emailOpen', function ($query) {
                $query->whereNotNull('opened_at');
            })->get();
    }

    /**
     * Get bounces
     *
     * @param $email
     * @return Collection
     * @throws \Exception
     */
    public static function getBounces($email): Collection
    {
        return ModelResolver::get('SentEmail')::whereEmail($email)
            ->with('emailBounce')
            ->whereHas('emailBounce', function ($query) {
                $query->whereNotNull('bounced_at');
            })->get();
    }

    /**
     * Get complaints
     *
     * @param $email
     * @return Collection
     * @throws \Exception
     */
    public static function getComplaints($email): Collection
    {
        return ModelResolver::get('SentEmail')::whereEmail($email)
            ->with('emailComplaint')
            ->whereHas('emailComplaint', function ($query) {
                $query->whereNotNull('complained_at');
            })->get();
    }

    /**
     * Get all emails that have been clicked
     *
     * @param $email
     * @return Collection
     * @throws \Exception
     */

    public static function getClicks($email): Collection
    {
        return ModelResolver::get('SentEmail')::whereEmail($email)
            ->with(['emailLinks' => function ($query) {
                $query->where('clicked', true);
            }])
            ->whereHas('emailLinks', function ($query) {
                $query->where('clicked', true);
            })->get();
    }
}