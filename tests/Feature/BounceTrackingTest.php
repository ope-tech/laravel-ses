<?php

namespace Juhasev\LaravelSes\Tests\Feature;

use Juhasev\LaravelSes\ModelResolver;

class BounceTrackingTest extends FeatureTestCase
{
    public function testBounceTracking()
    {
        ModelResolver::get('SentEmail')::create([
            'message_id' => '84b8739d03d2245baed4999232916608@swift.generated',
            'email' => 'eriksen23@gmail.com',
            'bounce_tracking' => true
        ]);

        $fakeJson = json_decode($this->exampleSesResponse);

        $this->json(
            'POST',
            'laravel-ses/notification/bounce',
            (array)$fakeJson
        );

        $bounceRecord = ModelResolver::get('EmailBounce')::first()->toArray();

        // Check bounce is logged correctly
        // Note email Amazon returns is set as email rather than email set in sent email
        $this->assertEquals('Permanent', $bounceRecord['type']);
        $this->assertEquals('84b8739d03d2245baed4999232916608@swift.generated', $bounceRecord['message_id']);
        $this->assertEquals(1, $bounceRecord['sent_email_id']);
        $this->assertEquals('bounce@simulator.amazonses.com', $bounceRecord['email']);
    }

    public function testSubscriptionConfirmation()
    {
        $fakeJson = json_decode($this->exampleSubscriptionResponse);

        $this->json(
            'POST',
            '/laravel-ses/notification/bounce',
            (array)$fakeJson
        )->assertJson(['success' => true]);
    }

    public function testABounceIsNotStoredWhenThereIsNoEquivilantMessageId()
    {
        ModelResolver::get('SentEmail')::create([
            'message_id' => 'abcaseasyas123@swift.generated',
            'email' => 'eriksen23@gmail.com'
        ]);

        $fakeJson = json_decode($this->exampleSesResponse);

        $this->json(
            'POST',
            'laravel-ses/notification/bounce',
            (array)$fakeJson
        );

        $this->assertNull(ModelResolver::get('EmailBounce')::first());
    }

    public function testThatBounceIsNotRecordedIfBounceTrackingIsNotSet()
    {
        ModelResolver::get('SentEmail')::create([
            'message_id' => '84b8739d03d2245baed4999232916608@swift.generated',
            'email' => 'eriksen23@gmail.com'
        ]);

        $fakeJson = json_decode($this->exampleSesResponse);

        $this->json(
            'POST',
            'laravel-ses/notification/bounce',
            (array)$fakeJson
        );

        $this->assertNull(ModelResolver::get('EmailBounce')::first());
    }


