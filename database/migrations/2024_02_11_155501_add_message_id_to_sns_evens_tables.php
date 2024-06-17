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
            $table->string('message_id')->nullable()->after('sent_email_id');
            $table->index('message_id');
        });

        Schema::table('laravel_ses_email_complaints', function (Blueprint $table) {
            $table->string('message_id')->nullable()->after('sent_email_id');
            $table->index('message_id');
        });

        Schema::table('laravel_ses_email_opens', function (Blueprint $table) {
            $table->string('message_id')->nullable()->after('sent_email_id');
            $table->index('message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laravel_ses_email_bounces', function (Blueprint $table) {
            $table->dropColumn('message_id');
        });

        Schema::table('laravel_ses_email_complaints', function (Blueprint $table) {
            $table->dropColumn('message_id');
        });

        Schema::table('laravel_ses_email_opens', function (Blueprint $table) {
            $table->dropColumn('message_id');
        });
    }
};
