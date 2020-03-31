<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laravel_ses_email_links', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('link_identifier')->index();
            $table->unsignedBigInteger('sent_email_id');
            $table->string('original_url');
            $table->boolean('clicked')->default(false);
            $table->unsignedSmallInteger('click_count')->default(0);

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
        Schema::dropIfExists('laravel_ses_email_links');
    }
}
