<?php

namespace Juhasev\LaravelSes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juhasev\LaravelSes\Contracts\EmailComplaintContract;
use Juhasev\LaravelSes\ModelResolver;

class EmailComplaint extends Model implements EmailComplaintContract
{
    protected $table = 'laravel_ses_email_complaints';

    public $timestamps = false;
    
    protected $guarded = [];

    /**
     * Relation ship
     *
     * @return BelongsTo
     * @throws \Exception
     */
    public function sentEmail()
    {
        return $this->belongsTo(ModelResolver::get('SentEmail'));
    }
}
