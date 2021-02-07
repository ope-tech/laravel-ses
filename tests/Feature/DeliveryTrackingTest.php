<?php

namespace Juhasev\LaravelSes\Tests\Feature;

use Exception;
use Illuminate\Support\Facades\Event;
use Juhasev\LaravelSes\Factories\Events\SesDeliveryEvent;
use Juhasev\LaravelSes\ModelResolver;
use Juhasev\LaravelSes\Tests\FeatureTestCase;

class DeliveryTrackingTest extends FeatureTestCase
{
    public function testDeliveryTracking()
    {
        ModelResolver::get('SentEmail')::create([
            'message_id' => '010101777df559d4-5080db0f-5e72-43aa-af23-3cdeca00807c-000000',
            'email' => 'eriksen23@gmail.com',
            'delivery_tracking' => true
        ]);

        $fakeJson = json_decode($this->exampleSesResponse);

        if ($fakeJson === null) {
            throw new Exception("Fake json failed to parse");
        }

        Event::fake();

        $this->json(
            'POST',
            '/ses/notification/delivery',
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
            '/ses/notification/delivery',
            (array)$fakeJson
        )->assertJson(['success' => true]);
    }

    public function testTopicResponse()
    {
        $fakeJson = json_decode($this->exampleTopicResponse);
        $response = $this->json(
            'POST',
            '/ses/notification/delivery',
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
            '/ses/notification/delivery',
            (array)$fakeJson
        );

        $this->assertNull(ModelResolver::get('SentEmail')::first()->delivered_at);
    }

    private $exampleTopicResponse = '{
          "Type": "Notification",
          "MessageId": "6abf341d-f4e7-5d58-a5f6-6c84bc4e39f2",
          "TopicArn": "arn:aws:sns:us-west-2:635608510762:staging-ses-delivery-us-west-2",
          "Message": "Successfully validated SNS topic for Amazon SES event publishing.",
          "Timestamp": "2021-02-07T01:46:17.368Z",
          "SignatureVersion": "1",
          "Signature": "KoisQ3njC6m+gkr6GlSoX8NA+XLEVUZ2tgBPfQ4VP2uIZSL1YCpnUUfoH1IYflo+PniNbVummhiEWNAYvNYF31vihbwiMqXwXWZ3xS23YxflknPDYNF8hBYZkBG66S1arRvNtw6F+JsxgQd6nZrs4RMADALRaD8vu79C5ZsEnFATUIOrdWOML7XKd3/kXnHKbxZvwpjhCTYu7x0Srb378OMMl9ax5/I0465zs2XSL/LaP5NB3aQp9DSGOJTDUlEh0C8wXZceJr3c9PlYQStbMkqDdzeqBy4Gbrtnx/28CSKgh9Hx1UuAAeZvVmjmYmFco1nobu8+m2H/cpx6mllQNQ==",
          "SigningCertURL": "https://sns.us-west-2.amazonaws.com/SimpleNotificationService-010a507c1833636cd94bdb98bd93083a.pem",
          "UnsubscribeURL": "https://sns.us-west-2.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:us-west-2:635608510762:staging-ses-delivery-us-west-2:43df3888-7e5e-4e35-83b7-3247d9947525"
    }';

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
        "MessageId": "bbe17393-1d62-51ee-baaf-2b095c738701",
        "TopicArn": "arn:aws:sns:us-west-2:635608510762:staging-ses-delivery-us-west-2",
        "Subject": "Amazon SES Email Event Notification",
        "Message": "{\"eventType\":\"Delivery\",\"mail\":{\"timestamp\":\"2021-02-07T19:26:07.316Z\",\"source\":\"invite@staging.sampleninja.io\",\"sourceArn\":\"arn:aws:ses:us-west-2:635608510762:identity/sampleninja.io\",\"sendingAccountId\":\"635608510762\",\"messageId\":\"010101777df559d4-5080db0f-5e72-43aa-af23-3cdeca00807c-000000\",\"destination\":[\"eriksen23@gmail.com\"],\"headersTruncated\":false,\"headers\":[{\"name\":\"Received\",\"value\":\"from [127.0.0.1] (ec2-34-222-112-151.us-west-2.compute.amazonaws.com [34.222.112.151]) by email-smtp.amazonaws.com with SMTP (SimpleEmailService-d-86T2QZXB8) id 65fhQokClkTT7psVXZJo for eriksen23@gmail.com; Sun, 07 Feb 2021 19:26:07 +0000 (UTC)\"},{\"name\":\"Message-ID\",\"value\":\"<f15a35e162a7324363785b71f5813cf7@swift.generated>\"},{\"name\":\"Date\",\"value\":\"Sun, 07 Feb 2021 19:26:07 +0000\"},{\"name\":\"Subject\",\"value\":\"We want your feedback!\"},{\"name\":\"From\",\"value\":\"The Sample Ninja Team <invite@staging.sampleninja.io>\"},{\"name\":\"To\",\"value\":\"eriksen23@gmail.com\"},{\"name\":\"MIME-Version\",\"value\":\"1.0\"},{\"name\":\"X-SES-CONFIGURATION-SET\",\"value\":\"staging-ses-us-west-2\"}],\"commonHeaders\":{\"from\":[\"The Sample Ninja Team <invite@staging.sampleninja.io>\"],\"date\":\"Sun, 07 Feb 2021 19:26:07 +0000\",\"to\":[\"eriksen23@gmail.com\"],\"messageId\":\"010101777df559d4-5080db0f-5e72-43aa-af23-3cdeca00807c-000000\",\"subject\":\"We want your feedback!\"},\"tags\":{\"ses:operation\":[\"SendSmtpEmail\"],\"ses:configuration-set\":[\"staging-ses-us-west-2\"],\"ses:source-ip\":[\"34.222.112.151\"],\"ses:from-domain\":[\"staging.sampleninja.io\"],\"ses:caller-identity\":[\"ses-smtp-user.20190503-125922\"],\"ses:outgoing-ip\":[\"54.240.27.42\"]}},\"delivery\":{\"timestamp\":\"2021-02-07T19:26:09.114Z\",\"processingTimeMillis\":1798,\"recipients\":[\"eriksen23@gmail.com\"],\"smtpResponse\":\"250 2.0.0 OK 1612725969 y7si5679644pgb.218 - gsmtp\",\"reportingMTA\":\"a27-42.smtp-out.us-west-2.amazonses.com\"}}\n",
        "Timestamp": "2021-02-07T19:26:09.194Z",
        "SignatureVersion": "1"
    }';
}
