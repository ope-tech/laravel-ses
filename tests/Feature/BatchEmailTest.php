<?php

namespace oliveready7\LaravelSes\Tests\Feature;
use oliveready7\LaravelSes\Tests\Feature\FeatureTestCase;
use oliveready7\LaravelSes\SesMail;
use oliveready7\LaravelSes\Models\SentEmail;
use oliveready7\LaravelSes\Models\EmailOpen;
use oliveready7\LaravelSes\Models\EmailLink;
use oliveready7\LaravelSes\Mocking\TestMailable;
use Illuminate\Database\Eloquent\Collection;


class BatchEmailTest extends FeatureTestCase {

    public function test_batch_emails_can_be_sent_and_stats_can_be_gotten() {
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

        foreach($emails as $email) {
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
        foreach($emails as $email) {
            if($email != 'bounce@ses.com'){
                $messageId = SentEmail::whereEmail($email)->first()->message_id;
                $fakeJson = json_decode($this->generateDeliveryJson($messageId));
                $this->json('POST',
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

        foreach($emails as $email) {
            if(in_array($email, $openedEmails)) {
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
        $this->assertArraySubset([
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
        ], SentEmail::statsForBatch('welcome_emails'));


    }
}
