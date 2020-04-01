<?php

namespace Juhasev\LaravelSes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juhasev\LaravelSes\Contracts\EmailRejectContract;
use Juhasev\LaravelSes\ModelResolver;

class EmailReject extends Model implements EmailRejectContract
{
    protected $table = 'laravel_ses_email_rejects';

    public $timestamps = false;
    
    protected $guarded = [];

    /**
     * Relation ship to parent
     *
     * @return BelongsTo
     * @throws \Exception
     */
    public function sentEmail()
    {
        return $this->belongsTo(ModelResolver::get('SentEmail'));
    }
}
