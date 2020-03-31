<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->integer('sent_email_id');
            $table->string('email');
            $table->string('batch')->nullable(); // TODO This seems out of place here
            $table->uuid('beacon_identifier')->index();
            $table->string('url');
            $table->dateTime('opened_at')->nullable();
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
        Schema::dropIfExists('laravel_ses_email_opens');
    }
}
