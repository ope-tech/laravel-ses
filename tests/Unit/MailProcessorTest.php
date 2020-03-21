<?php
use Juhasev\LaravelSes\MailProcessor;
use Juhasev\LaravelSes\Models\SentEmail;
use Juhasev\LaravelSes\Models\EmailOpen;
use Juhasev\LaravelSes\Models\EmailLink;
use Juhasev\LaravelSes\Tests\Unit\UnitTestCase;

class MailProcessorTest extends UnitTestCase
{
    public function testOpenTrackingBeaconIsPresentInEmail()
    {
        $body = 'this is a test email body';

        $sentEmail = SentEmail::create([
            'email' => 'lamela@yahoo.com',
            'message_id' => 'somerandomid@swift.generated'
        ]);

        $mailProcessor = new MailProcessor($sentEmail, $body);

        $parsedBody = $mailProcessor->openTracking()->getEmailBody();

        $this->assertEquals(
            'this is a test email body<img src="'.
            'https://laravel-ses.com/laravel-ses/beacon/' . EmailOpen::first()->beacon_identifier .
            '" alt="" style="width:1px;height:1px;"/>',
            $parsedBody
        );
    }

    public function testLinksAreParsedCorrectlySoTheyCanBeTracked()
    {
        // Body of text with one link in it
        $body = "This is a test body of text, <a href='https://click.me'>Click Me</a>";

        $sentEmail = SentEmail::create([
            'email' => 'lamela@yahoo.com',
            'message_id' => 'somerandomid@swift.generated'
        ]);

        $mailProcessor = new MailProcessor($sentEmail, $body);

        $parsedBody = $mailProcessor->linkTracking()->getEmailBody();

        $linkId = EmailLink::first()->link_identifier;

        // Make sure body of email is now correct
        $this->assertEquals(
            'This is a test body of text, <a href="'.
            'https://laravel-ses.com/laravel-ses/link/' . $linkId .
            '">Click Me</a>',
            $parsedBody
        );

        // Make sure two identical links can be parsed and one unique one
        $threeLinks = "<a href='https://link.dev'>do not open me</a><a href='https://link.dev'>open me</a>" .
        "<a href='https://google.com/'>google link</a>";

        $mailProcessor = new MailProcessor($sentEmail, $threeLinks);

        $threeLinksParsed = $mailProcessor->linkTracking()->getEmailBody();

        $this->assertEquals(4, EmailLink::count()); // Make sure three new links were created

        // Identical links have different ids, so it is advised to give original links a unique query var
        // e.g https://link.dev?link=1 and https://link.dev?link=2
        $this->assertEquals(
            '<a href="'.
            'https://laravel-ses.com/laravel-ses/link/' . EmailLink::find(2)->link_identifier .
            '">do not open me</a>' .
            '<a href="'.
            'https://laravel-ses.com/laravel-ses/link/' . EmailLink::find(3)->link_identifier .
            '">open me</a>' .
            '<a href="' .
            'https://laravel-ses.com/laravel-ses/link/' . EmailLink::find(4)->link_identifier .
            '">google link</a>',
            $threeLinksParsed
        );

        $emailLink = EmailLink::first()->toArray();

        // Make sure email link data is correct
        $this->assertEquals('https://click.me', $emailLink['original_url']);
        $this->assertEquals(1, $emailLink['sent_email_id']);
    }
}
