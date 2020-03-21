<?php

namespace Juhasev\LaravelSes\Tests\Feature;

use Juhasev\LaravelSes\Models\SentEmail;
use Juhasev\LaravelSes\Models\EmailLink;
use Juhasev\LaravelSes\Tests\Feature\FeatureTestCase;
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

        $emailLink = EmailLink::first()->toArray();

        $this->assertTrue($emailLink['clicked']);
        $this->assertEquals(1, $emailLink['click_count']);

        $this->get("https://laravel-ses.com/laravel-ses/link/$linkId");

        $emailLink = EmailLink::first()->toArray();

        $this->assertTrue($emailLink['clicked']);
        $this->assertEquals(2, $emailLink['click_count']);

    }
}
