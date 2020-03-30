<?php

namespace Juhasev\LaravelSes\Models;

use Illuminate\Database\Eloquent\Model;
use Juhasev\LaravelSes\Contracts\EmailLinkContract;

class EmailLink extends Model implements EmailLinkContract
{
    protected $table = 'laravel_ses_email_links';

    protected $guarded = [];

    protected $casts = [
        'clicked' => 'boolean'
    ];

    public function sentEmail()
    {
        $sentEmailModel = config('laravelses.models.sent_emails');

        return $this->belongsTo($sentEmailModel);
    }

    public function setClicked($clicked)
    {
        $this->clicked = $clicked;
        $this->save();
        return $this;
    }

    public function incrementClickCount()
    {
        $this->click_count++;
        $this->save();
        return $this;
    }
}
