![alt text](laravel-ses.png "Laravel SES")

# Laravel SES (Simple Email Service AWS)
Laravel SES is package that allows you to get sending statistics for emails you send through AWS SES (Simple Email Service), 
including deliveries, opens, bounces, complaints and link tracking. This package was originally written by Oliveready7.
Unfortunately the original author had stopped maintaining this package so I decided to create this fork so that this 
package can be used with current versions of Laravel. The minimum requirement is PHP 7.3.

All packages have been updated to modern versions. I have optimized the original database
storage for space and proper indexing. This package is compatible with Laravel 8.x.

Laravel SES also supports SMTP errors codes will throw meaning exceptions like when you exceed your rate limits so you can handle proper back off.

If you are using Laravel 8 use the dev-master branch.

In your composer.json use

```json
"juhasev/laravel-ses": "dev-master"
```

Then run

```bash
composer install
```

## Installation
Install via composer

```bash
composer require juhasev/laravel-ses
composer require aws/aws-php-sns-message-validator (optional)
```

In config/app.php make sure you load up the service provider. This should happen automatically.
```php
Juhasev\LaravelSes\LaravelSesServiceProvider::class
```

## Laravel configuration
Make sure your app/config/services.php has SES values set

```php
'ses' => [
    'key' => your_ses_key,
    'secret' => your_ses_secret,
    'domain' => your_ses_domain,
    'region' => your_ses_region
],
```

Make sure your mail driver located in app/config/mail.php is set to 'ses'
```php
    'default' => env('MAIL_MAILER', 'ses')
```

Publish public assets

```bash
php artisan vendor:publish --tag=ses-assets --force
```

Publish migrations

```bash
php artisan vendor:publish --tag=ses-migrations --force
```

Publish the package's config (laravelses.php)

```bash
php artisan vendor:publish --tag=ses-config
```

### Routes
This package add 3 public routes to your application that AWS SNS callbacks target
```
/ses/notification/bounce
/ses/notification/complaint
/ses/notification/delivery
```
We also add two more public routes for tracking opens and link clicks
```
/ses/beacon
/ses/link
```

Config Options

- aws_sns_validator - whether the package uses AWS's SNS validator for inbound SNS requests. Default = false
- debug - Debug mode that logs all SNS call back requests

https://github.com/aws/aws-php-sns-message-validator

## AWS Configuration

### Pre-reading
If you are new to using SES Notification this article is a good starting point

https://docs.aws.amazon.com/sns/latest/dg/sns-http-https-endpoint-as-subscriber.html

### IAM User and policies
Your application IAM user needs to be send email via SES and subscribe to
SNS notifications. This can be done in the AWS Control Panel as the article above suggests or
AWS CloudFormation template like one below:

AWS CloudFormation policy example:
```
  ApplicationSNSPolicy:
    Type: "AWS::IAM::ManagedPolicy"
    Properties:
      Description: "Policy for sending subscribing to SNS bounce notifications"
      Path: "/"
      PolicyDocument:
        Version: "2012-10-17"
        Statement:
          - Effect: Allow
            Action:
              - sns:CreateTopic
              - sns:DeleteTopic
              - sns:Subscribe
              - sns:Unsubscribe
            Resource:
              - 'arn:aws:sns:*'

  ApplicationSESPolicy:
    Type: "AWS::IAM::ManagedPolicy"
    Properties:
      Description: "Policy for creating SES bounce notification"
      Path: "/"
      PolicyDocument:
        Version: "2012-10-17"
        Statement:
          - Effect: Allow
            Action:
              - ses:*
            Resource:
              - '*'

```

Once policies are defined they need to added to the configured IAM user. 

```
  # AWS PHP API User
  APIUser:
    Type: "AWS::IAM::User"
    Properties:
      ManagedPolicyArns:
        - !Ref ApplicationSNSPolicy
        - !Ref ApplicationSESPolicy
      UserName: staging-user
```

### Running setup
Make sure in your APP_URL (in .env) is set correctly, matching your sending domain. 
If you do send email for multiple domains (i.e. multi tenant application) you can set multiple domains 
using this command.

> You need to have SES domain ready before continuing

The setup command automatically configures your SES domain to send SNS notifications that
trigger call backs to your Laravel application.

```bash
php artisan sns:setup mydomain.com
```
> NOTE: You should not attempt to use sub domains client.mydomain.com, this is not currently supported by AWS.

## Usage

To send an email with all tracking enabled

```php
SesMail::enableAllTracking()
    ->to('hello@example.com')
    ->send(new Mailable);
```

Calling enableAllTracking() enables open, reject, bounce, delivery, complaint and link tracking.

> Please note that an LaravelSesTooManyRecipients Exception is thrown if you attempt send a Mailable that contains multiple recipients when Open -tracking is enabled.

Other exception thrown are:

```php
LaravelSesDailyQuotaExceededException::class
LaravelSesInvalidSenderAddressException::class
LaravelSesMaximumSendingRateExceeded::class
LaravelSesSendFailedException::class
LaravelSesTemporaryServiceFailureException::class
LaravelSesTooManyRecipientsException::class
```

You can catch them all using the base class:

```php
try {
    SesMail::enableAllTracking()
        ->to('hello@example.com')
        ->send(new Mailable);
        
} catch (LaravelSesMaximumSendingRateExceeded $e) {

    // Implement back off logic

} catch (LaravelSesException $e) {
    
    $smtpCode = $e->getCode();
    $smtpErrorMessage = $e->getMessage();
    
    // Do something like back of if rate limit is reached.
)

You can, of course, disable and enable all the tracking options

```php
SesMail::disableAllTracking();
SesMail::disableOpenTracking();
SesMail::disableLinkTracking();
SesMail::disableBounceTracking();
SesMail::disableComplaintTracking();
SesMail::disableDeliveryTracking();

