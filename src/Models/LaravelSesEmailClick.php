<?php

namespace OpeTech\LaravelSes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaravelSesEmailClick extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'clicked_at' => 'datetime',
        'sns_raw_data' => 'json',
        'link_tags' => 'json',
    ];
}
