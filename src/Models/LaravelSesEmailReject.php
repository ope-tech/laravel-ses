<?php

namespace OpeTech\LaravelSes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaravelSesEmailReject extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'rejected_at' => 'datetime',
        'sns_raw_data' => 'json',
    ];
}
