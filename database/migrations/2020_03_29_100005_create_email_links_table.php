<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->id();
            $table->char('link_identifier', 36);
            $table->unsignedBigInteger('sent_email_id');
            $table->string('original_url', 255);
            $table->boolean('clicked')->default(false);
            $table->unsignedSmallInteger('click_count')->default(0);

            // Indexes
            $table->index('link_identifier', 'laravel_ses_email_links_link_identifier_index');
            $table->index(['clicked', 'sent_email_id']);
            $table->index(['sent_email_id', 'clicked']);

            // Foreign key constraint
            $table->foreign('sent_email_id', 'laravel_ses_email_links_sent_email_id_foreign')
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
        Schema::dropIfExists('laravel_ses_email_links');
    }
}
