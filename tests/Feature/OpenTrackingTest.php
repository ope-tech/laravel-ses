<?php

namespace Juhasev\LaravelSes\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Juhasev\LaravelSes\Facades\SesMail;
use Juhasev\LaravelSes\Factories\Events\SesOpenEvent;
use Juhasev\LaravelSes\Mocking\TestMailable;
use Juhasev\LaravelSes\ModelResolver;
use Juhasev\LaravelSes\Models\EmailOpen;
use Juhasev\LaravelSes\Tests\FeatureTestCase;

class OpenTrackingTest extends FeatureTestCase
{
    public function testOpenTracking()
    {
        SesMail::fake();
        SesMail::enableOpenTracking();
        SesMail::to('harrykane9@gmail.com')->send(new TestMailable());

        //send a junk uuid and check error is thrown
        $this->get('/ses/beacon/thisisjunk')
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'errors' => [
                    'Invalid Beacon'
                ]
            ]);

        Event::fake();

        $this->get('/ses/beacon/' . EmailOpen::first()->beacon_identifier)
            ->assertStatus(302)
            ->assertHeader('location', 'https://laravel-ses.com/ses/to.png');

        Event::assertDispatched(SesOpenEvent::class);

        //check email open has been saved
        $this->assertNotNull(ModelResolver::get('EmailOpen')::first()->opened_at);
    }
}
