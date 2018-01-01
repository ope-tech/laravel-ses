<?php

namespace oliveready7\LaravelSes;
use Illuminate\Console\Command;

class SetupSNS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:sns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up amazon SNS configuration';

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
        $snsSetup->init();
    }
}
