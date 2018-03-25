<?php

namespace oliveready7\LaravelSes\Tests\Feature;

use oliveready7\LaravelSes\Models\SentEmail;
use oliveready7\LaravelSes\Models\EmailLink;
use oliveready7\LaravelSes\Tests\Feature\FeatureTestCase;
use Ramsey\Uuid\Uuid;

class ClickTrackingTest extends FeatureTestCase
{
    public function testEmailLinksCanBeTracked()
    {
        $linkId = Uuid::uuid4()->toString();

        EmailLink::create([
            'sent_email_id' => 11,
            'original_url' => 'https://redirect.com',
            'link_identifier' => $linkId
        ]);

        $res = $this->get("https://laravel-ses.com/laravel-ses/link/$linkId")
            ->assertStatus(302);

        $this->assertEquals('https://redirect.com', $res->getTargetUrl());

        $this->assertArraySubset([
            'clicked' => true,
            'click_count' => 1
        ], EmailLink::first()->toArray());

        $this->get("https://laravel-ses.com/laravel-ses/link/$linkId");

        $this->assertArraySubset([
            'clicked' => true,
            'click_count' => 2
        ], EmailLink::first()->toArray());
    }
}
