<?php

namespace OpeTech\LaravelSes\Actions\Sns;

use Aws\SesV2\SesV2Client;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateConfigurationSetEventDestination
{
    use AsAction;

    public function __construct(protected SesV2Client $sesClient)
    {

    }

    public function handle(): bool
    {

        $this->sesClient->createConfigurationSetEventDestination([
            'ConfigurationSetName' => GetConfigurationSetName::run(),
            'EventDestination' => [
                'Enabled' => true,
                'MatchingEventTypes' => [
                    'SEND',
                    'REJECT',
                    'BOUNCE',
                    'COMPLAINT',
                    'DELIVERY',
                    'OPEN',
                    'CLICK',
                    'RENDERING_FAILURE',
                    'DELIVERY_DELAY',
                ],
                'SnsDestination' => [
                    'TopicArn' => GetTopicArn::run(),
                ],
                'Name' => GetEventDestinationName::run(),
            ],
            'EventDestinationName' => GetEventDestinationName::run(),
        ]);

        return true;
    }
}
