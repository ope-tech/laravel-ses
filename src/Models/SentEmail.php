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

   
    /**
     * Set delivery time for the email
     *
     * @param $time
     */
    public function setDeliveredAt($time)
    {
        $this->delivered_at = $time;
        $this->save();
    }

}
