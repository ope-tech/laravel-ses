<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->bigIncrements('id');
            $table->unsignedInteger('batch_id')->nullable();
            $table->string('message_id');
            $table->string('email')->index();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->boolean('complaint_tracking')->default(false);
            $table->boolean('delivery_tracking')->default(false);
            $table->boolean('bounce_tracking')->default(false);
            $table->boolean('reject_tracking')->default(false);
            $table->timestamps();

            $table->foreign('batch_id')
                ->references('id')
                ->on('laravel_ses_batches')
                ->onDelete('cascade');
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
