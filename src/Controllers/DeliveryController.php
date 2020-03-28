<?php

namespace Juhasev\LaravelSes\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ServerRequestInterface;
use Juhasev\LaravelSes\Models\SentEmail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use GuzzleHttp\Client;
use stdClass;

class DeliveryController extends BaseController
{
    /**
     * Delivery request from SNS
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
     */

    public function delivery(ServerRequestInterface $request)
    {
        $this->validateSns($request);

        $result = json_decode(request()->getContent());

        $this->logResult($request);

        if ($this->isSubscriptionConfirmation($result)) {

            $this->confirmSubscription($result);

            return response()->json([
                'success' => true,
                'message' => 'Delivery subscription confirmed'
            ]);
        }

        // TODO: This can fail
        $message = json_decode($result->Message);

        $this->persistDelivery($message);

        $this->logMessage("Complaint processed for: " . $message->mail->destination[0]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery notification processed'
        ]);
    }

    /**
     * Persist delivery record to the database
     *
     * @param stdClass $message
     */

    protected function persistDelivery(stdClass $message): void
    {
        if ($this->debug()) {
            Log::debug("Skipped persisting delivery");
            return;
        }

        Log::debug("Persisting delivery");

        $messageId = $this->parseMessageId($message);

        $deliveryTime = Carbon::parse($message->delivery
            ->timestamp);

        try {
            $sentEmail = SentEmail::whereMessageId($messageId)
                ->whereDeliveryTracking(true)
                ->firstOrFail();

            $sentEmail->setDeliveredAt($deliveryTime);

        } catch (ModelNotFoundException $e) {
            Log::error('Could not find laravel_ses_email_complaints table. Did you run migrations?');
        }
    }
}
