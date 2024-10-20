# Package details

## This package is current in BETA mode so please be prepared for breaking changes every now and then

Track open rates, deliveries bounce rates, complaints, link clicks and more for every single email you send via SES in your Laravel App.

Setup tracking in minutes by reading the installtion instructions below.

This package uses the SESV2Client to leverage configuration sets and SES's built in tracking. It doesn't use the list management, or campaign management.

## Installation

### Install the package

`composer require opetech/laravelses`

### Add Laravel SES as a mailer

Add to config/mail.php under mailers, to make sure the LaravelSes transport is available.

```php
'laravel-ses' => [
    'transport' => 'laravel-ses',
]
```

### Migrations

Run the migrations for the packages.

`php artisan migrate`

### Publish the config

`php artisan vendor:publish --provider="OpeTech\LaravelSes\LaravelSesServiceProvider`

### Setup SES and SNS

_For this you'll need IAM credentials that have full access to SNS and SES. Add these crendentials as you would for using SES without the package. i.e. config/mail.php_

```
'ses' => [
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
],
```

Before running the below command, make sure your app's endpoints are publically available. SNS will hit your endpoints to confirm the subscriptions. If you're testing or playing around in development, we recommended you use a service like Ngrok to temporarily allow the public internet to interact with your app.

**Your APP_URL needs to be set correctly.**

Run `laravel-ses:setup-config-and-sns`

-   You will be prompted for a custom redirect domain. This is optional, but recommended for serious users. See [Configuring the redirect domain](#configuring-the-redirect-domain) for more info.

This will create a configuration set called "laravel-ses-{env}-configuration-set". You can customise the prefix by changing the config option "prefix".

It will also create an event destination (laravel-ses-{env}-event-destination) and an SNS topic (laravel-ses-{env}-topic). Again the prefix is customisable by changing the prefix config option.

### Configuring the redirect domain

_OPTIONAL_

This option isn't required, as AWS will use its own domain, however it is suggested, as email providers see links matching the sending domain in your email content as a positive. From a user's point of view they will see AWS urls instead of your own URLs, which may put them off clicking links in your email.

The tracking domain should ideally be a subdomain of the sending domain's domain. This is better for spam scores.
For example: sending domain - myemails.com, tracking domain. ses.myemails.com. Or if you're using a subdomain to send emails e.g. emails.example.com, use ses.example.com for the tracking.

We would also suggest avoiding using tracking in the subdomain as plugins such as ad blockers, malware detection etc, don't look favourably on the wording and could block the email, or break the content.

To configure a custom tracking domain follow these steps - https://docs.aws.amazon.com/ses/latest/dg/configure-custom-open-click-domains.html#configure-custom-open-click-domain.

We would also recommend setting up a custom "MAIL FROM" domain, otherwise messages sent through Amazon SES will be marked as originating from a subdomain of amazon.com. Instead of your own domain. This allows you to comply with DMARC policies and improves trust.

### Batching

For the purpose of grouping stats, you can use "batching". Implement the `OpeTech\LaravelSes\Contracts\Batchable` contract, use the `OpeTech\LaravelSes\Mailables\Batching` trait and implement the `getBatch` method.

`getBatch` should return a string representing your batch.

LaravelSes will create a new batch if the batch doesn't exist at the time of sending.

batches are held in the `laravel_ses_batches` table.

#### Batching via Mailables

You can use batching directly on your Mailables:

```php
<?php

use OpeTech\LaravelSes\Contracts\Batchable;
use OpeTech\LaravelSes\Mailables\Batching;

class MyMailable extends Mailable implements Batchable
{
    public function getBatch() :string
    {
        return 'my-first-batch';
    }
}


```

### Batching via the Mail Facade

You can call the withBatch method, and provide the name for your batch.

**Warning:** This won't work if you're sending a queued mailable. Use [Batching via Mailables](#batching-via-mailables) instead. This is mainly used if you're not using mailables e.g. using the `raw` method.

```php
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

 Mail::withBatch('exports')
    ->raw('Your export is attached', function (Message $message) {
        return $message
            ->attach('yourexport.csv')
            ->to('hello@example.com')
            ->subject('My Subject')
            ->from('sender@example.com');
    });
```

### Batching Mail Notifications

You can add batching to your notiflacations using the mail channel. Instead of using `MailMessage` you need to use `OpeTech\LaravelSes\Notifications\MailMessageWithBatching`

```php
 public function toMail(object $notifiable)
{
    return (new MailMessageWithBatching)
        ->from('hello@example.com')
        ->batch('restock_alerts')
        ->subject('The item you were watching has been restocked')

        ->line('Asics Superblasts have been restocked in your size')
        ->action('View Product', url('path/to/product'));
}
```
