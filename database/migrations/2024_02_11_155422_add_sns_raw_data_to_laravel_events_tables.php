<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('laravel_ses_email_bounces', function (Blueprint $table) {
            $table->json('sns_raw_data')->nullable()->after('bounced_at');
        });

        Schema::table('laravel_ses_email_complaints', function (Blueprint $table) {
            $table->json('sns_raw_data')->nullable()->after('complained_at');
        });

        Schema::table('laravel_ses_email_opens', function (Blueprint $table) {
            $table->json('sns_raw_data')->nullable()->after('opened_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laravel_ses_email_bounces', function (Blueprint $table) {
            $table->dropColumn('sns_raw_data');
        });

        Schema::table('laravel_ses_email_complaints', function (Blueprint $table) {
            $table->dropColumn('sns_raw_data');
        });

        Schema::table('laravel_ses_email_opens', function (Blueprint $table) {
            $table->dropColumn('sns_raw_data');
        });
    }
};
