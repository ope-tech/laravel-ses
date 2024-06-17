<?php

namespace OpeTech\LaravelSes\Actions\Sns;

use Aws\Sns\SnsClient;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTopicArn
{
    use AsAction;

    public function __construct(protected SnsClient $snsClient)
    {

    }

    public function handle(): string
    {
        $result = $this->snsClient->createTopic([
            'Name' => GetTopicName::run(),
        ]);

        return $result['TopicArn'];

    }
}
