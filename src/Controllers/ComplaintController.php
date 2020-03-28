<?php

namespace Juhasev\LaravelSes\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Juhasev\LaravelSes\Models\EmailComplaint;
use Juhasev\LaravelSes\Models\SentEmail;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

class ComplaintController extends BaseController
{
    /**
     * Complaint from SNS
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
     */
    public function complaint(ServerRequestInterface $request)
    {
        $debug = config('laravelses.debug');

        $this->validateSns($request);

        $result = json_decode(request()->getContent());

        if (!$result) {
            Log::warning("Request contained no JSON");
            return response()->json(['success' => true]);
        }

        if ($debug) $this->logResult($result);

        //if amazon is trying to confirm the subscription
        if (isset($result->Type) && $result->Type == 'SubscriptionConfirmation') {

            // TODO No error checking
            $client = new Client;
            $client->get($result->SubscribeURL);

            if ($debug) Log::info("Subscribed to: " . $result->TopicArn);

            return response()->json([
                'success' => true,
                'message' => 'Subscription confirmed'
            ]);
        }

        if ($debug) $this->logResult($result);

        // TODO: This can fail
        $message = json_decode($result->Message);

        if (!$debug) $this->persistComplaint($message);

        if ($debug) Log::info("Complaint processed for: " . $message->mail->destination[0]);
    }

    /**
     * Persist complaint to the database
     *
     * @param $message
     */

    private function persistComplaint($message)
    {
        $messageId = $this->parseMessageId($message);

        try {
            $sentEmail = SentEmail::whereMessageId($messageId)
                ->whereComplaintTracking(true)
                ->firstOrFail();

            EmailComplaint::create([
                'message_id' => $messageId,
                'sent_email_id' => $sentEmail->id,
                'type' => $message->complaint->complaintFeedbackType,
                'email' => $message->mail->destination[0],
                'complained_at' => Carbon::parse($message->mail->timestamp)
            ]);
        } catch (ModelNotFoundException $e) {

            Log::error('Could not find laravel_ses_email_complaints table. Did you run migrations?');
        }
    }

    /**
     * Parse message ID out of message
     *
     * @param stdClass $message
     * @return string
     */

    private function parseMessageId(stdClass $message): string
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
     * Debug mode on
     *
     * @param $result
     */

    private function logResult($result)
    {
        Log::debug("COMPLAINT REQUEST");
        Log::debug(print_r($result, true));
    }
}
