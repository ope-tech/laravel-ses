<?php

namespace Juhasev\LaravelSes\Tests\Feature;

use Juhasev\LaravelSes\Tests\Feature\FeatureTestCase;
use Juhasev\LaravelSes\SesMail;
use Juhasev\LaravelSes\Models\SentEmail;
use Juhasev\LaravelSes\Models\EmailOpen;
use Juhasev\LaravelSes\Models\EmailLink;
use Juhasev\LaravelSes\Mocking\TestMailable;
use Illuminate\Database\Eloquent\Collection;

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

        $stats = SesMail::statsForBatch('welcome_emails');

        // Make sure all stats are 0 apart except sent emails
        $this->assertEquals(8, $stats['send_count']);
        $this->assertEquals(0, $stats['deliveries']);
        $this->assertEquals(0, $stats['opens']);
        $this->assertEquals(0, $stats['complaints']);
        $this->assertEquals(0, $stats['click_throughs']);

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

        // Bounce an email
        $messageId  = SentEmail::whereEmail('bounce@ses.com')->first()->message_id;
        $fakeJson = json_decode($this->generateBounceJson($messageId));
        $this->json('POST', 'laravel-ses/notification/bounce', (array)$fakeJson);

        // Two complaints
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


        //check that stats are now correct, click throughs = amount of users that clicked at least one link
        //link popularity is amount of unique clicks on a link in the email body, ordered by most popular
        $stats = SesMail::statsForBatch('welcome_emails');

        $this->assertEquals([
            "send_count" => 8,
            "deliveries" => 7,
            "opens" => 4,
            "bounces" => 1,
            "complaints" => 2,
            "click_throughs" => 3,
            "link_popularity" => collect([
                "https://google.com" => [
                    "clicks" => 3
                ],
                "https://superficial.io" => [
                    "clicks" => 1
                ]
            ])
        ], $stats);
    }
}
