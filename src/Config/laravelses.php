<?php

return [

    /**
     * Whether to use AWS SNS validator. This is probably a good idead
     *
     * https://github.com/aws/aws-php-sns-message-validator
     */

    'aws_sns_validator' => env('SES_SNS_VALIDATOR', false),

    /**
     * Enable debug mode. In this mode you can test incoming AWS routes
     * manually. No data is saved to the database in this mode.
     *
     * NOTE: You cannot run package unit tests with this enabled!
     */

    'debug' => env('SES_DEBUG', false),

    /**
     * Log prefix for all logged messages. Set to whatever you want for convenient debugging
     */

    'log_prefix' => 'LARAVEL-SES'
];
