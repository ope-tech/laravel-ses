<?php

namespace OpeTech\LaravelSes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaravelSesEmailBounce extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'bounced_at' => 'datetime',
        'sns_raw_data' => 'json',
    ];
}
