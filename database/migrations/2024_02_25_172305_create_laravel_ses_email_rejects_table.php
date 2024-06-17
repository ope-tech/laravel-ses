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
        Schema::create('laravel_ses_email_rejects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sent_email_id');
            $table->string('message_id');
            $table->text('reason');

            $table->json('sns_raw_data')->nullable();
            $table->index('message_id');
            $table->index('sent_email_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laravel_ses_email_rejects');
    }
};
