# laravel-ses
A Laravel Package that allows you to get simple sending statistics for emails you send through SES, including deliveries, opens, bounces, complaints and link tracking

### This package is still in early development

## Installation (will be put on packagist later)

Install via composer

Add to composer.json
```
"repositories": [
    {
        "type": "git",
        "url": "git@github.com:oliveready7/laravel-ses.git"
    }
],


"require": {
    "oliveready7/laravel-ses": "dev-master"
}
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

Run command to setup Amazon email notifications

```
php artisan setup:sns
```

Publish public assets

```
php artisan vendor:publish --tag=public --force
```

Lastly migrate package's database tables

```
php artisan migrate
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
oliveready7\LaravelSes\Models\SentEmail::statsForBatch('welcome_emails');

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
                "bounced_at": "2018-01-02 09:12:00"
            }
        ]
    }
```
