<?php

namespace Juhasev\LaravelSes\Commands;

use Illuminate\Console\Command;
use Juhasev\LaravelSes\SnsSetup;

class SetupSns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sns:setup {domain?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up Amazon SNS configuration for given domain i.e. sampleninja.io. Omit domain to use APP_URL domain';

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
        SnsSetup::create($this, $this->argument('domain'));
    }
}
