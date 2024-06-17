<?php

namespace OpeTech\LaravelSes\Actions\Sns;

use Aws\SesV2\Exception\SesV2Exception;
use Aws\SesV2\SesV2Client;
use Lorisleiva\Actions\Concerns\AsAction;
use OpeTech\LaravelSes\Exceptions\LaravelSesConfigurationException;

class CreateConfigurationSet
{
    use AsAction;

    public function __construct(protected SesV2Client $sesClient)
    {
    }

    public function handle(?string $customRedirectDomain): bool
    {
        try {
            $this->sesClient->createConfigurationSet([
                'ConfigurationSetName' => GetConfigurationSetName::run(),
                'SendingOptions' => [
                    'SendingEnabled' => true,
                ],
                ...($customRedirectDomain ? [
                    'TrackingOptions' => [
                        'CustomRedirectDomain' => $customRedirectDomain,
                    ]] : []),
            ]);
        } catch (SesV2Exception $e) {

            //Simplify the message.
            if ($e->getAwsErrorCode() === 'AlreadyExistsException') {
                throw new LaravelSesConfigurationException('Configuration Set already exists.');
            }

            throw new LaravelSesConfigurationException($e->getMessage());
        }

        return true;
    }
}
