<?php

use Aws\Command;
use Aws\SesV2\Exception\SesV2Exception;
use Aws\SesV2\SesV2Client;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use OpeTech\LaravelSes\Actions\Sns\CreateConfigurationSet;
use OpeTech\LaravelSes\Actions\Sns\CreateConfigurationSetEventDestination;
use OpeTech\LaravelSes\Actions\Sns\CreateSnsTopicWithHttpSubscription;
use OpeTech\LaravelSes\Actions\Sns\GetConfigurationSetName;
use OpeTech\LaravelSes\Actions\Sns\GetTopicArn;

describe('successes', function () {
    it('creates a configuration set correctly', function () {
        $sesMock = Mockery::mock(SesV2Client::class);
        $snsMock = Mockery::mock(SnsClient::class);

        $sesMock->shouldReceive('createConfigurationSet')
            ->with([
                'ConfigurationSetName' => 'laravel-ses-testing-configuration-set',
                'SendingOptions' => [
                    'SendingEnabled' => true,
                ],
            ]);

        mockSuccessfulCreateTopic($snsMock);
        mockSuccessfulSnsSubscription($snsMock);
        mockSuccessfulCreateConfigurationSetEventDestination($sesMock);

        bindMocks($this->app, $sesMock, $snsMock);

        $this->artisan('laravel-ses:setup-config-and-sns')
            ->expectsQuestion('Do you want to use a custom redirect domain? (y/n)', 'n')
            ->expectsOutput('Configuration set created successfully.')
            ->assertExitCode(0);

    });

    it('creates a configuration set with a custom redirect domain', function () {
        $sesMock = Mockery::mock(SesV2Client::class);
        $snsMock = Mockery::mock(SnsClient::class);

        $sesMock->shouldReceive('createConfigurationSet')
            ->with([
                'ConfigurationSetName' => 'laravel-ses-testing-configuration-set',
                'SendingOptions' => [
                    'SendingEnabled' => true,
                ],
                'TrackingOptions' => [
                    'CustomRedirectDomain' => 'tracking.example.com',
                ],
            ]);

        mockSuccessfulCreateTopic($snsMock);
        mockSuccessfulSnsSubscription($snsMock);
        mockSuccessfulCreateConfigurationSetEventDestination($sesMock);

        bindMocks($this->app, $sesMock, $snsMock);

        $this->artisan('laravel-ses:setup-config-and-sns')
            ->expectsQuestion('Do you want to use a custom redirect domain? (y/n)', 'y')
            ->expectsQuestion('Please enter the custom redirect domain (you will need to configure DNS separately): ', 'tracking.example.com')
            ->expectsOutput('Configuration set created successfully.')
            ->assertExitCode(0);
    });

    it('creates SNS topic and subscribes to the HTTP endpoint for receiveing notifications', function () {
        $sesMock = Mockery::mock(SesV2Client::class);
        $snsMock = Mockery::mock(SnsClient::class);

        mockSuccessfulCreateConfigurationSet($sesMock);

        $snsMock->shouldReceive('createTopic')->with(['Name' => 'laravel-ses-testing-topic'])->andReturn([
            'TopicArn' => 'arn:aws:sns:us-east-1:123456789012:testing-laravel-ses-topic',
        ]);

        $snsMock->shouldReceive('subscribe')->with([
            'Endpoint' => 'http://localhost/laravel-ses/sns-notification',
            'Protocol' => 'https',
            'TopicArn' => 'arn:aws:sns:us-east-1:123456789012:testing-laravel-ses-topic',
        ]);

        mockSuccessfulCreateConfigurationSetEventDestination($sesMock);

        bindMocks($this->app, $sesMock, $snsMock);

        $this->artisan('laravel-ses:setup-config-and-sns')
            ->expectsQuestion('Do you want to use a custom redirect domain? (y/n)', 'n')
            ->expectsOutput('SNS notifications setup.')
            ->assertExitCode(0);
    });

    it('sets up the event desintation correctly', function () {
        $sesMock = Mockery::mock(SesV2Client::class);
        $snsMock = Mockery::mock(SnsClient::class);

        mockSuccessfulCreateConfigurationSet($sesMock);
        mockSuccessfulCreateTopic($snsMock);
        mockSuccessfulSnsSubscription($snsMock);

        bindMocks($this->app, $sesMock, $snsMock);

        $sesMock->shouldReceive('createConfigurationSetEventDestination')->with([
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
                'Name' => 'laravel-ses-testing-event-destination',
            ],
            'EventDestinationName' => 'laravel-ses-testing-event-destination',
        ]);

        $this->artisan('laravel-ses:setup-config-and-sns')
            ->expectsQuestion('Do you want to use a custom redirect domain? (y/n)', 'n')
            ->expectsOutput('Configuration Set Event Destination created successfully.')
            ->assertExitCode(0);
    });

});

