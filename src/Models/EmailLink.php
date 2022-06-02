<?php

namespace Juhasev\LaravelSes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juhasev\LaravelSes\Contracts\EmailLinkContract;
use Juhasev\LaravelSes\ModelResolver;

class EmailLink extends Model implements EmailLinkContract
{
    protected $table = 'laravel_ses_email_links';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'sent_email_id' => 'integer',
        'clicked' => 'boolean',
        'click_count' => 'integer',
    ];

    /**
     * Relationship to parent
     *
     * @return BelongsTo
     * @throws \Exception
     */
    public function sentEmail()
    {
        return $this->belongsTo(ModelResolver::get('SentEmail'));
    }

    /**
     * Get clicked
     *
     * @param $clicked
     * @return $this
     */
    public function setClicked(bool $clicked)
    {
        $this->clicked = $clicked;
        $this->save();
        return $this;
    }

    /**
     * Increment click count
     *
     * @return $this
     */
    public function incrementClickCount()
    {
        $this->click_count++;
        $this->save();
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->getKey();
    }

    /**
     * @return mixed
     */
    public function originalUrl()
    {
        return $this->original_url;
    }
}
