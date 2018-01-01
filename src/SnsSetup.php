<?php

namespace oliveready7\LaravelSes;
use Aws\Ses\SesClient;
use Aws\Sns\SnsClient;

class SnsSetup {
    protected $ses;
    protected $sns;

    public function __construct() {
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

    }
    public function init($protocol) {
        $this->setupNotification('Bounce', $protocol);
        $this->setupNotification('Complaint', $protocol);
        $this->setupNotification('Delivery', $protocol);
    }

    public function setupNotification($type, $protocol) {

        $result = $this->sns->createTopic([
            'Name' => "laravel-ses-{$type}"
        ]);

        $topicArn = $result['TopicArn'];

        $urlSlug = strtolower($type);

        $result = $this->sns->subscribe([
            'Endpoint' => config('app.url') . "/laravel-ses/notification/{$urlSlug}",
            'Protocol' => $protocol,
            'TopicArn' => $topicArn
        ]);

        $result = $this->ses->setIdentityNotificationTopic([
            'Identity' => config('services.ses.domain'),
            'NotificationType' => $type,
            'SnsTopic' => $topicArn
        ]);

        $result = $this->ses->setIdentityHeadersInNotificationsEnabled([
            'Enabled' => true,
            'Identity' => config('services.ses.domain'),
            'NotificationType' => $type
        ]);

        return true;
    }

    public function notificationIsSet($type) {
        $result = $this->ses->getIdentityNotificationAttributes(['Identities' => [config('services.ses.domain')]]);
        return isset($result['NotificationAttributes'][config('services.ses.domain')]["{$type}Topic"]);
    }
}
