<?php

namespace Juhasev\LaravelSes\Models;

use Illuminate\Database\Eloquent\Model;
use Juhasev\LaravelSes\Contracts\EmailOpenContract;

class EmailOpen extends Model implements EmailOpenContract
{
    protected $table = 'laravel_ses_email_opens';

    protected $guarded = [];
}
