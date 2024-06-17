## Remove, redudant columns.

### sent email columns

complaint_tracking, bounce_tracking, delivery_tracking can be removed from the laravel_ses_sent_emails table. These are all tracked by default and don't need to be set per email sent.

### Beacon identifier

beacon_identifier can be removed from the laravel_ses_email_opens table. This is now all handle by SES, so we don't need to worry about adding beacons for open tracking.

By default, this pixel is inserted at the bottom of the email; however, some email providers’ applications truncate the preview of an email when it exceeds a certain size and may provide a link to view the remainder of the message. In this scenario, the SES pixel tracking image does not load and will throw off the open rates you’re trying to track. To get around this, you can optionally place the pixel at the beginning of the email, or anywhere else, by inserting the {{ses:openTracker}} placeholder into the email body. Once SES receives the message with the placeholder, it will be replaced with open tracking pixel image.

Important
Just add one {{ses:openTracker}} placeholder, as more than one will result in a 400 BadRequestException error code being returned.
