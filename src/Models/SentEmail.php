<?php

namespace oliveready7\LaravelSes\Models;

use Illuminate\Database\Eloquent\Model;
use oliveready7\LaravelSes\Models\EmailLink;

class SentEmail extends Model
{
    protected $table = 'laravel_ses_sent_emails';

    protected $guarded = [];

    public function setDeliveredAt($time) {
        $this->delivered_at = $time;
        $this->save();
    }

    public function emailOpen() {
        return $this->hasOne('oliveready7\LaravelSes\Models\EmailOpen');
    }

    public function emailLinks() {
        return $this->hasMany('oliveready7\LaravelSes\Models\EmailLink');
    }

    public function emailBounce() {
        return $this->hasOne('oliveready7\LaravelSes\Models\EmailBounce');
    }

    public static function numberSentForBatch($batchName) {
        return self::whereBatch($batchName)
            ->count();
    }

    public static function opensForBatch($batchName) {
        return self::join(
                'laravel_ses_email_opens',
                'laravel_ses_sent_emails.id',
                'laravel_ses_email_opens.sent_email_id'
            )
            ->where('laravel_ses_sent_emails.batch', $batchName)
            ->whereNotNull('laravel_ses_email_opens.opened_at')
            ->count();
    }

    public static function bouncesForBatch($batchName) {
        return self::join(
                'laravel_ses_email_bounces',
                'laravel_ses_sent_emails.id',
                'laravel_ses_email_bounces.sent_email_id'
            )
            ->where('laravel_ses_sent_emails.batch', $batchName)
            ->whereNotNull('laravel_ses_email_bounces.bounced_at')
            ->count();
    }

    public static function complaintsForBatch($batchName) {
        return self::join(
                'laravel_ses_email_complaints',
                'laravel_ses_sent_emails.id',
                'laravel_ses_email_complaints.sent_email_id'
            )
            ->where('laravel_ses_sent_emails.batch', $batchName)
            ->whereNotNull('laravel_ses_email_complaints.complained_at')
            ->count();
    }

    public static function deliveriesForBatch($batchName) {
        return self::whereBatch($batchName)
            ->whereNotNull('delivered_at')
            ->count();
    }

    public static function getAmountOfUsersThatClickedAtLeastOneLink($batchName) {
        return self::where('laravel_ses_sent_emails.batch', $batchName)
            ->join('laravel_ses_email_links', function($join) {
                $join
                    ->on('laravel_ses_sent_emails.id', '=', 'sent_email_id')
                    ->where('laravel_ses_email_links.clicked', '=', true);
            })
            ->select('email')
            ->count(\DB::raw('DISTINCT(email)'));
    }

    public static function getLinkPopularityOrder($batchName) {
        return self::where('laravel_ses_sent_emails.batch', $batchName)
            ->join('laravel_ses_email_links', function($join) {
                $join
                    ->on('laravel_ses_sent_emails.id', '=', 'sent_email_id')
                    ->where('laravel_ses_email_links.clicked', '=', true);
            })
            ->get()
            ->groupBy('original_url')
            ->map(function($linkClicks) {
                return ['clicks' => $linkClicks->count()];
            })
            ->sortByDesc('clicks');
    }


    public static function statsForBatch($batchName) {
        return [
            'send_count' => self::numberSentForBatch($batchName),
            'deliveries' => self::deliveriesForBatch($batchName),
            'opens' => self::opensForBatch($batchName),
            'bounces' => self::bouncesForBatch($batchName),
            'complaints' => self::complaintsForBatch($batchName),
            'click_throughs' => self::getAmountOfUsersThatClickedAtLeastOneLink($batchName),
            'link_popularity' => self::getLinkPopularityOrder($batchName)
        ];


    }
}
