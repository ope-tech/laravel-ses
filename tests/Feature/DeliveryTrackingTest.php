<?php

namespace Juhasev\LaravelSes\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Juhasev\LaravelSes\Factories\Events\SesDeliveryEvent;
use Juhasev\LaravelSes\ModelResolver;
use Juhasev\LaravelSes\Tests\FeatureTestCase;

class DeliveryTrackingTest extends FeatureTestCase
{
    public function testDeliveryTracking()
    {
        ModelResolver::get('SentEmail')::create([
            'message_id' => 'a4947f1f3fdb397b3a7bf2d3b7d2f53e@swift.generated',
            'email' => 'eriksen23@gmail.com',
            'delivery_tracking' => true
        ]);

        $fakeJson = json_decode($this->exampleSesResponse);

        Event::fake();

        $this->json(
            'POST',
            'laravel-ses/notification/delivery',
            (array)$fakeJson
        );

        Event::assertDispatched(SesDeliveryEvent::class);

        $this->assertNotNull(ModelResolver::get('SentEmail')::first()->delivered_at);
    }

    public function testConfirmSubscription()
    {
        $fakeJson = json_decode($this->exampleSubscriptionResponse);
        $response = $this->json(
            'POST',
            '/laravel-ses/notification/delivery',
            (array)$fakeJson
        )->assertJson(['success' => true]);
    }

    public function testDeliveryTimeIsNotSetIfTrackingNotEnabled()
    {
        ModelResolver::get('SentEmail')::create([
            'message_id' => 'a4947f1f3fdb397b3a7bf2d3b7d2f53e@swift.generated',
            'email' => 'eriksen23@gmail.com'
        ]);

        $fakeJson = json_decode($this->exampleSesResponse);
        $res = $this->json(
            'POST',
            'laravel-ses/notification/delivery',
            (array)$fakeJson
        );

        $this->assertNull(ModelResolver::get('SentEmail')::first()->delivered_at);
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

    private $exampleSesResponse = '{
        "Type": "Notification",
        "MessageId": "950a823d-501f-5137-a9a3-d0246f6094b6",
        "TopicArn": "arn:aws:sns:eu-west-1:111111111111:laravel-ses-Bounce",
        "Message": "{\"notificationType\":\"Delivery\",\"mail\":{\"timestamp\":\"2017-08-25T07:58:39.096Z\",\"source\":\"test@laravel-ses.com\",\"sourceArn\":\"arn:aws:ses:eu-west-1:11153938800833:identity/laravel-ses.com\",\"sourceIp\":\"127.0.0.1\",\"sendingAccountId\":\"111100833\",\"messageId\":\"1112015e18666bf8-8277947d-f88b-47ef-8e1b-1c97d4d4e51a-000000\",\"destination\":[\"success@simulator.amazonses.com\"],\"headersTruncated\":false,\"headers\":[{\"name\":\"Message-ID\",\"value\":\"<a4947f1f3fdb397b3a7bf2d3b7d2f53e@swift.generated>\"},{\"name\":\"Date\",\"value\":\"Fri, 25 Aug 2017 07:58:38 +0000\"},{\"name\":\"Subject\",\"value\":\"test\"},{\"name\":\"From\",\"value\":\"test@laravel-ses.com\"},{\"name\":\"Reply-To\",\"value\":\"test@laravel-ses.com\"},{\"name\":\"To\",\"value\":\"success@simulator.amazonses.com\"},{\"name\":\"MIME-Version\",\"value\":\"1.0\"},{\"name\":\"Content-Type\",\"value\":\"text/html; charset=utf-8\"},{\"name\":\"Content-Transfer-Encoding\",\"value\":\"quoted-printable\"}],\"commonHeaders\":{\"from\":[\"test@laravel-ses.com\"],\"replyTo\":[\"test@laravel-ses.com\"],\"date\":\"Fri, 25 Aug 2017 07:58:38 +0000\",\"to\":[\"success@simulator.amazonses.com\"],\"messageId\":\"<a4947f1f3fdb397b3a7bf2d3b7d2f53e@swift.generated>\",\"subject\":\"test\"}},\"delivery\":{\"timestamp\":\"2017-08-25T07:58:40.192Z\",\"processingTimeMillis\":1096,\"recipients\":[\"success@simulator.amazonses.com\"],\"smtpResponse\":\"250 2.6.0 Message received\",\"remoteMtaIp\":\"205.251.222.49\",\"reportingMTA\":\"b8-29.smtp-out.eu-west-1.amazonses.com\"}}"
    }';
}
