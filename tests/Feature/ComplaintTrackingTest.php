<?php

namespace oliveready7\LaravelSes\Tests\Feature;

use oliveready7\LaravelSes\Models\SentEmail;
use oliveready7\LaravelSes\Models\EmailComplaint;
use oliveready7\LaravelSes\Tests\Feature\FeatureTestCase;

class ComplaintTrackingTest extends FeatureTestCase
{
    public function testComplaintTracking()
    {
        SentEmail::create([
            'message_id' => '049c6b53557871a2a1fb77e117f60971@swift.generated',
            'email' => 'eriksen23@gmail.com',
            'complaint_tracking' => true
        ]);

        $fakeJson = json_decode($this->exampleSesResponse);
        $res = $this->json(
            'POST',
            'laravel-ses/notification/complaint',
            (array)$fakeJson
        );

        //check bounce is logged correctly, note email Amazon returns is set as email rather than email set in sent email
        $this->assertArraySubset([
            'type' => 'abuse',
            'message_id' => '049c6b53557871a2a1fb77e117f60971@swift.generated',
            'sent_email_id' => 1,
            'email' => 'complaint@simulator.amazonses.com'
        ], EmailComplaint::first()->toArray());
    }


    public function testAComplaintIsNotStoredWhenThereIsNoEquivlanentMessageId()
    {
        SentEmail::create([
            'message_id' => 'abcaseasyas123@swift.generated',
            'email' => 'eriksen23@gmail.com',
            'complaint_tracking' => true
        ]);


        $fakeJson = json_decode($this->exampleSesResponse);
        $res = $this->json(
            'POST',
            'laravel-ses/notification/bounce',
            (array)$fakeJson
        );

        $this->assertNull(EmailComplaint::first());
    }

    public function testSubscriptionConfirmation()
    {
        $fakeJson = json_decode($this->exampleSubscriptionResponse);
        $response = $this->json(
            'POST',
            '/laravel-ses/notification/complaint',
            (array)$fakeJson
        )->assertJson(['success' => true]);
    }

    public function testThatComplaintIsNotRecordedIfComplaintTrackingIsNotSet()
    {
        SentEmail::create([
            'message_id' => '049c6b53557871a2a1fb77e117f60971@swift.generated',
            'email' => 'eriksen23@gmail.com'
        ]);

        $fakeJson = json_decode($this->exampleSesResponse);
        $res = $this->json(
            'POST',
            'laravel-ses/notification/complaint',
            (array)$fakeJson
        );

        $this->assertNull(EmailComplaint::first());
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
    	"Message": "{\"notificationType\":\"Complaint\",\"complaint\":{\"complainedRecipients\":[{\"emailAddress\":\"complaint@simulator.amazonses.com\"}],\"timestamp\":\"2017-08-25T07:58:41.000Z\",\"feedbackId\":\"0102015e1866790f-365140b7-896b-11e7-90ec-fd10e954797f-000000\",\"userAgent\":\"Amazon SES Mailbox Simulator\",\"complaintFeedbackType\":\"abuse\"},\"mail\":{\"timestamp\":\"2017-08-25T07:58:39.000Z\",\"source\":\"test@laravel-ses.com\",\"sourceArn\":\"arn:aws:ses:eu-west-1:111111111111:identity/babecall.co.uk\",\"sourceIp\":\"127.0.0.1\",\"sendingAccountId\":\"111111111111\",\"messageId\":\"0102015e18666ec9-e00f3e03-f3fd-486f-9522-ebc919b8ea9c-000000\",\"destination\":[\"complaint@simulator.amazonses.com\"],\"headersTruncated\":false,\"headers\":[{\"name\":\"Message-ID\",\"value\":\"<049c6b53557871a2a1fb77e117f60971@swift.generated>\"},{\"name\":\"Date\",\"value\":\"Fri, 25 Aug 2017 07:58:39 +0000\"},{\"name\":\"Subject\",\"value\":\"test\"},{\"name\":\"From\",\"value\":\"test@laravel-ses.com\"},{\"name\":\"Reply-To\",\"value\":\"test@laravel-ses.com\"},{\"name\":\"To\",\"value\":\"complaint@simulator.amazonses.com\"},{\"name\":\"MIME-Version\",\"value\":\"1.0\"},{\"name\":\"Content-Type\",\"value\":\"text/html charset=utf-8\"},{\"name\":\"Content-Transfer-Encoding\",\"value\":\"quoted-printable\"}],\"commonHeaders\":{\"from\":[\"test@laravel-ses.com\"],\"replyTo\":[\"test@laravel-ses.com\"],\"date\":\"Fri, 25 Aug 2017 07:58:39 +0000\",\"to\":[\"complaint@simulator.amazonses.com\"],\"messageId\":\"<049c6b53557871a2a1fb77e117f60971@swift.generated>\",\"subject\":\"test\"}}}"
    }';
}
