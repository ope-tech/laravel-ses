<?php

namespace Juhasev\LaravelSes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juhasev\LaravelSes\Contracts\EmailOpenContract;
use Juhasev\LaravelSes\ModelResolver;

class EmailOpen extends Model implements EmailOpenContract
{
    protected $table = 'laravel_ses_email_opens';
    
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'sent_email_id' => 'integer',
        'opened_at' => 'datetime',
    ];

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

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->getKey();
    }
}
