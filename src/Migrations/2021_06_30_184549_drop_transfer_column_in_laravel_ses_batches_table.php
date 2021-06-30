<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Juhasev\LaravelSes\ModelResolver;

class DropTransferColumnInLaravelSesBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('laravel_ses_batches', function (Blueprint $table) {
            $table->dropColumn('transfer');
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
        Schema::table('laravel_ses_batches', function (Blueprint $table) {
            $table->string('transfer')->unique()->nullable()->default(null);
        });

        ModelResolver::get('Batch')::whereNull('transfer')->update([
            'transfer' => DB::raw("name")
        ]);
    }
}
