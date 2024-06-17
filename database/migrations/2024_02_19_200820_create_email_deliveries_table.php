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
        Schema::create('laravel_ses_email_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sent_email_id');
            $table->string('message_id');
            $table->dateTime('delivered_at')->nullable();
            $table->json('sns_raw_data')->nullable();

            $table->index('message_id');
            $table->index('sent_email_id');
            $table->index('delivered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laravel_ses_email_deliveries');
    }
};
