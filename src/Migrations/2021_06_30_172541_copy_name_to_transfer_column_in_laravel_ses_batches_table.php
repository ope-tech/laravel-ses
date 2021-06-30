<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Juhasev\LaravelSes\ModelResolver;

class CopyNameToTransferColumnInLaravelSesBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function up()
    {
        $batchModel = ModelResolver::get('Batch');

        Schema::table('laravel_ses_batches', function (Blueprint $table) {
            $table->string('transfer')->unique()->nullable()->default(null);
        });

        logger("Copying batch name column to transfer column");

        // We now this migration is slow, but we need to deal with duplicates
        while (true) {

            $batch = $batchModel::whereNull('transfer')->first();

            if (!$batch) {
                logger("No more batches found...");
                break;
            }

            $batch->transfer = $batch->name;

            try {
                $batch->save();
            } catch (QueryException $e) {
                logger($batch->name . " -> duplicate entry deleting duplicate...");
                $batch->delete();
                continue;

            }

            logger($batch->name . " -> copied");
        }

        logger("Copy complete");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('laravel_ses_batches', function (Blueprint $table) {
            $table->dropColumn('transfer');
        });
    }
}
