<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->unsignedBigInteger('sent_email_id');
            $table->string('type');
            $table->dateTime('complained_at');
            $table->timestamps();

            $table->foreign('sent_email_id')
                ->references('id')
                ->on('laravel_ses_sent_emails')
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
