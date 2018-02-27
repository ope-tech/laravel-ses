<?php

namespace oliveready7\LaravelSes\Tests\Unit;
use oliveready7\LaravelSes\Models\EmailBounce;



class EmailQueriesTest extends UnitTestCase {

    function test_has_bounced_endpoint_returns_true_when_an_email_has_bounced() {
        EmailBounce::create([
            'message_id' => '7a',
            'sent_email_id' => '1',
            'type' => 'Permanent',
            'email' => 'harrykane@gmail.com',
            'bounced_at' => '2018-01-01 12:00:00'
        ]);

        $res = $this->get('laravel-ses/api/has/bounced/harrykane@gmail.com')
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

    function test_has_bounced_endpoint_returns_false_when_an_email_has_not_bounced() {
        $res = $this->get('laravel-ses/api/has/bounced/harrykane@gmail.com')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'bounced' => false
            ]);
    }
}
