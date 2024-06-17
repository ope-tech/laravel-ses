<?php

namespace OpeTech\LaravelSes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaravelSesEmailComplaint extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'complained_at' => 'datetime',
        'sns_raw_data' => 'json',
    ];
}
