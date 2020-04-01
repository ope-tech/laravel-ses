<?php

namespace Juhasev\LaravelSes\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Juhasev\LaravelSes\Contracts\EmailRejectContract;
use Juhasev\LaravelSes\Factories\EventFactory;
use Juhasev\LaravelSes\ModelResolver;
use Psr\Http\Message\ServerRequestInterface;

class RejectController extends BaseController
{
    /**
     * Reject controller
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
     * @throws Exception
     */

    public function reject(ServerRequestInterface $request)
    {
        $this->validateSns($request);

        $result = json_decode(request()->getContent());

        $this->logResult($request);

        if ($this->isSubscriptionConfirmation($result)) {

            $this->confirmSubscription($result);

            return response()->json([
                'success' => true,
                'message' => 'Reject subscription confirmed'
            ]);
        }

        $message = json_decode($result->Message);

        $this->persistReject($message);

        $this->logMessage("Reject processed for: " . $message->mail->destination[0]);

        return response()->json([
            'success' => true,
            'message' => 'Reject processed'
        ]);
    }

    /**
     * Persis bounce
     *
     * @param $message
     * @throws Exception
     */

    protected function persistReject($message): void
    {
        $messageId = $this->parseMessageId($message);

        try {
            $sentEmail = ModelResolver::get('SentEmail')::where([
                ['message_id', $messageId],
                ['reject_tracking', true]
            ])->firstOrFail();

        } catch (ModelNotFoundException $e) {
            Log::error("Could not find sent email ($messageId). Email reject failed to record!");
            return;
        }

        try {
            $emailReject = ModelResolver::get('EmailReject')::create([
                'sent_email_id' => $sentEmail->id,
                'type' => 'Reject',
                'rejected_at' => Carbon::parse($message->mail->timestamp)
            ]);

            $this->sendEvent($emailReject);

        } catch (QueryException $e) {
            echo $e->getMessage();
            Log::error("Failed insert reject, got error: ".$e->getMessage());
        }
    }

    /**
     * Sent event to listeners
     *
     * @param EmailRejectContract $emailReject
     */

    protected function sendEvent(EmailRejectContract $emailReject)
    {
        event(EventFactory::create('Reject', 'EmailReject', $emailReject->id));
    }
}
