<?php

namespace Juhasev\LaravelSes\Models;

use Illuminate\Database\Eloquent\Model;
use Juhasev\LaravelSes\Contracts\EmailBounceContract;

class EmailBounce extends Model implements EmailBounceContract
{
    protected $table = 'laravel_ses_email_bounces';

    protected $guarded = [];
}
