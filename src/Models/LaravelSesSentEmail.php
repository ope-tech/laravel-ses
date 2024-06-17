<?php

namespace OpeTech\LaravelSes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpeTech\LaravelSes\Database\Factories\LaravelSesSentEmailFactory;

class LaravelSesSentEmail extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'compaint_tracking' => 'boolean',
        'delivery_tracking' => 'boolean',
        'bounce_tracking' => 'boolean',
    ];

    protected static function newFactory()
    {
        return LaravelSesSentEmailFactory::new();
    }
}
