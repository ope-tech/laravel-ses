<?php

namespace Juhasev\LaravelSes\Controllers;

use Aws\Sns\Exception\InvalidSnsMessageException;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Juhasev\LaravelSes\ModelResolver;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

class BaseController extends Controller
{
    /**
     * Validate SNS requests from AWS
     *
     * @param ServerRequestInterface $request
     */

    protected function validateSns(ServerRequestInterface $request)
    {
        if (config('laravelses.aws_sns_validator')) {

            $message = Message::fromPsrRequest($request);

            $validator = new MessageValidator();

            try {

                $validator->validate($message);
            } catch (InvalidSnsMessageException $e) {

                // Pretend we're not here if the message is invalid
                abort(404, 'Not Found');
            }
        }
    }

    /**
     * Parse message ID out of message
     *
     * @param stdClass $message
     * @return string
     */

    protected function parseMessageId(stdClass $message): string
    {
        $messageId = $message
            ->mail
            ->commonHeaders
            ->messageId;

        $messageId = str_replace('<', '', $messageId);
        $messageId = str_replace('>', '', $messageId);

        return $messageId;
    }

    /**
     * Make call back to AWS to confirm subscription
     *
     * @param stdClass $result
     * @return void
     */

    protected function confirmSubscription(stdClass $result): void
    {
        $client = new Client;
        $client->get($result->SubscribeURL);

        $this->logMessage("Subscribed to: " . $result->TopicArn);
    }

    /**
     * If AWS is trying to confirm subscription
     *
     * @param stdClass $result
     * @return bool
     */

    protected function isSubscriptionConfirmation(stdClass $result): bool
    {
        return isset($result->Type) && $result->Type == 'SubscriptionConfirmation';
    }

    /**
     * Log message
     *
     * @param $message
     */

    protected function logMessage($message): void
    {
        if ($this->debug()) {
            Log::debug(config('laravelses.log_prefix').": " . $message);
        }
    }

    /**
     * Debug mode on
     *
     * @param $result
     */

    protected function logResult($result): void
    {
        if ($this->debug()) {
            $this->logMessage('Result object:');
            Log::debug(print_r($result, true));
        }
    }

    /**
     * Check if debugging is turned on
     * 
     * @return bool
     */
    
    protected function debug(): bool
    {
        return config('laravelses.debug');
    }
}
