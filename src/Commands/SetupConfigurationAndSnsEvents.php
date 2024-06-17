<?php

namespace OpeTech\LaravelSes\Commands;

use Illuminate\Console\Command;
use OpeTech\LaravelSes\Actions\Sns\CreateConfigurationSet;
use OpeTech\LaravelSes\Actions\Sns\CreateConfigurationSetEventDestination;
use OpeTech\LaravelSes\Actions\Sns\CreateSnsTopicWithHttpSubscription;
use OpeTech\LaravelSes\Exceptions\LaravelSesException;
use Throwable;

class SetupConfigurationAndSnsEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-ses:setup-config-and-sns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('Creating configuration set...');

            $answer = $this->ask('Do you want to use a custom redirect domain? (y/n)', 'n');

            if ($answer === 'y') {
                $customRedirectDomain = $this->ask('Please enter the custom redirect domain (you will need to configure DNS separately): ');
            } else {
                $customRedirectDomain = null;
            }

            CreateConfigurationSet::run($customRedirectDomain);

            $this->info('Configuration set created successfully.');

        } catch (LaravelSesException $e) {

            $this->error('Could not create a configuration set because: '.$e->getMessage());
        }

        //carry on and setup the event destination.
        $this->info('Creating SNS notifications...');

        try {
            CreateSnsTopicWithHttpSubscription::run();

            $this->info('SNS notifications setup.');

        } catch (Throwable $e) {
            $this->error('Could not successfully setup SNS because: '.$e->getMessage());
        }

        $this->info('Creating Configuration Set Event Destination...');

        try {
            CreateConfigurationSetEventDestination::run();

            $this->info('Configuration Set Event Destination created successfully.');

        } catch (Throwable $e) {
            $this->error('Could not create a Configuration Set Event Destination because: '.$e->getMessage());
        }

        return Command::SUCCESS;
    }
}
