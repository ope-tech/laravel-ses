<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Juhasev\LaravelSes\ModelResolver;

class DropOldNameColumnInLaravelSesBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('laravel_ses_batches', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function down()
    {
        if (!Schema::hasColumn('laravel_ses_batches', 'name')) {
            Schema::table('laravel_ses_batches', function (Blueprint $table) {
                $table->char('name', 36)->index()->nullable()->default(null);
            });
        }

        ModelResolver::get('Batch')::whereNull('name')->update([
            'name' => DB::raw("transfer")
        ]);
    }
}