describe('errors', function () {
    it('outputs an error if a configuration set cannot be created for an unknown reason', function () {

        $sesMock = Mockery::mock(SesV2Client::class);
        $snsMock = Mockery::mock(SnsClient::class);

        $sesMock->shouldReceive('createConfigurationSet')
            ->andThrow(new SesV2Exception(
                'An error occurred',
                new Command('createConfigurationSet')
            ));

        mockSuccessfulCreateTopic($snsMock);
        mockSuccessfulSnsSubscription($snsMock);
        mockSuccessfulCreateConfigurationSetEventDestination($sesMock);

        bindMocks($this->app, $sesMock, $snsMock);

        $this->artisan('laravel-ses:setup-config-and-sns')
            ->expectsQuestion('Do you want to use a custom redirect domain? (y/n)', 'n')
            ->expectsOutput('Could not create a configuration set because: '.'An error occurred')
            ->assertExitCode(0);
    });

    it('outputs a specific error if the configuration set already exists', function () {

        $sesMock = Mockery::mock(SesV2Client::class);
        $snsMock = Mockery::mock(SnsClient::class);

        $sesMock->shouldReceive('createConfigurationSet')
            ->andThrow(new SesV2Exception(
                'Error',
                new Command('createConfigurationSet'),
                [
                    'code' => 'AlreadyExistsException',
                ],
            ));

        mockSuccessfulCreateTopic($snsMock);
        mockSuccessfulSnsSubscription($snsMock);
        mockSuccessfulCreateConfigurationSetEventDestination($sesMock);

        bindMocks($this->app, $sesMock, $snsMock);

        $this->artisan('laravel-ses:setup-config-and-sns')
            ->expectsQuestion('Do you want to use a custom redirect domain? (y/n)', 'n')
            ->expectsOutput('Could not create a configuration set because: '.'Configuration Set already exists.')
            ->assertExitCode(0);
    });

    it('outputs an error if SNS subscription fails', function () {

        $sesMock = Mockery::mock(SesV2Client::class);
        $snsMock = Mockery::mock(SnsClient::class);

        mockSuccessfulCreateConfigurationSet($sesMock);
        mockSuccessfulCreateTopic($snsMock);

        $snsMock->shouldReceive('subscribe')
            ->andThrow(new SnsException(
                'Error',
                new Command('subscribe'),
            ));

        mockSuccessfulCreateConfigurationSetEventDestination($sesMock);

        bindMocks($this->app, $sesMock, $snsMock);

        $this->artisan('laravel-ses:setup-config-and-sns')
            ->expectsQuestion('Do you want to use a custom redirect domain? (y/n)', 'n')
            ->expectsOutput('Could not successfully setup SNS because: '.'Error')
            ->assertExitCode(0);
    });

    it('outputs an error if create event destination fails', function () {

        $sesMock = Mockery::mock(SesV2Client::class);
        $snsMock = Mockery::mock(SnsClient::class);

        mockSuccessfulCreateConfigurationSet($sesMock);
        mockSuccessfulCreateTopic($snsMock);
        mockSuccessfulSnsSubscription($snsMock);

        $sesMock->shouldReceive('createConfigurationSetEventDestination')
            ->andThrow(new SesV2Exception(
                'Error',
                new Command('subscribe'),
            ));

        bindMocks($this->app, $sesMock, $snsMock);

        $this->artisan('laravel-ses:setup-config-and-sns')
            ->expectsQuestion('Do you want to use a custom redirect domain? (y/n)', 'n')
            ->expectsOutput('Could not create a Configuration Set Event Destination because: '.'Error')
            ->assertExitCode(0);
    });

});

function mockSuccessfulCreateConfigurationSet($sesMock)
{
    $sesMock->shouldReceive('createConfigurationSet');
}

function mockSuccessfulCreateTopic($snsMock)
{
    $snsTopicArn = 'arn:aws:sns:us-east-1:123456789012:testing-laravel-ses-topic';

    $snsMock->shouldReceive('createTopic')->andReturn([
        'TopicArn' => $snsTopicArn,
    ]);
}

function mockSuccessfulCreateConfigurationSetEventDestination($sesMock)
{
    $sesMock->shouldReceive('createConfigurationSetEventDestination');
}

function mockSuccessfulSnsSubscription($snsMock)
{
    $snsMock->shouldReceive('subscribe');
}

function bindMocks($app, $sesMock, $snsMock)
{
    $app->when([
        CreateConfigurationSet::class,
        CreateConfigurationSetEventDestination::class,
    ])
        ->needs(SesV2Client::class)
        ->give(function ($app) use ($sesMock) {
            return $sesMock;
        });

    $app->when([
        CreateSnsTopicWithHttpSubscription::class,
        GetTopicArn::class,
    ])
        ->needs(SnsClient::class)
        ->give(function ($app) use ($snsMock) {
            return $snsMock;
        });
}
