<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Juhasev\LaravelSes\ModelResolver;

class CreateNewNameColumnInLaravelSesBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function up()
    {
        Schema::table('laravel_ses_batches', function (Blueprint $table) {
            $table->string('name')
                ->unique()
                ->nullable()
                ->default(null)
                ->after('id');
        });

        ModelResolver::get('Batch')::whereNull('name')->update([
            'name' => DB::raw("transfer")
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('laravel_ses_batches', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
}
