<?php

namespace Juhasev\LaravelSes\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Juhasev\LaravelSes\ModelResolver;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

class DeliveryController extends BaseController
{
    /**
     * Delivery request from SNS
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
     * @throws \Exception
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
     * @throws \Exception
     */

    protected function persistDelivery(stdClass $message): void
    {
        if ($this->debug()) return;

        $messageId = $this->parseMessageId($message);

        $deliveryTime = Carbon::parse($message->delivery
            ->timestamp);

        try {
            $sentEmail = ModelResolver::get('SentEmail')::whereMessageId($messageId)
                ->whereDeliveryTracking(true)
                ->firstOrFail();

            $sentEmail->setDeliveredAt($deliveryTime);

        } catch (ModelNotFoundException $e) {
            Log::error('Could not find laravel_ses_sent_emails table. Did you run migrations?');
        }
    }
}
