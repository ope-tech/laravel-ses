<?php

namespace Juhasev\LaravelSes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Juhasev\LaravelSes\ModelResolver;

class SentEmail extends Model implements SentEmailContract
{
    protected $table = 'laravel_ses_sent_emails';

    protected $guarded = [];

    public function setDeliveredAt($time)
    {
        $this->delivered_at = $time;
        $this->save();
    }

    public function emailOpen()
    {
        return $this->hasOne(ModelResolver::get('EmailOpen'));
    }

    public function emailLinks()
    {
        return $this->hasMany(ModelResolver::get('EmailLink'));
    }

    public function emailBounce()
    {
        return $this->hasOne(ModelResolver::get('EmailBounce'));
    }

    public function emailComplaint()
    {
        return $this->hasOne(ModelResolver::get('EmailComplaint'));
    }

    public function emailReject()
    {
        return $this->hasOne(ModelResolver::get('EmailReject'));
    }

    public static function numberSentForBatch(string $batchName)
    {
        return self::whereBatch($batchName)
            ->count();
    }

    public static function opensForBatch(string $batchName)
    {
        return self::join(
                'laravel_ses_email_opens',
                'laravel_ses_sent_emails.id',
                'laravel_ses_email_opens.sent_email_id'
            )
            ->where('laravel_ses_sent_emails.batch', $batchName)
            ->whereNotNull('laravel_ses_email_opens.opened_at')
            ->count();
    }

    public static function bouncesForBatch(string $batchName)
    {
        return self::join(
                'laravel_ses_email_bounces',
                'laravel_ses_sent_emails.id',
                'laravel_ses_email_bounces.sent_email_id'
            )
            ->where('laravel_ses_sent_emails.batch', $batchName)
            ->whereNotNull('laravel_ses_email_bounces.bounced_at')
            ->count();
    }

    public static function complaintsForBatch(string $batchName)
    {
        return self::join(
                'laravel_ses_email_complaints',
                'laravel_ses_sent_emails.id',
                'laravel_ses_email_complaints.sent_email_id'
            )
            ->where('laravel_ses_sent_emails.batch', $batchName)
            ->whereNotNull('laravel_ses_email_complaints.complained_at')
            ->count();
    }

    public static function deliveriesForBatch(string $batchName)
    {
        return self::whereBatch($batchName)
            ->whereNotNull('delivered_at')
            ->count();
    }

    public static function getAmountOfUsersThatClickedAtLeastOneLink(string $batchName)
    {
        return self::where('laravel_ses_sent_emails.batch', $batchName)
            ->join('laravel_ses_email_links', function ($join) {
                $join
                    ->on('laravel_ses_sent_emails.id', '=', 'sent_email_id')
                    ->where('laravel_ses_email_links.clicked', '=', true);
            })
            ->select('email')
            ->count(DB::raw('DISTINCT(email)'));
    }

    public static function getLinkPopularityOrder(string $batchName): array
    {
        return self::where('laravel_ses_sent_emails.batch', $batchName)
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
