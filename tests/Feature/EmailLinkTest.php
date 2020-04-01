<?php

namespace Juhasev\LaravelSes\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Juhasev\LaravelSes\Factories\Events\SesLinkEvent;
use Juhasev\LaravelSes\ModelResolver;
use Juhasev\LaravelSes\Tests\FeatureTestCase;
use Ramsey\Uuid\Uuid;

class EmailLinkTest extends FeatureTestCase
{
    public function testEmailLinksCanBeTracked()
    {
        $linkId = Uuid::uuid4()->toString();

        ModelResolver::get('EmailLink')::create([
            'sent_email_id' => 11,
            'original_url' => 'https://redirect.com',
            'link_identifier' => $linkId
        ]);

        Event::fake();
        
        $res = $this->get("https://laravel-ses.com/ses/link/$linkId")
            ->assertStatus(302);

        Event::assertDispatched(SesLinkEvent::class);
        
        $this->assertEquals('https://redirect.com', $res->getTargetUrl());

        $emailLink = ModelResolver::get('EmailLink')::first()->toArray();

        $this->assertTrue($emailLink['clicked']);
        $this->assertEquals(1, $emailLink['click_count']);

        $this->get("https://laravel-ses.com/ses/link/$linkId");

        $emailLink = ModelResolver::get('EmailLink')::first()->toArray();

        $this->assertTrue($emailLink['clicked']);
        $this->assertEquals(2, $emailLink['click_count']);

    }
}
