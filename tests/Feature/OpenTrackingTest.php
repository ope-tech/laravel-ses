<?php

namespace Juhasev\LaravelSes\Tests\Feature;

use Juhasev\LaravelSes\Facades\SesMail;
use Juhasev\LaravelSes\Mocking\TestMailable;
use Juhasev\LaravelSes\ModelResolver;
use Juhasev\LaravelSes\Models\EmailOpen;

class OpenTrackingTest extends FeatureTestCase
{
    public function testOpenTracking()
    {
        SesMail::fake();
        SesMail::enableOpenTracking();
        SesMail::to('harrykane9@gmail.com')->send(new TestMailable());

        //send a junk uuid and check error is thrown
        $this->get('laravel-ses/beacon/thisisjunk')
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'errors' => [
                    'Invalid Beacon'
                ]
            ]);

        $res = $this->get('laravel-ses/beacon/' . EmailOpen::first()->beacon_identifier)
            ->assertStatus(302)
            ->assertHeader('location', 'https://laravel-ses.com/laravel-ses/to.png');

        //check email open has been saved
        $this->assertNotNull(ModelResolver::get('EmailOpen')::first()->opened_at);
    }
}
