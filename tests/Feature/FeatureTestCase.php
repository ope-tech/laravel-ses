<?php
namespace Juhasev\LaravelSes\Tests\Feature;

use Juhasev\LaravelSes\SesMail;
use Juhasev\LaravelSes\LaravelSesServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class FeatureTestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'testbench']);
    }
    /**
     * Load package service provider
     * @param  \Illuminate\Foundation\Application $app
     * @return lasselehtinen\MyPackage\MyPackageServiceProvider
     */
    protected function getPackageProviders($app)
    {
        return [LaravelSesServiceProvider::class];
    }
    /**
     * Load package alias
     * @param  \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'SesMail' => SesMail::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('app.url', 'https://laravel-ses.com');
        $app['config']->set('app.debug', true);
    }


    public function generateBounceJson($messageId, $email = 'bounce@simulator.amazonses.com')
    {
        return '{"Type" : "Notification",
          "MessageId" : "950a823d-501f-5137-a9a3-d0246f6094b6",
          "TopicArn" : "arn:aws:sns:eu-west-1:111158800833:laravel-ses-Bounce",
          "Message" : "{\"notificationType\":\"Bounce\",\"bounce\":{\"bounceType\":\"Permanent\",\"bounceSubType\":\"General\",\"bouncedRecipients\":[{\"emailAddress\":\"'.$email.'\",\"action\":\"failed\",\"status\":\"5.1.1\",\"diagnosticCode\":\"smtp; 550 5.1.1 user unknown\"}],\"timestamp\":\"2017-08-24T20:55:27.843Z\",\"feedbackId\":\"0102015e16074124-76fb1d19-754a-4623-b37b-509eb649e884-000000\",\"remoteMtaIp\":\"207.171.163.188\",\"reportingMTA\":\"dsn; a7-12.smtp-out.eu-west-1.amazonses.com\"},\"mail\":{\"timestamp\":\"2017-08-24T20:55:27.000Z\",\"source\":\"test@laravel-ses.com\",\"sourceArn\":\"arn:aws:ses:eu-west-1:111158800833:identity/laravel-ses.com\",\"sourceIp\":\"127.0.0\",\"sendingAccountId\":\"111111111111\",\"messageId\":\"0102015e16073ec2-e6c0fd6b-17fb-4f8d-a1ce-82c68fe2a943-000000\",\"destination\":[\"'.$email.'\"],\"headersTruncated\":false,\"headers\":[{\"name\":\"Message-ID\",\"value\":\"<530389a196a58d2057754a9d8eeda262@swift.generated>\"},{\"name\":\"Date\",\"value\":\"Thu, 24 Aug 2017 20:55:27 +0000\"},{\"name\":\"Subject\",\"value\":\"test\"},{\"name\":\"From\",\"value\":\"test@laravel-ses.com\"},{\"name\":\"Reply-To\",\"value\":\"test@laravel-ses.com\"},{\"name\":\"To\",\"value\":\"'.$email.'\"},{\"name\":\"MIME-Version\",\"value\":\"1.0\"},{\"name\":\"Content-Type\",\"value\":\"text/html; charset=utf-8\"},{\"name\":\"Content-Transfer-Encoding\",\"value\":\"quoted-printable\"}],\"commonHeaders\":{\"from\":[\"test@laravel-ses.com\"],\"replyTo\":[\"test@laravel-ses.com\"],\"date\":\"Thu, 24 Aug 2017 20:55:27 +0000\",\"to\":[\"'.$email.'\"],\"messageId\":\"' . $messageId . '\",\"subject\":\"test\"}}}",
          "Timestamp" : "2017-08-24T20:55:27.883Z",
          "SignatureVersion" : "1",
          "Signature" : "EXAMPLEoRtETzzKxQhgINqozOINqCWecbs827aR4rbYQpMameLSzB9KbUl+pc630htDNFp8TRMe6z55CEERbWehRw//cZ2zI2Gt/qlYL5NdW54UrTJvNl4Sh4ifWatGbhfkWHsgjf4SnNNdAV+rgr4sB45mUwZMUuXcXTu1dKA07qXYYj+VTt3M8tPC9fXd+WvmnoakHi6fg4aqdPXzY5QhCYBJmWp5Io0qkQWKgxF3HJG91coRqp7NQcEfPz2CGcvT0EiPgZxh6D0y7fZNNrg/ThdOVxeFixYi1Ix67hCerQ9H7d6XBQzbYEHTUeVMRozAkFziTuoyQ==",
          "SigningCertURL" : "https://sns.eu-west-1.amazonaws.com/SimpleNotificationService-433026a4050d206028891664da859041.pem",
          "UnsubscribeURL" : "https://sns.eu-west-1.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:eu-west-1:111111111111:laravel-ses-Bounce:7546aed7-b188-46f6-913c-2f548c0cb251"}';
    }

    public function generateComplaintJson($messageId, $email = 'complaint@simulator.amazonses.com')
    {
        return '{
            "Type": "Notification",
            "MessageId": "950a823d-501f-5137-a9a3-d0246f6094b6",
            "TopicArn": "arn:aws:sns:eu-west-1:111111111111:laravel-ses-Bounce",
            "Message": "{\"notificationType\":\"Complaint\",\"complaint\":{\"complainedRecipients\":[{\"emailAddress\":\"'.$email.'\"}],\"timestamp\":\"2017-08-25T07:58:41.000Z\",\"feedbackId\":\"0102015e1866790f-365140b7-896b-11e7-90ec-fd10e954797f-000000\",\"userAgent\":\"Amazon SES Mailbox Simulator\",\"complaintFeedbackType\":\"abuse\"},\"mail\":{\"timestamp\":\"2017-08-25T07:58:39.000Z\",\"source\":\"test@laravel-ses.com\",\"sourceArn\":\"arn:aws:ses:eu-west-1:111111111111:identity/babecall.co.uk\",\"sourceIp\":\"127.0.0.1\",\"sendingAccountId\":\"111111111111\",\"messageId\":\"0102015e18666ec9-e00f3e03-f3fd-486f-9522-ebc919b8ea9c-000000\",\"destination\":[\"'.$email.'\"],\"headersTruncated\":false,\"headers\":[{\"name\":\"Message-ID\",\"value\":\"<049c6b53557871a2a1fb77e117f60971@swift.generated>\"},{\"name\":\"Date\",\"value\":\"Fri, 25 Aug 2017 07:58:39 +0000\"},{\"name\":\"Subject\",\"value\":\"test\"},{\"name\":\"From\",\"value\":\"test@laravel-ses.com\"},{\"name\":\"Reply-To\",\"value\":\"test@laravel-ses.com\"},{\"name\":\"To\",\"value\":\"'.$email.'\"},{\"name\":\"MIME-Version\",\"value\":\"1.0\"},{\"name\":\"Content-Type\",\"value\":\"text/html charset=utf-8\"},{\"name\":\"Content-Transfer-Encoding\",\"value\":\"quoted-printable\"}],\"commonHeaders\":{\"from\":[\"test@laravel-ses.com\"],\"replyTo\":[\"test@laravel-ses.com\"],\"date\":\"Fri, 25 Aug 2017 07:58:39 +0000\",\"to\":[\"'.$email.'\"],\"messageId\":\"'.$messageId.'\",\"subject\":\"test\"}}}"
        }';
    }

    public function generateDeliveryJson($messageId, $email = 'success@simulator.amazonses.com')
    {
        return '{
            "Type": "Notification",
            "MessageId": "950a823d-501f-5137-a9a3-d0246f6094b6",
            "TopicArn": "arn:aws:sns:eu-west-1:111111111111:laravel-ses-Bounce",
            "Message": "{\"notificationType\":\"Delivery\",\"mail\":{\"timestamp\":\"2017-08-25T07:58:39.096Z\",\"source\":\"test@laravel-ses.com\",\"sourceArn\":\"arn:aws:ses:eu-west-1:11153938800833:identity/laravel-ses.com\",\"sourceIp\":\"127.0.0.1\",\"sendingAccountId\":\"111100833\",\"messageId\":\"1112015e18666bf8-8277947d-f88b-47ef-8e1b-1c97d4d4e51a-000000\",\"destination\":[\"'.$email.'\"],\"headersTruncated\":false,\"headers\":[{\"name\":\"Message-ID\",\"value\":\"<a4947f1f3fdb397b3a7bf2d3b7d2f53e@swift.generated>\"},{\"name\":\"Date\",\"value\":\"Fri, 25 Aug 2017 07:58:38 +0000\"},{\"name\":\"Subject\",\"value\":\"test\"},{\"name\":\"From\",\"value\":\"test@laravel-ses.com\"},{\"name\":\"Reply-To\",\"value\":\"test@laravel-ses.com\"},{\"name\":\"To\",\"value\":\"'.$email.'\"},{\"name\":\"MIME-Version\",\"value\":\"1.0\"},{\"name\":\"Content-Type\",\"value\":\"text/html; charset=utf-8\"},{\"name\":\"Content-Transfer-Encoding\",\"value\":\"quoted-printable\"}],\"commonHeaders\":{\"from\":[\"test@laravel-ses.com\"],\"replyTo\":[\"test@laravel-ses.com\"],\"date\":\"Fri, 25 Aug 2017 07:58:38 +0000\",\"to\":[\"'.$email.'\"],\"messageId\":\"'.$messageId.'\",\"subject\":\"test\"}},\"delivery\":{\"timestamp\":\"2017-08-25T07:58:40.192Z\",\"processingTimeMillis\":1096,\"recipients\":[\"'.$email.'\"],\"smtpResponse\":\"250 2.6.0 Message received\",\"remoteMtaIp\":\"205.251.222.49\",\"reportingMTA\":\"b8-29.smtp-out.eu-west-1.amazonses.com\"}}"
        }';
    }
}
