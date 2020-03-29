<?php

namespace Juhasev\LaravelSes\Commands;

use Illuminate\Console\Command;
use Juhasev\LaravelSes\SnsSetup;

class SetupSNS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sns:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up Amazon SNS configuration';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $snsSetup = new SnsSetup;
        $snsSetup->init('https');
    }
}
