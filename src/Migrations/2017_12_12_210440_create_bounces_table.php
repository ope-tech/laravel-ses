<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBouncesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laravel_ses_email_bounces', function (Blueprint $table) {
            $table->increments('id');
            $table->string('message_id');
            $table->unsignedInteger('sent_email_id');
            $table->string('type');
            $table->string('email');
            $table->dateTime('bounced_at');
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
        Schema::dropIfExists('laravel_ses_email_bounces');
    }
}
