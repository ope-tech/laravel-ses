<?php

namespace Juhasev\LaravelSes\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Juhasev\LaravelSes\ModelResolver;

class SentEmail extends Model implements SentEmailContract
{
    protected $table = 'laravel_ses_sent_emails';

    protected $guarded = [];

    protected $casts = [
        'batch_id' => 'integer',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'complaint_tracking' => 'boolean',
        'delivery_tracking' => 'boolean',
        'bounce_tracking' => 'boolean',
    ];

    /**
     * Opened relationship
     *
     * @return HasOne
     * @throws Exception
     */
    public function emailOpen()
    {
        return $this->hasOne(ModelResolver::get('EmailOpen'));
    }

    /**
     * Email links relationship
     *
     * @return HasMany
     * @throws Exception
     */
    public function emailLinks()
    {
        return $this->hasMany(ModelResolver::get('EmailLink'));
    }

    /**
     * Email bounce relationship
     *
     * @return HasOne
     * @throws Exception
     */
    public function emailBounce()
    {
        return $this->hasOne(ModelResolver::get('EmailBounce'));
    }

    /**
     * Email complaint relationship
     *
     * @return HasOne
     * @throws Exception
     */
    public function emailComplaint()
    {
        return $this->hasOne(ModelResolver::get('EmailComplaint'));
    }

    /**
     * Email reject relationship
     *
     * @return HasOne
     * @throws Exception
     */
    public function emailReject()
    {
        return $this->hasOne(ModelResolver::get('EmailReject'));
    }

    /**
     * Parent relationship to batch
     *
     * @return BelongsTo
     * @throws Exception
     */
    public function batch()
    {
        return $this->belongsTo(ModelResolver::get('Batch'));
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

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->getKey();
    }
}
