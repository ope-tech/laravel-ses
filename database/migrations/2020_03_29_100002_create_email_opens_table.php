<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailOpensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laravel_ses_email_opens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sent_email_id');
            // $table->char('beacon_identifier', 36);
            $table->dateTime('opened_at')->nullable();

            // Indexes
            // $table->index('beacon_identifier');
            $table->index(['sent_email_id', 'opened_at']);
            $table->index(['opened_at', 'sent_email_id']);
            $table->index('opened_at');

            // Foreign key constraint
            $table->foreign('sent_email_id')->references('id')->on('laravel_ses_sent_emails')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('laravel_ses_email_opens');
    }
}
