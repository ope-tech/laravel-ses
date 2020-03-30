<?php

namespace Juhasev\LaravelSes\Models;

use Illuminate\Database\Eloquent\Model;
use Juhasev\LaravelSes\Contracts\EmailComplaintContract;

class EmailComplaint extends Model implements EmailComplaintContract
{
    protected $table = 'laravel_ses_email_complaints';

    protected $guarded = [];
}
