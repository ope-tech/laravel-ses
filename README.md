![alt text](laravel-ses.png "Laravel SES")

# Laravel AWS Simple Email Service
A Laravel 6+ Package that allows you to get sending statistics for emails you send through AWS SES (Simple Email Service), 
including deliveries, opens, bounces, complaints and link tracking. This package was originally written by Oliveready7 and
had gone not maintained for years. I created this fork so that this package can be used with modern Laravel 6 & 7.
Please note that this package is still experimental and going thru testing.

Install via composer

Add to composer.json
```
composer require juhasev/laravel-ses
```
Make sure your app/config/services.php has SES values set

```
'ses' => [
    'key' => your_ses_key,
    'secret' => your_ses_secret,
    'domain' => your_ses_domain,
    'region' => your_ses_region,
],
```

Important to note that if you're using an IAM, it needs access to
SNS (for deliveries, bounces and complaints) as well as SES

Make sure your mail driver located in app/config/mail.php is set to 'ses'

Publish public assets

```
php artisan vendor:publish --tag=ses-assets --force
```

Publish migrations

```
php artisan vendor:publish --tag=ses-migrations --force
```

Optionally you can publish the package's config (laravelses.php)

```
php artisan vendor:publish --tag=ses-config
```

Config Options
- aws_sns_validator - whether the package uses AWS's SNS validator for inbound SNS requests. Default = false

https://github.com/aws/aws-php-sns-message-validator

Run command in **production** to setup Amazon email notifications to track bounces, complaints and deliveries. 
Make sure in your configuration your app URL is set correctly.

If your application uses the http protocol instead of https add the --http flag to this command

```
php artisan setup:sns
```

## Usage

To send an email with all tracking enabled

```
SesMail::enableAllTracking()
    ->to('hello@example.com')
    ->send(new Mailable);
```

All tracking allows you to track opens, bounces, deliveries, complaints and links


You can, of course, disable and enable all the tracking options

```
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

```
SesMail::enableAllTracking()
    ->setBatch('welcome_emails')
    ->to('hello@example.com')
    ->send(new Mailable);
```

You can manipulate the results manually by querying the database. Or you can use functions that come with the package.

```
SesMail::statsForBatch('welcome_emails');

//example result
[
    "send_count" => 8,
    "deliveries" => 7,
    "opens" => 4,
    "bounces" => 1,
    "complaints" => 2,
    "click_throughs" => 3,
    "link_popularity" => collect([
        "https://welcome.page" => [
            "clicks" => 3
        ],
        "https://facebook.com/brand" => [
            "clicks" => 1
        ]
    ])
]
```

Send count = number of emails that were attempted

Deliveries = number of emails that were delivered

Opens = number of emails that were opened

Complaints = number of people that put email into spam

Click throughs = number of people that clicked at least one link in your email

Link Popularity = number of unique clicks on each link in the email, ordered by the most clicked.



### API INFO

Data always has the 'success' key indicating whether the request was successful or not

400 bad request = validation for the endpoint failed

404 = something in your query was not found

422 = any other error that might have occurred

```GET /laravel-ses/api/has/bounced/{email}```

#### Parameters - none

#### Response

```json
    {
        "success": "true",
        "bounced": "true",
        "bounces": [
            {
                "message_id":"7a",
                "sent_email_id": "1",
                "type": "Permanent",
                "email": "harrykane@gmail.com",
                "bounced_at": "2018-01-01 12:00:00"
            }
        ]
    }
```

```GET /laravel-ses/api/has/complained/{email}```

#### Parameters - none

#### Response

```json
    {
        "success": "true",
        "complained": "true",
        "complaints": [
            {
                "message_id":"8b",
                "sent_email_id": "23",
                "type": "abuse",
                "email": "wanyama@hotmail.com",
                "complained_at": "2018-01-02 09:12:00"
            }
        ]
    }
```

```GET /laravel-ses/api/stats/batch/{batchName}```

#### Parameters - none

#### Response

```json
    {
        "success": "true",
        "data": {
            "success":true,
            "data": {
                "send_count":0,
                "deliveries":0,
                "opens":0,
                "bounces":0,
                "complaints":0,
                "click_throughs":0,
                "link_popularity":[]
            }
        }
    }
