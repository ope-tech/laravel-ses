<?php

namespace Juhasev\LaravelSes\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Juhasev\LaravelSes\Contracts\BatchContract;
use Juhasev\LaravelSes\ModelResolver;

class Batch extends Model implements BatchContract
{
    protected $table = 'laravel_ses_batches';

    protected $guarded = [];

    /**
     * Model relation to sent emails
     * @return HasMany
     * @throws Exception
     */
    public function sentEmails()
    {
        return $this->hasMany(ModelResolver::get('SentEmail'));
    }

    /**
     * Resolve
     *
     * @param string $name
     * @return BatchContract|null
     */
    public static function resolve(string $name): ?BatchContract
    {
        return self::where('name', $name)->first();
    }
}
