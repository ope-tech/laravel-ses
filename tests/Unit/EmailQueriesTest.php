<?php

namespace Juhasev\LaravelSes\Tests\Unit;

use Juhasev\LaravelSes\Models\EmailBounce;
use Juhasev\LaravelSes\Models\EmailComplaint;

class EmailQueriesTest extends UnitTestCase
{
    public function testHasComplainedEndpointReturnsTrueWhenAnEmailHasComplained()
    {
        EmailComplaint::create([
            'message_id' => '8b',
            'sent_email_id' => 23,
            'type' => 'abuse',
            'email' => 'wanyama@hotmail.com',
            'complained_at' =>  '2018-01-02 09:12:00'
        ]);

        $this->get('laravel-ses/api/has/complained/wanyama@hotmail.com')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'complained' => true,
                'complaints' => [
                    [
                        'message_id' => '8b',
                        'sent_email_id' => 23,
                        'type' => 'abuse',
                        'email' => 'wanyama@hotmail.com',
                        'complained_at' =>  '2018-01-02 09:12:00'
                    ]
                ]
            ]);
    }

    public function testHasComplainedEndpointReturnsFalseWhenAnEmailHasNotComplained()
    {
        $this->get('laravel-ses/api/has/complained/wanyama@hotmail.com')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'complained' => false
            ]);
    }

    public function testHasBouncedEndpointReturnsTrueWhenAnEmailHasBounced()
    {
        EmailBounce::create([
            'message_id' => '7a',
            'sent_email_id' => '1',
            'type' => 'Permanent',
            'email' => 'harrykane@gmail.com',
            'bounced_at' => '2018-01-01 12:00:00'
        ]);

        $this->get('laravel-ses/api/has/bounced/harrykane@gmail.com')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'bounced' => true,
                'bounces' => [
                    [
                        'message_id' => '7a',
                        'sent_email_id' => '1',
                        'type' => 'Permanent',
                        'email' => 'harrykane@gmail.com',
                        'bounced_at' => '2018-01-01 12:00:00'
                    ]
                ]
            ]);
    }

    public function testHasBouncedEndpointReturnsFalseWhenAnEmailHasNotBounced()
    {
        $res = $this->get('laravel-ses/api/has/bounced/harrykane@gmail.com')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'bounced' => false
            ]);
    }
}
