<?php

namespace Juhasev\LaravelSes\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Juhasev\LaravelSes\Contracts\SentEmailContract;
use Juhasev\LaravelSes\Factories\EventFactory;
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
        $messageId = $this->parseMessageId($message);

        $deliveryTime = Carbon::parse($message->delivery
            ->timestamp);

        try {
            $sentEmail = ModelResolver::get('SentEmail')::whereMessageId($messageId)
                ->whereDeliveryTracking(true)
                ->firstOrFail();

        } catch (ModelNotFoundException $e) {
            Log::error("Could not find sent email ($messageId). Email delivery failed to record!");
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
