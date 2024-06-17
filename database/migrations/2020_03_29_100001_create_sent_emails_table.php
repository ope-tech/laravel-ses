<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSentEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laravel_ses_sent_emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->string('message_id');
            $table->string('email');
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            // $table->boolean('complaint_tracking')->default(0);
            // $table->boolean('delivery_tracking')->default(0);
            // $table->boolean('bounce_tracking')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('email');
            $table->index('message_id');
            // $table->index('bounce_tracking');
            // $table->index('complaint_tracking');
            // $table->index('delivery_tracking');
            $table->index('delivered_at');

            // Foreign key constraint
            $table->foreign('batch_id')->references('id')->on('laravel_ses_batches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('laravel_ses_sent_emails');
    }
}
