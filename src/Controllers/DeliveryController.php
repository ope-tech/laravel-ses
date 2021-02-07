<?php

namespace Juhasev\LaravelSes\Controllers;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Juhasev\LaravelSes\Factories\EventFactory;
use Juhasev\LaravelSes\ModelResolver;
use Psr\Http\Message\ServerRequestInterface;

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

        $content = request()->getContent();

        $this->logResult($content);

        $result = json_decode($content);

        if ($this->isSubscriptionConfirmation($result)) {

            $this->confirmSubscription($result);

            return response()->json([
                'success' => true,
                'message' => 'Delivery subscription confirmed'
            ]);
        }

        $message = json_decode($result->Message);

        if ($message === null) {
            throw new Exception('Result message failed to decode! '. print_r($result,true));
        }

        $this->persistDelivery($message);

        $this->logMessage("Delivery processed for: " . $message->mail->destination[0]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery notification processed'
        ]);
    }

    /**
     * Persist delivery record to the database
     *
     * @param $message
     * @throws \Exception
     */

    protected function persistDelivery($message): void
    {
        $messageId = $this->parseMessageId($message);

        $deliveryTime = Carbon::parse($message->delivery
            ->timestamp);

        try {
            $sentEmail = ModelResolver::get('SentEmail')::whereMessageId($messageId)
                ->whereDeliveryTracking(true)
                ->firstOrFail();

        } catch (ModelNotFoundException $e) {

            // If sent email is not found then email delivery
            // was likely done without using Laravel-SES
            // We simply ignore these and do nothing with them.

            return;
        }

        try {
            $sentEmail->setDeliveredAt($deliveryTime);

        } catch (QueryException $e) {
            Log::error("Failed updating delivered timestamp, got error: " . $e->getMessage());
        }

        $this->sendEvent($sentEmail);
    }

    /**
     * Sent event to listeners
     *
     * @param SentEmailContract $sentEmail
     */

    protected function sendEvent(SentEmailContract $sentEmail)
    {
        event(EventFactory::create('Delivery', 'SentEmail', $sentEmail->id));
    }
}