SesMail::enableAllTracking();
SesMail::enableOpenTracking();
SesMail::enableLinkTracking();
SesMail::enableBounceTracking();
SesMail::enableComplaintTracking();
SesMail::enableDeliveryTracking();
```

The batching option gives you the chance to group emails, so you can get the results for a specific group

```php
SesMail::enableAllTracking()
    ->setBatch('welcome_emails')
    ->to('hello@example.com')
    ->send(new Mailable);
```

You can also get aggregate stats:

```php

Stats::statsForEmail($email);

$stats = Stats::statsForBatch('welcome_emails');

print_r($stats)

```

```
[
    "sent" => 8,
    "deliveries" => 7,
    "opens" => 4,
    "bounces" => 1,
    "complaints" => 2,
    "clicks" => 3,
    "link_popularity" => [
        "https://welcome.page" => [
            "clicks" => 3
        ],
        "https://facebook.com/brand" => [
            "clicks" => 1
        ]
    ]
]
```

To get individual stats via Repositories
```php
EmailStatRepository::getBouncedCount($email);
EmailRepository::getBounces($email);

BatchStatRepository::getBouncedCount($batch);
BatchStatRepository::getDeliveredCount($batch);
BatchStatRepository::getComplaintsCount($batch);

```
You can also use the models directly as you would any other Eloquent model:

```php
$sentEmails = SentEmail::whereEmail($email)->get();

$emailBounces = EmailBounce::whereEmail($email)->get();
$emailComplaints = EmailComplaint::whereEmail($email)->get();
$emailLink = EmailLink::whereEmail($email)->get();
$emailOpen = EmailOpen::whereEmail($email)->get();

```
If you are using custom models then you can use ModelResolver() helper like so
```php
$sentEmail = ModelResolver::get('SentEmail')::take(100)->get();
```
### Listening to event

Event subscriber can be created:
```php
<?php

namespace App\Listeners;

use App\Actions\ProcessSesEvent;
use Juhasev\LaravelSes\Factories\Events\SesBounceEvent;
use Juhasev\LaravelSes\Factories\Events\SesComplaintEvent;
use Juhasev\LaravelSes\Factories\Events\SesDeliveryEvent;
use Juhasev\LaravelSes\Factories\Events\SesOpenEvent;
use Juhasev\LaravelSes\Factories\Events\SesSentEvent;

class SesSentEventSubscriber
{
    /**
     * Subscribe to events
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(SesBounceEvent::class, SesSentEventSubscriber::class . '@bounce');
        $events->listen(SesComplaintEvent::class, SesSentEventSubscriber::class . '@complaint');
        $events->listen(SesDeliveryEvent::class,SesSentEventSubscriber::class . '@delivery');
        $events->listen(SesOpenEvent::class, SesSentEventSubscriber::class . '@open');
        $events->listen(SesLinkEvent::class, SesSentEventSubscriber::class . '@link');
    }

    /**
     * SES bounce event took place
     *
     * @param SesBounceEvent $event
     */
    public function bounce(SesBounceEvent $event)
    {
        // Do something
    }

    /**
     * SES complaint event took place
     *
     * @param SesComplaintEvent $event
     */
    public function complaint(SesComplaintEvent $event)
    {
        // Do something
    }

    /**
     * SES delivery event took place
     *
     * @param SesDeliveryEvent $event
     */
    public function delivery(SesDeliveryEvent $event)
    {
        // Do something
    }

    /**
     * SES Open open event took place
     *
     * @param SesOpenEvent $event
     */
    public function open(SesOpenEvent $event)
    {
        // Do something
    }
   /**
     * SES Open link event took place
     *
     * @param SesLinkEvent $event
     */
    public function link(SesLinkEvent $event)
    {
        // Do something
    }

}
```

You will need to register EventSubscriber in the Laravel App/Providers/EventServiveProvider.php in order to work.

```php
 protected $subscribe = [
    SesSentEventSubscriber::class
 ];
 ```  

Example event data:

```php
print_r($event->data);
```

```
(
    [id] => 22
    [sent_email_id] => 49
    [type] => Permanent
    [bounced_at] => 2020-04-03 19:42:31
    [sent_email] => Array
        (
            [id] => 49
            [message_id] => 31b530dce8e2a282d12e5627e7109580@localhost
            [email] => bounce@simulator.amazonses.com
            [batch_id] => 7
            [sent_at] => 2020-04-03 19:42:31
            [delivered_at] => 
            [batch] => Array
                (
                    [id] => 7
                    [name] => fa04cbf2c2:Project:268
                    [created_at] => 2020-04-03 17:03:23
                    [updated_at] => 2020-04-03 17:03:23
                )

        )
    )
)
```


### Terminology
Sent = number of emails that were attempted

Deliveries = number of emails that were delivered

Opens = number of emails that were opened

Complaints = number of people that put email into spam

Clicks = number of people that clicked at least one link in your email

Link Popularity = number of unique clicks on each link in the email, ordered by the most clicked.

## Development

Clone Laravel SES repo to your project under /packages

```
git clone https://github.com/juhasev/laravel-ses.git
```

Setup Composer.json to resolve classes from your dev folder:

```json
 "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Juhasev\\LaravelSes\\": "packages/juhasev/laravel-ses/src"
    }
  },
```

Composer require
```json
require: {
    "juhasev/laravel-ses": "dev-master"
}
```

Or run require

```bash
composer require juhasev/laravel-ses:dev-master
```

To run unit tests execute
```bash
phpunit
```

