<?php

namespace Juhasev\LaravelSes\Models;

use Illuminate\Database\Eloquent\Model;
use Juhasev\LaravelSes\Contracts\EmailRejectContract;

class EmailReject extends Model implements EmailRejectContract
{
    protected $table = 'laravel_ses_email_rejects';

    protected $guarded = [];
}
