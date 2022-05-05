<?php

namespace Juhasev\LaravelSes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juhasev\LaravelSes\Contracts\EmailBounceContract;
use Juhasev\LaravelSes\ModelResolver;

class EmailBounce extends Model implements EmailBounceContract
{
    public $timestamps = false;
    
    protected $table = 'laravel_ses_email_bounces';
    
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

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->getKey();
    }
}
