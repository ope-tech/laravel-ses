<?php

namespace Juhasev\LaravelSes\Tests\Unit;

use Illuminate\Support\Facades\Event;
use Juhasev\LaravelSes\Exceptions\LaravelSesTooManyRecipientsException;
use Juhasev\LaravelSes\Facades\SesMail;
use Juhasev\LaravelSes\Factories\Events\SesSentEvent;
use Juhasev\LaravelSes\Mocking\TestMailable;
use Juhasev\LaravelSes\Tests\UnitTestCase;

class SesMailerTest extends UnitTestCase
{
    public function testSendEmailEventIsSent()
    {
        SesMail::fake();
        Event::fake();

        SesMail::enableAllTracking()
            ->to('oliveready@gmail.com')
            ->send(new TestMailable());

        Event::assertDispatched(SesSentEvent::class);

        SesMail::assertSent(TestMailable::class);
    }

    public function testExceptionIsThrownWhenTryingToSendToMoreThanOnePerson()
    {
        $this->expectException(LaravelSesTooManyRecipientsException::class);

        SesMail::fake();

        SesMail::to([
            'oliveready@gmail.com',
            'something@whatever.com',
        ])->send(new TestMailable());
    }

    public function testTrackingSettingsAreSetCorrectly()
    {
        SesMail::enableOpenTracking()
            ->enableLinkTracking()
            ->enableBounceTracking();

        $this->assertEquals([
            'openTracking' => true,
            'linkTracking' => true,
            'bounceTracking' => true,
            'deliveryTracking' => false,
            'complaintTracking' => false,
        ], SesMail::trackingSettings());

        //check that disabling works
        SesMail::disableOpenTracking()
            ->disableLinkTracking()
            ->disableBounceTracking();

        $this->assertEquals([
            'openTracking' => false,
            'linkTracking' => false,
            'bounceTracking' => false,
            'deliveryTracking' => false,
            'complaintTracking' => false,
        ], SesMail::trackingSettings());

        //check all tracking methods work
        SesMail::enableAllTracking();

        $this->assertEquals([
            'openTracking' => true,
            'linkTracking' => true,
            'bounceTracking' => true,
            'deliveryTracking' => true,
            'complaintTracking' => true,
        ], SesMail::trackingSettings());

        SesMail::disableAllTracking();

        $this->assertEquals([
            'openTracking' => false,
            'linkTracking' => false,
            'bounceTracking' => false,
            'deliveryTracking' => false,
            'complaintTracking' => false,
        ], SesMail::trackingSettings());
    }
}
