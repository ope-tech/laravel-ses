<?php

namespace Juhasev\LaravelSes\Models;

use Illuminate\Database\Eloquent\Model;

class EmailBounce extends Model
{
    protected $table = 'laravel_ses_email_bounces';

    protected $guarded = [];
}
