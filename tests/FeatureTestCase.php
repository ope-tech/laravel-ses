<?php

namespace Juhasev\LaravelSes\Tests;

use Illuminate\Foundation\Application;
use Juhasev\LaravelSes\Facades\SesMail;
use Juhasev\LaravelSes\LaravelSesServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class FeatureTestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();

        $this->artisan('migrate', ['--database' => 'testbench']);
    }

    /**
     * Load package service provider
     *
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [LaravelSesServiceProvider::class];
    }

    /**
     * Load package alias
     *
     * @param Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'SesMail' => SesMail::class,
        ];
    }

    /**
     * Get environment setup
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('app.url', 'https://laravel-ses.com');
        $app['config']->set('app.debug', true);
    }

    /**
     * Generate bounce json
     *
     * @param $messageId
     * @param string $email
     * @return array
     */
    public function generateBouncePayload($messageId, $email = 'bounce@simulator.amazonses.com')
    {
        $message = [
            "eventType" => "Bounce",
            "bounce" => [
                "feedbackId" => "010101777e55309f-fac8d01b-3c72-4d85-b7dc-7f5acc4dae4c-000000",
                "bounceType" => "Permanent",
                "bounceSubType" => "General",
                "bouncedRecipients" => [
                    [
                        "emailAddress" => "$email",
                        "action" => "failed",
                        "status" => "5.1.1",
                        "diagnosticCode" => "smtp; 550-5.1.1 The email account that you tried to reach does not exist."
                    ]
                ],
                "timestamp" => "2021-02-07T21=>10=>48.305Z",
                "reportingMTA" => "dsn; a27-52.smtp-out.us-west-2.amazonses.com"
            ],
            "mail" => [
                "timestamp" => "2021-02-07T21:10:47.730Z",
                "source" => "invite@juhapanel.sampleninja.io",
                "sourceArn" => "arn:aws:ses:us-west-2:635608510762:identity/sampleninja.io",
                "sendingAccountId" => "635608510762",
                "messageId" => "010101777e552eb2-7597bca3-2730-452a-ae64-35983ed994ce-000000",
                "destination" => [
                    "$email"
                ],
                "headersTruncated" => false,
                "headers" => [
                    [
                        "name" => "Received",
                        "value" => "from localhost)"
                    ],
                    [
                        "name" => "Message-ID",
                        "value" => "<$messageId>"
                    ],
                    [
                        "name" => "Date",
                        "value" => "Sun, 07 Feb 2021 21=>10=>47 +0000"
                    ],
                    [
                        "name" => "Subject",
                        "value" => "We want your feedback!"
                    ],
                    [
                        "name" => "From",
                        "value" => "The Sample Ninja Team <invite@juhapanel.sampleninja.io>"
                    ],
                    [
                        "name" => "To",
                        "value" => "$email"
                    ],
                    [
                        "name" => "MIME-Version",
                        "value" => "1.0"
                    ],
                    [
                        "name" => "X-SES-CONFIGURATION-SET",
                        "value" => "staging-ses-us-west-2"
                    ]
                ]
            ]
        ];

        return [
            "Type" => "Notification",
            "MessageId" => "3ba72fc2-84a4-5181-8bc3-7758dd3cfdb3",
            "TopicArn" => "arn:aws:sns:us-west-2:635608510762:staging-ses-bounce-us-west-2",
            "Subject" => "Amazon SES Email Event Notification",
            "Message" => json_encode($message),
            "Timestamp" => "2021-02-07T21:10:48.412Z",
            "SignatureVersion" => "1",
            "Signature" => "TOilTHyNNPPpsv5FQGcyt45YR/pVJYzBljw/CJbIYhLePWAC9ZSVH4EJCNpuDCoDUbco6+I5A7gzHOM7elLdMTB5TSpdp38MF63X3WpHHiBLON6astguDTmqJxj6OzxRpe31bOzxJBwC6eMNs5YxEw5GL/s97lp+7M47HVShOVQEmIkYqaLsMCLAoHkC22h9dFaxJHk/UVJv3fR4Q5MPpPlI03Ol4udpxA7Z3dolb9nwTtchbMAo0J9ZpAiFQzD0G9pMh+DYm40tqBJjyXwk1R9kFcfdL4LaBOudIf76KVsbPEf5B5QCnEMEwp03bhhwx9lYKEYiTZZOGWDkeBBY2A==",
            "SigningCertURL" => "https://sns.us-west-2.amazonaws.com/SimpleNotificationService-010a507c1833636cd94bdb98bd93083a.pem",
            "UnsubscribeURL" => "https://sns.us-west-2.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:us-west-2:635608510762:staging-ses-bounce-us-west-2:caf51da3-ce68-4d64-b9f2-c9f51e17401d"
        ];
    }

    /**
     * Generate complaint request
     *
     * @param $messageId
     * @param string $email
     * @return array
     */
    public function generateComplaintPayload($messageId, $email = 'complaint@simulator.amazonses.com')
    {
        $message = [
            "eventType" => "Complaint",
            "complaint" => [
                "feedbackId" => "010101777e55309f-fac8d01b-3c72-4d85-b7dc-7f5acc4dae4c-000000",
                "complaintSubType" => null,
                "complaintFeedbackType" => "abuse",
                "complainedRecipients" => [
                    [
                        "emailAddress" => "$email"
                    ]
                ],
                "timestamp" => "2021-02-07T21:10:48.305Z",
                "reportingMTA" => "dsn; a27-52.smtp-out.us-west-2.amazonses.com"
            ],
            "mail" => [
                "timestamp" => "2021-02-07T21:10:47.730Z",
                "source" => "invite@juhapanel.sampleninja.io",
                "sourceArn" => "arn:aws:ses:us-west-2:635608510762:identity/sampleninja.io",
                "sendingAccountId" => "635608510762",
                "messageId" => "010101777e552eb2-7597bca3-2730-452a-ae64-35983ed994ce-000000",
                "destination" => [
                    "$email"
                ],
                "headersTruncated" => false,
                "headers" => [
                    [
                        "name" => "Received",
                        "value" => "from localhost"
                    ],
                    [
                        "name" => "Message-ID",
                        "value" => "<$messageId>"
                    ],
                    [
                        "name" => "Date",
                        "value" => "Sun, 07 Feb 2021 21=>10=>47 +0000"
                    ],
                    [
                        "name" => "Subject",
                        "value" => "We want your feedback!"
                    ],
                    [
                        "name" => "From",
                        "value" => "The Sample Ninja Team <invite@juhapanel.sampleninja.io>"
                    ],
                    [
                        "name" => "To",
                        "value" => "$email"
                    ],
                    [
                        "name" => "MIME-Version",
                        "value" => "1.0"
                    ],
                    [
                        "name" => "X-SES-CONFIGURATION-SET",
                        "value" => "staging-ses-us-west-2"
                    ]
                ]
            ]
        ];

        return [
            "Type" => "Notification",
            "MessageId" => "950a823d-501f-5137-a9a3-d0246f6094b6",
            "TopicArn" => "arn:aws:ses:us-west-2:635608510762:identity/sampleninja.io",
            "Message" => json_encode($message)
        ];
    }

    /**
     * Generate delivery payload
     *
     * @param $messageId
     * @param string $email
     * @return array
     */
    public function generateDeliveryPayload($messageId, $email = 'success@simulator.amazonses.com')
    {
        $message = [
            "eventType" => "Delivery",
            "mail" => [
                "timestamp" => "2021-02-07T21:26:32.000Z",
                "source" => "invite@juhapanel.sampleninja.io",
                "sourceArn" => "arn:aws:ses:us-west-2:635608510762:identity/sampleninja.io",
                "sendingAccountId" => "635608510762",
                "messageId" => "010101777e639740-9228413e-068a-4c01-9bb8-595b6e676700-000000",
                "destination" => [
                    "$email"
                ],
                "headersTruncated" => false,
                "headers" => [
                    [
                        "name" => "Received",
                        "value" => "from Localhost; Sun, 07 Feb 2021 21:26:31 +0000 (UTC)"
                    ],
                    [
                        "name" => "Message-ID",
                        "value" => "<$messageId>"
                    ],
                    [
                        "name" => "Date",
                        "value" => "Sun, 07 Feb 2021 21:26:31 +0000"
                    ],
                    [
                        "name" => "Subject",
                        "value" => "We want your feedback!"
                    ],
                    [
                        "name" => "From",
                        "value" => "The Sample Ninja Team <invite@juhapanel.sampleninja.io>"
                    ],
                    [
                        "name" => "To",
                        "value" => "$email"
                    ],
                    [
                        "name" => "MIME-Version",
                        "value" => "1.0"
                    ],
                    [
                        "name" => "X-SES-CONFIGURATION-SET",
                        "value" => "staging-ses-us-west-2"
                    ]
                ]
            ],
            "delivery" => [
                "timestamp" => "2021-02-07T21:26:32.900Z",
                "processingTimeMillis" => 900,
                "recipients" => [
                    "$email"
                ],
                "smtpResponse" => "250 2.0.0 OK 1612733192 j1si16962504pgl.398 - gsmtp",
                "reportingMTA" => "a27-42.smtp-out.us-west-2.amazonses.com"
            ]
        ];

        return [
            "Type" => "Notification",
            "MessageId" => "950a823d-501f-5137-a9a3-d0246f6094b6",
            "TopicArn" => "arn:aws:sns:eu-west-1:111111111111:laravel-ses-Delivery",
            "Message" => json_encode($message)
        ];
    }
}
