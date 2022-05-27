<?php

namespace Juhasev\LaravelSes;

use Aws\SesV2\Exception\SesV2Exception;
use Aws\SesV2\SesV2Client;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class SnsSetup
{
    protected SesV2Client $ses;
    protected SnsClient $sns;
    protected string|null $domain;
    protected Command $console;
    protected string $configSetName;
    protected int $exceptionCount;

    /**
     * SnsSetup constructor.
     *
     * @param Command $console
     * @param string|null $domain
     * @return int
     */
    public function __construct(Command $console, string $domain = null)
    {
        $console->info(str_repeat('-', 48));
        $console->info(" SETTING UP SES Bounce, Delivery and Complaints ");
        $console->info(str_repeat('-', 48));

        $this->domain = $domain ?? parse_url(config('app.url'), PHP_URL_HOST);

        $this->resetExceptionCounter();

        $this->console = $console;

        $this->configSetName = App::environment() . "-ses-" . config('services.ses.region');

        $this->ses = new SesV2Client([
            'credentials' => [
                'key' => config('services.ses.key'),
                'secret' => config('services.ses.secret')
            ],
            'region' => config('services.ses.region'),
            'version' => 'latest'
        ]);

        $this->sns = new SnsClient([
            'credentials' => [
                'key' => config('services.ses.key'),
                'secret' => config('services.ses.secret')
            ],
            'region' => config('services.ses.region'),
            'version' => 'latest'
        ]);

        $this->init();

        $console->newLine();

        if ($this->exceptionCount > 0) {
            $console->error('Some setup tasks failed! Please review them manually in AWS Console!');
        }

        $console->info('ALL COMPLETED!');
    }

    /**
     * Fluent method
     *
     * @param Command $console
     * @param string|null $domain
     * @return SnsSetup
     */
    public static function create(Command $console, string $domain = null): SnsSetup
    {
        return new self($console, $domain);
    }

    /**
     * Init SNS setup
     */
    public function init(): void
    {
        $this->createConfigurationSet();
        $this->setupNotification('bounce');
        $this->setupNotification('complaint');
        $this->setupNotification('delivery');
    }

    /**
     * Setup notification
     *
     * @param string $type
     * @return bool
     */
    public function setupNotification(string $type): bool
    {
        $topic = App::environment() . "-ses-{$type}-" . config('services.ses.region');

        try {
            $result = $this->sns->createTopic([
                'Name' => $topic
            ]);
        } catch (SNSException $e) {
            $this->console->error("Topic (" . $topic . ") already exists...");

            return false;
        }

        $topicArn = $result['TopicArn'];

        $urlSlug = strtolower($type);

        $eventDestinationName = "destination-" . $topic;

        try {
            $this->ses->createConfigurationSetEventDestination([
                'ConfigurationSetName' => $this->configSetName,
                'EventDestination' => [
                    'Enabled' => true,
                    'MatchingEventTypes' => [strtoupper($type)],
                    'SnsDestination' => [
                        'TopicArn' => $topicArn,
                    ],
                ],
                'EventDestinationName' => $eventDestinationName,
            ]);
        } catch (SesV2Exception $e) {
            $this->outputException('EventDestination', $eventDestinationName, $e, true);
        }

        $this->sns->subscribe([
            'Endpoint' => config('app.url') . "/ses/notification/{$urlSlug}",
            'Protocol' => 'https',
            'TopicArn' => $topicArn
        ]);

        return true;
    }

    /**
     * Create configuration set
     */
    protected function createConfigurationSet(): void
    {
        try {
            $this->ses->createConfigurationSet([
                'ConfigurationSetName' => $this->configSetName,
                'DeliveryOptions' => [
                    'TlsPolicy' => 'REQUIRE',
                ],
                'SendingOptions' => [
                    'SendingEnabled' => true,
                ],
                'TrackingOptions' => [
                    'CustomRedirectDomain' => $this->domain,
                ],
            ]);
        } catch (SesV2Exception $e) {
            $this->outputException("ConfigSet", $this->configSetName, $e);
        }
    }

    /**
     * Output SES Exception
     *
     * @param string $type
     * @param string $name
     * @param Exception $e
     * @param bool $exit
     */
    protected function outputException(string $type, string $name, Exception $e, bool $exit = false): void
    {
        $this->exceptionCount++;

        if (Str::contains($e->getMessage(), 'AlreadyExistsException')) {
            $this->console->comment("SES " . sprintf('%-25s', $type) . " " . sprintf('%-50s', $name) . " Already exist!");
        } else {

            $this->console->error($e->getMessage());

            if ($exit) exit(0);
        }
    }

    private function resetExceptionCounter(): void
    {
        $this->exceptionCount = 0;
    }
}