```

```GET /laravel-ses/api/stats/email/{email}```

#### Parameters - none

#### Response

```json
{
"success": true,
"data": {
    "counts": {
        "sent_emails": 3,
        "deliveries": 2,
        "opens": 1,
        "bounces": 1,
        "complaints": 1,
        "click_throughs": 2
    },
    "data": {
        "sent_emails": [
            {
                "id": 1,
                "message_id": "d3e2028a324fde23363ec3a073ecd436@swift.generated",
                "email": "something@gmail.com",
                "batch": "welcome_emails",
                "sent_at": "2018-03-18 10:28:26",
                "delivered_at": "2017-08-25 07:58:40",
                "complaint_tracking": "1",
                "delivery_tracking": "1",
                "bounce_tracking": "1",
                "created_at": "2018-03-18 10:28:26",
                "updated_at": "2018-03-18 10:28:26"
            },
            {
                "id": 9,
                "message_id": "b623bf23064c088fa044a58f22ddacad@swift.generated",
                "email": "something@gmail.com",
                "batch": "win_back",
                "sent_at": "2018-03-18 10:28:26",
                "delivered_at": "2017-08-25 07:58:40",
                "complaint_tracking": "1",
                "delivery_tracking": "1",
                "bounce_tracking": "1",
                "created_at": "2018-03-18 10:28:26",
                "updated_at": "2018-03-18 10:28:26"
            },
            {
                "id": 10,
                "message_id": "ec100dcd13ce91b6b28002173564d1b9@swift.generated",
                "email": "something@gmail.com",
                "batch": "june_newsletter",
                "sent_at": "2018-03-18 10:28:26",
                "delivered_at": null,
                "complaint_tracking": "1",
                "delivery_tracking": "1",
                "bounce_tracking": "1",
                "created_at": "2018-03-18 10:28:26",
                "updated_at": "2018-03-18 10:28:26"
            }
        ],
        "deliveries": [
            {
                "id": 1,
                "message_id": "d3e2028a324fde23363ec3a073ecd436@swift.generated",
                "email": "something@gmail.com",
                "batch": "welcome_emails",
                "sent_at": "2018-03-18 10:28:26",
                "delivered_at": "2017-08-25 07:58:40",
                "complaint_tracking": "1",
                "delivery_tracking": "1",
                "bounce_tracking": "1",
                "created_at": "2018-03-18 10:28:26",
                "updated_at": "2018-03-18 10:28:26"
            },
            {
                "id": 9,
                "message_id": "b623bf23064c088fa044a58f22ddacad@swift.generated",
                "email": "something@gmail.com",
                "batch": "win_back",
                "sent_at": "2018-03-18 10:28:26",
                "delivered_at": "2017-08-25 07:58:40",
                "complaint_tracking": "1",
                "delivery_tracking": "1",
                "bounce_tracking": "1",
                "created_at": "2018-03-18 10:28:26",
                "updated_at": "2018-03-18 10:28:26"
            }
        ],
        "opens": [
            {
                "id": 1,
                "sent_email_id": "1",
                "email": "something@gmail.com",
                "batch": "welcome_emails",
                "beacon_identifier": "bfd935de-2219-4f86-9bd3-24afd06cc37a",
                "url": "https://laravel-ses.com/laravel-ses/beacon/bfd935de-2219-4f86-9bd3-24afd06cc37a",
                "opened_at": "2018-03-18 10:28:26",
                "created_at": "2018-03-18 10:28:26",
                "updated_at": "2018-03-18 10:28:26"
            }
        ],
        "bounces": [
            {
                "id": 3,
                "message_id": "b623bf23064c088fa044a58f22ddacad@swift.generated",
                "sent_email_id": "9",
                "type": "abuse",
                "email": "something@gmail.com",
                "complained_at": "2017-08-25 07:58:39",
                "created_at": "2018-03-18 10:28:26",
                "updated_at": "2018-03-18 10:28:26"
            }
        ],
        "complaints": [
            {
                "id": 3,
                "message_id": "b623bf23064c088fa044a58f22ddacad@swift.generated",
                "sent_email_id": "9",
                "type": "abuse",
                "email": "something@gmail.com",
                "complained_at": "2017-08-25 07:58:39",
                "created_at": "2018-03-18 10:28:26",
                "updated_at": "2018-03-18 10:28:26"
            }
        ],
        "click_throughs": [
            {
                "id": 1,
                "link_identifier": "0f14e25d-4712-4a9a-be6b-b190dc1a31b3",
                "sent_email_id": "1",
                "original_url": "https://google.com",
                "batch": "welcome_emails",
                "clicked": "1",
                "click_count": "1",
                "created_at": "2018-03-18 10:28:26",
                "updated_at": "2018-03-18 10:28:26",
                "message_id": "d3e2028a324fde23363ec3a073ecd436@swift.generated",
                "email": "something@gmail.com",
                "sent_at": "2018-03-18 10:28:26",
                "delivered_at": "2017-08-25 07:58:40",
                "complaint_tracking": "1",
                "delivery_tracking": "1",
                "bounce_tracking": "1"
            },
            {
                "id": 1,
                "link_identifier": "c7933d15-8e22-41e7-9074-e327ee02cf1a",
                "sent_email_id": "1",
                "original_url": "https://superficial.io",
                "batch": "welcome_emails",
                "clicked": "1",
                "click_count": "1",
                "created_at": "2018-03-18 10:28:26",
                "updated_at": "2018-03-18 10:28:26",
                "message_id": "d3e2028a324fde23363ec3a073ecd436@swift.generated",
                "email": "something@gmail.com",
                "sent_at": "2018-03-18 10:28:26",
                "delivered_at": "2017-08-25 07:58:40",
                "complaint_tracking": "1",
                "delivery_tracking": "1",
                "bounce_tracking": "1"
            },
            {
                "id": 9,
                "link_identifier": "be2d0bc9-87b7-48cc-8b57-6b714bfc9d48",
                "sent_email_id": "9",
                "original_url": "https://google.com",
                "batch": "win_back",
                "clicked": "1",
                "click_count": "1",
                "created_at": "2018-03-18 10:28:26",
                "updated_at": "2018-03-18 10:28:26",
                "message_id": "b623bf23064c088fa044a58f22ddacad@swift.generated",
                "email": "something@gmail.com",
                "sent_at": "2018-03-18 10:28:26",
                "delivered_at": "2017-08-25 07:58:40",
                "complaint_tracking": "1",
                "delivery_tracking": "1",
                "bounce_tracking": "1"
            }
        ]
    }
}
```
