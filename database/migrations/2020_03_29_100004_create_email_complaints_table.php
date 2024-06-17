<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailComplaintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laravel_ses_email_complaints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sent_email_id');
            $table->string('type')->nullable();
            $table->dateTime('complained_at');

            // Indexes
            $table->index('sent_email_id', 'laravel_ses_email_complaints_sent_email_id_foreign');

            // Foreign key constraint
            $table->foreign('sent_email_id', 'laravel_ses_email_complaints_sent_email_id_foreign')
                ->references('id')->on('laravel_ses_sent_emails')
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
        Schema::dropIfExists('laravel_ses_email_complaints');
    }
}
