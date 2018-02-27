<?php

namespace oliveready7\LaravelSes\Tests\Unit;
use oliveready7\LaravelSes\Models\{EmailBounce, EmailComplaint};



class EmailQueriesTest extends UnitTestCase {

    function test_has_complained_endpoint_returns_true_when_an_email_has_complained() {
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

    function test_has_complained_endpoint_returns_false_when_an_email_has_not_complained() {
        $this->get('laravel-ses/api/has/complained/wanyama@hotmail.com')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'complained' => false
            ]);
    }

    function test_has_bounced_endpoint_returns_true_when_an_email_has_bounced() {
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

    function test_has_bounced_endpoint_returns_false_when_an_email_has_not_bounced() {
        $res = $this->get('laravel-ses/api/has/bounced/harrykane@gmail.com')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'bounced' => false
            ]);
    }
}