    private $exampleSubscriptionResponse = '{
          "Type" : "SubscriptionConfirmation",
          "MessageId" : "165545c9-2a5c-472c-8df2-7ff2be2b3b1b",
          "Token" : "2336412f37fb687f5d51e6e241d09c805a5a57b30d712f794cc5f6a988666d92768dd60a747ba6f3beb71854e285d6ad02428b09ceece29417f1f02d609c582afbacc99c583a916b9981dd2728f4ae6fdb82efd087cc3b7849e05798d2d2785c03b0879594eeac82c01f235d0e717736",
          "TopicArn" : "arn:aws:sns:us-west-2:123456789012:MyTopic",
          "Message" : "You have chosen to subscribe to the topic arn:aws:sns:us-west-2:123456789012:MyTopic.\nTo confirm the subscription, visit the SubscribeURL included in this message.",
          "SubscribeURL" : "google.com",
          "Timestamp" : "2012-04-26T20:45:04.751Z",
          "SignatureVersion" : "1",
          "Signature" : "EXAMPLEpH+DcEwjAPg8O9mY8dReBSwksfg2S7WKQcikcNKWLQjwu6A4VbeS0QHVCkhRS7fUQvi2egU3N858fiTDN6bkkOxYDVrY0Ad8L10Hs3zH81mtnPk5uvvolIC1CXGu43obcgFxeL3khZl8IKvO61GWB6jI9b5+gLPoBc1Q=",
          "SigningCertURL" : "https://sns.us-west-2.amazonaws.com/SimpleNotificationService-f3ecfb7224c7233fe7bb5f59f96de52f.pem"
    }';


    private $exampleSesResponse = '{"Type" : "Notification",
      "MessageId" : "950a823d-501f-5137-a9a3-d0246f6094b6",
      "TopicArn" : "arn:aws:sns:eu-west-1:111158800833:laravel-ses-Bounce",
      "Message" : "{\"notificationType\":\"Bounce\",\"bounce\":{\"bounceType\":\"Permanent\",\"bounceSubType\":\"General\",\"bouncedRecipients\":[{\"emailAddress\":\"bounce@simulator.amazonses.com\",\"action\":\"failed\",\"status\":\"5.1.1\",\"diagnosticCode\":\"smtp; 550 5.1.1 user unknown\"}],\"timestamp\":\"2017-08-24T20:55:27.843Z\",\"feedbackId\":\"0102015e16074124-76fb1d19-754a-4623-b37b-509eb649e884-000000\",\"remoteMtaIp\":\"207.171.163.188\",\"reportingMTA\":\"dsn; a7-12.smtp-out.eu-west-1.amazonses.com\"},\"mail\":{\"timestamp\":\"2017-08-24T20:55:27.000Z\",\"source\":\"test@laravel-ses.com\",\"sourceArn\":\"arn:aws:ses:eu-west-1:111158800833:identity/laravel-ses.com\",\"sourceIp\":\"127.0.0\",\"sendingAccountId\":\"111111111111\",\"messageId\":\"0102015e16073ec2-e6c0fd6b-17fb-4f8d-a1ce-82c68fe2a943-000000\",\"destination\":[\"bounce@simulator.amazonses.com\"],\"headersTruncated\":false,\"headers\":[{\"name\":\"Message-ID\",\"value\":\"<530389a196a58d2057754a9d8eeda262@swift.generated>\"},{\"name\":\"Date\",\"value\":\"Thu, 24 Aug 2017 20:55:27 +0000\"},{\"name\":\"Subject\",\"value\":\"test\"},{\"name\":\"From\",\"value\":\"test@laravel-ses.com\"},{\"name\":\"Reply-To\",\"value\":\"test@laravel-ses.com\"},{\"name\":\"To\",\"value\":\"bounce@simulator.amazonses.com\"},{\"name\":\"MIME-Version\",\"value\":\"1.0\"},{\"name\":\"Content-Type\",\"value\":\"text/html; charset=utf-8\"},{\"name\":\"Content-Transfer-Encoding\",\"value\":\"quoted-printable\"}],\"commonHeaders\":{\"from\":[\"test@laravel-ses.com\"],\"replyTo\":[\"test@laravel-ses.com\"],\"date\":\"Thu, 24 Aug 2017 20:55:27 +0000\",\"to\":[\"bounce@simulator.amazonses.com\"],\"messageId\":\"<84b8739d03d2245baed4999232916608@swift.generated>\",\"subject\":\"test\"}}}",
      "Timestamp" : "2017-08-24T20:55:27.883Z",
      "SignatureVersion" : "1",
      "Signature" : "EXAMPLEoRtETzzKxQhgINqozOINqCWecbs827aR4rbYQpMameLSzB9KbUl+pc630htDNFp8TRMe6z55CEERbWehRw//cZ2zI2Gt/qlYL5NdW54UrTJvNl4Sh4ifWatGbhfkWHsgjf4SnNNdAV+rgr4sB45mUwZMUuXcXTu1dKA07qXYYj+VTt3M8tPC9fXd+WvmnoakHi6fg4aqdPXzY5QhCYBJmWp5Io0qkQWKgxF3HJG91coRqp7NQcEfPz2CGcvT0EiPgZxh6D0y7fZNNrg/ThdOVxeFixYi1Ix67hCerQ9H7d6XBQzbYEHTUeVMRozAkFziTuoyQ==",
      "SigningCertURL" : "https://sns.eu-west-1.amazonaws.com/SimpleNotificationService-433026a4050d206028891664da859041.pem",
      "UnsubscribeURL" : "https://sns.eu-west-1.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:eu-west-1:111111111111:laravel-ses-Bounce:7546aed7-b188-46f6-913c-2f548c0cb251"}';
}
