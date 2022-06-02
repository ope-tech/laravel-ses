<?php

namespace Juhasev\LaravelSes\Tests\Feature;

use Juhasev\LaravelSes\Facades\SesMail;
use Juhasev\LaravelSes\Mocking\TestMailable;
use Juhasev\LaravelSes\ModelResolver;
use Juhasev\LaravelSes\Models\Batch;
use Juhasev\LaravelSes\Repositories\EmailRepository;
use Juhasev\LaravelSes\Services\Stats;
use Juhasev\LaravelSes\Tests\FeatureTestCase;
use Ramsey\Uuid\Uuid;

class StatsTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setupBasicCampaign();
    }

    public function testStatsForAnEmailEndPoint()
    {
        // add some more campaigns
        SesMail::enableAllTracking()
            ->setBatch('win_back')
            ->to("something@gmail.com")
            ->send(new TestMailable());

        SesMail::enableAllTracking()
            ->setBatch('june_newsletter')
            ->to("something@gmail.com")
            ->send(new TestMailable());


        $batch = Batch::resolve('win_back');

        $messageId = ModelResolver::get('SentEmail')::whereEmail('something@gmail.com')->where('batch_id', $batch->getId())->first()->message_id;
        $this->json('POST', 'ses/notification/bounce', $this->generateBouncePayload($messageId, 'something@gmail.com'));

        $messageId = ModelResolver::get('SentEmail')::whereEmail('something@gmail.com')->where('batch_id', $batch->getId())->first()->message_id;
        $this->json('POST', '/ses/notification/delivery', $this->generateDeliveryPayload($messageId, 'something@gmail.com'));

        $messageId = ModelResolver::get('SentEmail')::whereEmail('something@gmail.com')->where('batch_id', $batch->getId())->first()->message_id;
        $this->json('POST', 'ses/notification/complaint', $this->generateComplaintPayload($messageId, 'something@gmail.com'));

        $links = ModelResolver::get('SentEmail')::whereEmail('something@gmail.com')
            ->where('batch_id', $batch->getId())
            ->first()
            ->emailLinks()->create([
                'original_url' => 'https://google.com', 'link_identifier' => $linkId = Uuid::uuid4()->toString(),
            ]);

        $this->get("https://laravel-ses.com/ses/link/$linkId");

        $stats = Stats::statsForEmail('something@gmail.com');

        $expectedCounts = [
            "sent" => 3,
            "deliveries" => 2,
            "opens" => 1,
            "bounces" => 1,
            "complaints" => 1,
            "clicks" => 2
        ];

        $this->assertEquals($expectedCounts, $stats);


        // Test email repository data agrees with stats
        $sent = EmailRepository::getSent('something@gmail.com');
        $this->assertCount(3, $sent);

        $deliveries = EmailRepository::getDeliveries('something@gmail.com');
        $this->assertCount(2, $deliveries);

        $opens = EmailRepository::getOpens('something@gmail.com');
        $this->assertCount(1, $opens);

        $bounces = EmailRepository::getBounces('something@gmail.com');
        $this->assertCount(1, $bounces);

        $complaints = EmailRepository::getComplaints('something@gmail.com');
        $this->assertCount(1, $complaints);

        $clicks = EmailRepository::getClicks('something@gmail.com');

        $this->assertCount(2, $clicks);

        // Test data for email
        $stats = Stats::dataForEmail('something@gmail.com');

        // Check sent emails
        $this->assertEquals("something@gmail.com", $stats['sent'][0]['email']);
        $this->assertEquals(Batch::resolve("welcome_emails")->getId(), $stats['sent'][0]['batch_id']);

        $this->assertEquals("something@gmail.com", $stats['sent'][1]['email']);
        $this->assertEquals(Batch::resolve("win_back")->getId(), $stats['sent'][1]['batch_id']);

        $this->assertEquals("something@gmail.com", $stats['sent'][2]['email']);
        $this->assertEquals(Batch::resolve("june_newsletter")->getId(), $stats['sent'][2]['batch_id']);

        // Check deliveries
        $this->assertEquals("something@gmail.com", $stats['deliveries'][0]['email']);
        $this->assertEquals(Batch::resolve("welcome_emails")->getId(), $stats['deliveries'][0]['batch_id']);

        $this->assertEquals("something@gmail.com", $stats['deliveries'][1]['email']);
        $this->assertEquals(Batch::resolve("win_back")->getId(), $stats['deliveries'][1]['batch_id']);

        // Check click through
        $this->assertEquals(1, $stats['clicks'][0]->emailLinks[0]->sent_email_id);
        $this->assertEquals('https://google.com', $stats['clicks'][0]->emailLinks[0]->original_url);
        $this->assertEquals(Batch::resolve('welcome_emails')->getId(), $stats['clicks'][0]->batch_id);

        $this->assertEquals(1, $stats['clicks'][0]->emailLinks[1]->sent_email_id);
        $this->assertEquals('https://superficial.io', $stats['clicks'][0]->emailLinks[1]->original_url);
        $this->assertEquals(Batch::resolve('welcome_emails')->getId(), $stats['clicks'][0]['batch_id']);

        $this->assertEquals(9, $stats['clicks'][1]->emailLinks[0]->sent_email_id);
        $this->assertEquals('https://google.com', $stats['clicks'][1]->emailLinks[0]->original_url);
        $this->assertEquals(Batch::resolve('win_back')->getId(), $stats['clicks'][1]['batch_id']);
    }

    public function testStatsForBatchEndPoint()
    {
        $stats = Stats::statsForBatch(Batch::resolve('welcome_emails'));

        $this->assertEquals([
            "sent" => 8,
            "deliveries" => 7,
            "opens" => 4,
            "bounces" => 1,
            "complaints" => 2,
            "clicks" => 3,
            "link_popularity" => [
                "https://google.com" => [
                    "clicks" => 3
                ],
                "https://superficial.io" => [
                    "clicks" => 1
                ]
            ]
        ], $stats);
    }

    public function testStatsForNonExistingBatch()
    {
        $batch = Batch::resolve('lukaku');

        $this->assertNull($batch);
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

        $statsForBatch = Stats::statsForBatch(Batch::resolve('welcome_emails'));

        // Make sure all stats are 0 apart except sent_emails
        $this->assertEquals(8, $statsForBatch['sent']);
        $this->assertEquals(0, $statsForBatch['deliveries']);
        $this->assertEquals(0, $statsForBatch['opens']);
        $this->assertEquals(0, $statsForBatch['complaints']);
        $this->assertEquals(0, $statsForBatch['clicks']);
        $this->assertEquals([], $statsForBatch['link_popularity']);

        //deliver all emails apart from bounced email
        foreach ($emails as $email) {

            if ($email != 'bounce@ses.com') {

                $messageId = ModelResolver::get('SentEmail')::whereEmail($email)->first()->message_id;

                $this->json(
                    'POST',
                    '/ses/notification/delivery',
                    $this->generateDeliveryPayload($messageId)
                );
            }
        }

        //bounce an email
        $messageId = ModelResolver::get('SentEmail')::whereEmail('bounce@ses.com')->first()->message_id;
        $this->json('POST', '/ses/notification/bounce', $this->generateBouncePayload($messageId));

        //two complaints
        $messageId = ModelResolver::get('SentEmail')::whereEmail('complaint@yes.com')->first()->message_id;
        $this->json('POST', '/ses/notification/complaint', $this->generateComplaintPayload($messageId));

        $messageId = ModelResolver::get('SentEmail')::whereEmail('ay@yahoo.com')->first()->message_id;
        $this->json('POST', '/ses/notification/complaint', $this->generateComplaintPayload($messageId));

        //register 4 opens
        $openedEmails = [
            'something@gmail.com',
            'somethingelse@gmail.com',
            'hey@google.com',
            'no@gmail.com'
        ];

        foreach ($emails as $email) {
            if (in_array($email, $openedEmails)) {
                $sentEmailId = ModelResolver::get('SentEmail')::whereEmail($email)->first()->id;
                $id = ModelResolver::get('EmailOpen')::whereSentEmailId($sentEmailId)->first()->beacon_identifier;
                $this->get("ses/beacon/{$id}");
            }
        }

        //one user clicks both links
        ModelResolver::get('SentEmail')::whereEmail('something@gmail.com')->first()->emailLinks()->createMany([
            ['original_url' => 'https://google.com', 'link_identifier' => $linkId = Uuid::uuid4()->toString()],
            ['original_url' => 'https://superficial.io', 'link_identifier' => $anotherLinkId = Uuid::uuid4()->toString()],
        ]);
        $this->get("https://laravel-ses.com/ses/link/$linkId");
        $this->get("https://laravel-ses.com/ses/link/$anotherLinkId");


        //one user clicks one link three times
        ModelResolver::get('SentEmail')::whereEmail('hey@google.com')->first()->emailLinks()->create([
            'original_url' => 'https://google.com', 'link_identifier' => $linkId = Uuid::uuid4()->toString(),
        ]);
        $this->get("https://laravel-ses.com/ses/link/$linkId");
        $this->get("https://laravel-ses.com/ses/link/$linkId");
        $this->get("https://laravel-ses.com/ses/link/$linkId");

        //one user clicks one link only
        ModelResolver::get('SentEmail')::whereEmail('no@gmail.com')->first()->emailLinks()->create([
            'original_url' => 'https://google.com', 'link_identifier' => $linkId = Uuid::uuid4()->toString(),
        ]);
        $this->get("https://laravel-ses.com/ses/link/$linkId");
    }
}
