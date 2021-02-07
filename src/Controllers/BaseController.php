<?php

namespace Juhasev\LaravelSes\Controllers;

use Aws\Sns\Exception\InvalidSnsMessageException;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use GuzzleHttp\Client;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Http\Message\ServerRequestInterface;

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
     * @param $message
     * @return string
     */

    protected function parseMessageId($message): string
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
     * @param $result
     * @return void
     */

    protected function confirmSubscription($result): void
    {
        $client = new Client;
        $client->get($result->SubscribeURL);

        $this->logMessage("Subscribed to: " . $result->TopicArn);
    }

    /**
     * If AWS is trying to confirm subscription
     *
     * @param $result
     * @return bool
     */

    protected function isSubscriptionConfirmation($result): bool
    {
        return isset($result->Type) && $result->Type == 'SubscriptionConfirmation';
    }

    /**
     * Is topic confirmation
     *
     * @param $result
     * @return bool
     */
    protected function isTopicConfirmation($result): bool
    {
        return isset($result->Type) &&
            $result->Type == 'Notification' &&
            Str::contains($result->Message, "Successfully validated SNS topic");
    }

    /**
     * Log message
     *
     * @param $message
     */

    protected function logMessage($message): void
    {
        if ($this->debug()) {
            Log::debug(config('laravelses.log_prefix') . ": " . $message);
        }
    }

    /**
     * Debug mode on
     *
     * @param string $content
     */

    protected function logResult(string $content): void
    {
        if ($this->debug()) {
            Log::debug("RAW SES REQUEST BODY:\n" . $content);
        }
    }

    /**
     * Check if debugging is turned on
     *
     * @return bool
     */

    protected function debug(): bool
    {
        return config('laravelses.debug') === true;
    }
}
