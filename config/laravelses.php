<?php

return [

    /**
     * Prefixed added to your AWS resources. This is so you can have multiple SES configurations in the same AWS account.
     */
    'prefix' => 'laravel-ses',

    /**
     * Turning these on saves the raw data to the respesctive tables.
     * This can be useful for debugging and auditing.
     * It's not on by default since it will consume a lot of storage in your DB.
     */
    'log_raw_data' => [
        'bounces' => false,
        'complaints' => false,
        'deliveries' => false,
        'sends' => false,
        'opens' => false,
        'clicks' => false,
        'rejects' => false,
    ],

    /**
     * Specify which queue connection to use for the the SES library.
     * If you don't specify a queue connection, it will use the default queue connection.
     */
    'queue_connection' => null,

    /**
     * Specify which queue to use for each of the features. If the queue is null, jobs will be dispatched
     * on the sync connection, meaning all jobs will run synchronously. Would not recommend using this in production.
     * Especially if you're sending mass marketing emails.
     */
    'queues' => [
        'sns_notifications' => null,
        'sending' => null,
    ],
];
