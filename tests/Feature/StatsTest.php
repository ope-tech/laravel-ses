<?php

namespace oliveready7\LaravelSes\Tests\Feature;

use oliveready7\LaravelSes\Tests\Feature\FeatureTestCase;
use oliveready7\LaravelSes\SesMail;
use oliveready7\LaravelSes\Models\SentEmail;
use oliveready7\LaravelSes\Models\EmailOpen;
use oliveready7\LaravelSes\Models\EmailLink;
use oliveready7\LaravelSes\Models\EmailComplaint;
use oliveready7\LaravelSes\Mocking\TestMailable;
use Illuminate\Database\Eloquent\Collection;

class StatsTest extends FeatureTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->setupBasicCampaign();
    }

    public function testStatsForAnEmailEndPoint()
    {
        // make sure stats are correct for default campaingn
        $this->get('laravel-ses/api/stats/email/something@gmail.com')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'counts' => [
                        "sent_emails" => 1,
                        "deliveries" => 1,
                        "opens" => 1,
                        "bounces" => 0,
                        "complaints" => 0,
                        "click_throughs" => 1
                    ],
                    'data' => [
                        'sent_emails' => [
                            [
                                "email" => "something@gmail.com",
                                "batch" => "welcome_emails"
                            ]
                        ],
                        'deliveries' => [
                            [
                                "email" => "something@gmail.com",
                                "batch" => "welcome_emails"
                            ]
                        ],
                        'click_throughs' => [
                            [
                                "sent_email_id" => "1",
                                'original_url' => "https://google.com"
                            ],
                            [
                                "sent_email_id" => "1",
                                "original_url" => "https://superficial.io",
                            ]
                        ]

                    ]
                ]
            ]);

        // add some more campaigns
        SesMail::enableAllTracking()
                ->setBatch('win_back')
                ->to("something@gmail.com")
                ->send(new TestMailable());

        SesMail::enableAllTracking()
                ->setBatch('june_newsletter')
                ->to("something@gmail.com")
                ->send(new TestMailable());


        $messageId  = SentEmail::whereEmail('something@gmail.com')->whereBatch('win_back')->first()->message_id;
        $fakeJson = json_decode($this->generateBounceJson($messageId, 'something@gmail.com'));
        $this->json('POST', 'laravel-ses/notification/bounce', (array)$fakeJson);

        $messageId = SentEmail::whereEmail('something@gmail.com')->whereBatch('win_back')->first()->message_id;
        $fakeJson = json_decode($this->generateDeliveryJson($messageId, 'something@gmail.com'));
        $this->json('POST', '/laravel-ses/notification/delivery', (array)$fakeJson);

        $messageId  = SentEmail::whereEmail('something@gmail.com')->whereBatch('win_back')->first()->message_id;
        $fakeJson = json_decode($this->generateComplaintJson($messageId, 'something@gmail.com'));
        $this->json('POST', 'laravel-ses/notification/complaint', (array)$fakeJson);

        $l = $this->get('laravel-ses/api/stats/email/something@gmail.com');

        $links = SentEmail::whereEmail('something@gmail.com')
                ->whereBatch('win_back')
                ->first()
                ->emailLinks;


        $linkId = $links->first()->link_identifier;
        $this->get("https://laravel-ses.com/laravel-ses/link/$linkId");

        $j = $this->get('laravel-ses/api/stats/email/something@gmail.com')
                ->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'counts' => [
                            "sent_emails" => 3,
                            "deliveries" => 2,
                            "opens" => 1,
                            "bounces" => 1,
                            "complaints" => 1,
                            "click_throughs" => 2
                        ],
                        'data' => [
                            'sent_emails' => [
                                [
                                    "email" => "something@gmail.com",
                                    "batch" => "welcome_emails"
                                ],
                                [
                                    "email" => "something@gmail.com",
                                    "batch" => 'win_back'
                                ],
                                [
                                    "email" => "something@gmail.com",
                                    "batch" => 'june_newsletter'
                                ]
                            ],
                            'deliveries' => [
                                [
                                    "email" => "something@gmail.com",
                                    "batch" => "welcome_emails"
                                ],
                                [
                                    "email" => "something@gmail.com",
                                    "batch" => "win_back"
                                ]
                            ],
                            'click_throughs' => [
                                [
                                    "sent_email_id" => "1",
                                    'original_url' => "https://google.com",
                                    "batch" => 'welcome_emails'
                                ],
                                [
                                    "sent_email_id" => "1",
                                    "original_url" => "https://superficial.io",
                                    "batch" => 'welcome_emails'
                                ],
                                [
                                    "original_url" => "https://google.com",
                                    "batch" => 'win_back'
                                ]
                            ]

                        ]
                    ],

                ]);
        $this->get('laravel-ses/api/stats/email/does@notexist.com')
                ->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'counts' => [
                            "sent_emails" => 0,
                            "deliveries" => 0,
                            "opens" => 0,
                            "bounces" => 0,
                            "complaints" => 0,
                            "click_throughs" => 0
                        ],
                        'data' => [
                            "sent_emails" => [],
                            "deliveries" => [],
                            "opens" => [],
                            "bounces" => [],
                            "complaints" => [],
                            "click_throughs" => [],
                        ]
                    ]
                ]);
    }

    public function testStatsForBatchEndPoint()
    {
        //stats with data
        $this->get('laravel-ses/api/stats/batch/welcome_emails')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    "send_count" => 8,
                    "deliveries" => 7,
                    "opens" => 4,
                    "bounces" => 1,
                    "complaints" => 2,
                    "click_throughs" => 3,
                    "link_popularity" => [
                        "https://google.com" => [
                            "clicks" => 3
                        ],
                        "https://superficial.io" => [
                            "clicks" => 1
                        ]
                    ]
                ]
            ]);

        //batch that has no data
        $this->get('laravel-ses/api/stats/batch/lukaku')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    "send_count" => 0,
                    "deliveries" => 0,
                    "opens" => 0,
                    "bounces" => 0,
                    "complaints" => 0,
                    "click_throughs" => 0,
                    "link_popularity" => [
                    ]
                ]
            ]);
    }


    private function setupBasicCampaign()
    {
        SesMail::fake();

        $emails = [
            'something@gmail.com',
            'somethingelse@gmail.com',
            'ay@yahoo.com',
            'yo@hotmail.com',
            'hey@google.com',
            'no@gmail.com',
            'bounce@ses.com',
            'complaint@yes.com'
        ];

        foreach ($emails as $email) {
            SesMail::enableAllTracking()
                ->setBatch('welcome_emails')
                ->to($email)
                ->send(new TestMailable());
        }

        //make sure all stats are 0 apart from sent emails
        $this->assertArraySubset([
            "send_count" => 8,
            "deliveries" => 0,
            "opens" => 0,
            "complaints" => 0,
            "click_throughs" => 0,
            "link_popularity" => new Collection()
        ], SentEmail::statsForBatch('welcome_emails'));

        //deliver all emails apart from bounced email
        foreach ($emails as $email) {
            if ($email != 'bounce@ses.com') {
                $messageId = SentEmail::whereEmail($email)->first()->message_id;
                $fakeJson = json_decode($this->generateDeliveryJson($messageId));
                $this->json(
                    'POST',
                    '/laravel-ses/notification/delivery',
                    (array)$fakeJson
                );
            }
        }

        //bounce an email
        $messageId  = SentEmail::whereEmail('bounce@ses.com')->first()->message_id;
        $fakeJson = json_decode($this->generateBounceJson($messageId));
        $this->json('POST', 'laravel-ses/notification/bounce', (array)$fakeJson);

        //two complaints
        $messageId  = SentEmail::whereEmail('complaint@yes.com')->first()->message_id;
        $fakeJson = json_decode($this->generateComplaintJson($messageId));
        $this->json('POST', 'laravel-ses/notification/complaint', (array)$fakeJson);

        $messageId  = SentEmail::whereEmail('ay@yahoo.com')->first()->message_id;
        $fakeJson = json_decode($this->generateComplaintJson($messageId));
        $this->json('POST', 'laravel-ses/notification/complaint', (array)$fakeJson);

        //register 4 opens
        $openedEmails = [
            'something@gmail.com',
            'somethingelse@gmail.com',
            'hey@google.com',
            'no@gmail.com'
        ];

        foreach ($emails as $email) {
            if (in_array($email, $openedEmails)) {
                //get the open identifier
                $id = EmailOpen::whereEmail($email)->first()->beacon_identifier;
                $this->get("laravel-ses/beacon/{$id}");
            }
        }

        //one user clicks both links
        $links = SentEmail::whereEmail('something@gmail.com')->first()->emailLinks;

        $linkId = $links->where('original_url', 'https://google.com')->first()->link_identifier;
        $this->get("https://laravel-ses.com/laravel-ses/link/$linkId");

        $linkId = $links->where('original_url', 'https://superficial.io')->first()->link_identifier;
        $this->get("https://laravel-ses.com/laravel-ses/link/$linkId");


        //one user clicks one link three times
        $links = SentEmail::whereEmail('hey@google.com')->first()->emailLinks;

        $linkId = $links->where('original_url', 'https://google.com')->first()->link_identifier;
        $this->get("https://laravel-ses.com/laravel-ses/link/$linkId");
        $this->get("https://laravel-ses.com/laravel-ses/link/$linkId");
        $this->get("https://laravel-ses.com/laravel-ses/link/$linkId");

        //one user clicks one link only
        $links = SentEmail::whereEmail('no@gmail.com')->first()->emailLinks;
        $linkId = $links->where('original_url', 'https://google.com')->first()->link_identifier;
        $this->get("https://laravel-ses.com/laravel-ses/link/$linkId");
    }
}
