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
            $table->uuid('link_identifier');
            $table->integer('sent_email_id');
            $table->string('original_url');
            $table->string('batch')->nullable();
            $table->boolean('clicked')->default(false);
            $table->integer('click_count')->default(0);
            $table->timestamps();
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
