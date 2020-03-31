<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRejectTrackingToSentEmails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('laravel_ses_sent_emails', function (Blueprint $table) {
            $table->boolean('reject_tracking')->default(false)->after('bounce_tracking');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('laravel_ses_sent_emails', function (Blueprint $table) {
            $table->dropColumn('reject_tracking');
        });
    }
}
