<?php

namespace Juhasev\LaravelSes\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Juhasev\LaravelSes\Facades\SesMail;
use Juhasev\LaravelSes\Factories\Events\SesOpenEvent;
use Juhasev\LaravelSes\Mocking\TestMailable;
use Juhasev\LaravelSes\ModelResolver;
use Juhasev\LaravelSes\Models\Batch;
use Juhasev\LaravelSes\Services\Stats;
use Juhasev\LaravelSes\Tests\FeatureTestCase;
use Ramsey\Uuid\Uuid;

class BatchEmailTest extends FeatureTestCase
{
    public function testBatchEmailsCanBeSentAndStatsCanBeGotten()
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

        $stats = Stats::statsForBatch(
            Batch::resolve('welcome_emails')
        );

        // Make sure all stats are 0 apart except sent emails
        $this->assertEquals(8, $stats['sent']);
        $this->assertEquals(0, $stats['deliveries']);
        $this->assertEquals(0, $stats['opens']);
        $this->assertEquals(0, $stats['complaints']);
        $this->assertEquals(0, $stats['clicks']);

        //deliver all emails apart from bounced email
        foreach ($emails as $email) {
            if ($email !== 'bounce@ses.com') {
                $sentEmailId = ModelResolver::get('SentEmail')::whereEmail($email)->first()->message_id;

                $this->json(
                    'POST',
                    '/ses/notification/delivery',
                    $this->generateDeliveryPayload($sentEmailId)
                );
            }
        }

        // Bounce an email
        $sentEmailId  = ModelResolver::get('SentEmail')::whereEmail('bounce@ses.com')->first()->message_id;
        $this->json('POST', 'ses/notification/bounce', $this->generateBouncePayload($sentEmailId));

        // Two complaints
        $sentEmailId  = ModelResolver::get('SentEmail')::whereEmail('complaint@yes.com')->first()->message_id;
        $this->json('POST', 'ses/notification/complaint', $this->generateComplaintPayload($sentEmailId));

        $sentEmailId  = ModelResolver::get('SentEmail')::whereEmail('ay@yahoo.com')->first()->message_id;
        $this->json('POST', 'ses/notification/complaint', $this->generateComplaintPayload($sentEmailId));

        // register 4 opens
        $openedEmails = [
            'something@gmail.com',
            'somethingelse@gmail.com',
            'hey@google.com',
            'no@gmail.com'
        ];

        Event::fake(SesOpenEvent::class);

        foreach ($emails as $email) {
            if (in_array($email, $openedEmails)) {
                $sentEmailId  = ModelResolver::get('SentEmail')::where('email', $email)->first()->id;
                $emailOpen = ModelResolver::get('EmailOpen')::whereSentEmailId($sentEmailId)->first();
                $this->get("ses/beacon/{$emailOpen->beacon_identifier}");

                Event::assertDispatched(SesOpenEvent::class, static fn ($event) => $event->data['id'] === $emailOpen->id);
            }
        }

        // one user clicks both links
        ModelResolver::get('SentEmail')::whereEmail('something@gmail.com')->first()->emailLinks()->createMany([
            ['original_url' => 'https://google.com', 'link_identifier' => $linkId = Uuid::uuid4()->toString()],
            ['original_url' => 'https://superficial.io', 'link_identifier' => $anotherLinkId = Uuid::uuid4()->toString()],
        ]);
        $this->get("https://laravel-ses.com/ses/link/$linkId");
        $this->get("https://laravel-ses.com/ses/link/$anotherLinkId");


        // one user clicks one link three times
        ModelResolver::get('SentEmail')::whereEmail('hey@google.com')->first()->emailLinks()->create([
            'original_url' => 'https://google.com', 'link_identifier' => $linkId = Uuid::uuid4()->toString()
        ]);
        $this->get("https://laravel-ses.com/ses/link/$linkId");
        $this->get("https://laravel-ses.com/ses/link/$linkId");
        $this->get("https://laravel-ses.com/ses/link/$linkId");

        // one user clicks one link only
        ModelResolver::get('SentEmail')::whereEmail('no@gmail.com')->first()->emailLinks()->create([
            'original_url' => 'https://google.com', 'link_identifier' => $linkId = Uuid::uuid4()->toString()
        ]);
        $this->get("https://laravel-ses.com/ses/link/$linkId");

        //check that stats are now correct, click through = amount of users that clicked at least one link
        //link popularity is amount of unique clicks on a link in the email body, ordered by most popular
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
}
