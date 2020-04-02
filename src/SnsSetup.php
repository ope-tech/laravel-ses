<?php

namespace Juhasev\LaravelSes;

use Aws\Ses\SesClient;
use Aws\Sns\SnsClient;

class SnsSetup
{
    protected $ses;
    protected $sns;
    protected $domain;

    /**
     * SnsSetup constructor.
     *
     * @param string $domain
     */
    public function __construct(string $domain)
    {
        $this->domain = $domain;

        $this->ses = new SesClient([
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
    }

    /**
     * Fluent systax method
     *
     * @param string $domain
     * @return SnsSetup
     */
    public static function create(string $domain)
    {
        return new self($domain);
    }

    /**
     * Init SNS setup
     */
    public function init()
    {
        $this->setupNotification('Bounce');
        $this->setupNotification('Complaint');
        $this->setupNotification('Delivery');
    }

    /**
     * Setup notification
     *
     * @param $type
     * @return bool
     */
    public function setupNotification(string $type)
    {
        $result = $this->sns->createTopic([
            'Name' => "laravel-ses-{$type}"
        ]);

        $topicArn = $result['TopicArn'];

        $urlSlug = strtolower($type);

        $this->sns->subscribe([
            'Endpoint' => config('app.url') . "/ses/notification/{$urlSlug}",
            'Protocol' => 'https',
            'TopicArn' => $topicArn
        ]);

        $this->ses->setIdentityNotificationTopic([
            'Identity' => $this->domain,
            'NotificationType' => $type,
            'SnsTopic' => $topicArn
        ]);

        $this->ses->setIdentityHeadersInNotificationsEnabled([
            'Enabled' => true,
            'Identity' => $this->domain,
            'NotificationType' => $type
        ]);

        return true;
    }

    /**
     * Check if notification is set for type
     *
     * @param string $type
     * @return bool
     */

    public function notificationIsSet(string $type): bool
    {
        $result = $this->ses->getIdentityNotificationAttributes([
            'Identities' => [config('services.ses.domain')
            ]
        ]);

        return isset($result['NotificationAttributes'][config('services.ses.domain')]["{$type}Topic"]);
    }
}
