<?php

namespace OpeTech\LaravelSes\Actions\Sns;

use Aws\Sns\SnsClient;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateSnsTopicWithHttpSubscription
{
    use AsAction;

    public function __construct(protected SnsClient $snsClient)
    {

    }

    public function handle(): bool
    {
        //Subscribe to the topic
        $this->snsClient->subscribe([
            'Endpoint' => config('app.url').'/laravel-ses/sns-notification',
            'Protocol' => 'https',
            'TopicArn' => GetTopicArn::run(),
        ]);

        return true;

    }
}
