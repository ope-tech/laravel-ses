<?php

namespace Juhasev\LaravelSes\Controllers;

use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Juhasev\LaravelSes\Contracts\EmailComplaintContract;
use Juhasev\LaravelSes\Factories\EventFactory;
use Juhasev\LaravelSes\ModelResolver;
use Psr\Http\Message\ServerRequestInterface;

class ComplaintController extends BaseController
{
    /**
     * Complaint from SNS
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
     * @throws Exception
     */

    public function complaint(ServerRequestInterface $request)
    {
        $this->validateSns($request);

        $content = request()->getContent();

        $this->logResult($content);

        $result = json_decode($content);

        if (!$result) {
            Log::warning("Request contained no JSON");
            return response()->json(['success' => true]);
        }

        if ($this->isTopicConfirmation($result)) {
            return response()->json(['success' => true]);
        }

        if ($this->isSubscriptionConfirmation($result)) {

            $this->confirmSubscription($result);

            return response()->json([
                'success' => true,
                'message' => 'Complaint subscription confirmed'
            ]);
        }

        $message = json_decode($result->Message);

        if ($message === null) {
            throw new Exception('Result message failed to decode! '. print_r($result,true));
        }

        $this->persistComplaint($message);

        $this->logMessage("Complaint processed for: " . $message->mail->destination[0]);

        return response()->json([
            'success' => true,
            'message' => 'Complaint processed'
        ]);
    }

    /**
     * Persist complaint to the database
     *
     * @param $message
     * @throws Exception
     */

    private function persistComplaint($message)
    {
        $messageId = $this->parseMessageId($message);

        try {
            $sentEmail = ModelResolver::get('SentEmail')::whereMessageId($messageId)
                ->whereComplaintTracking(true)
                ->firstOrFail();

        } catch (ModelNotFoundException $e) {
            Log::error("Could not find sent email ($messageId). Email complaint failed to record!");
            return;
        }

        try {
            $emailComplaint = ModelResolver::get('EmailComplaint')::create([
                'sent_email_id' => $sentEmail->id,
                'type' => $message->complaint->complaintFeedbackType,
                'complained_at' => Carbon::parse($message->mail->timestamp)
            ]);

            $this->sendEvent($emailComplaint);

        } catch (QueryException $e) {
            Log::error("Failed inserting EmailComplaint, got error: " . $e->getMessage());
        }
    }

    /**
     * Sent event to listeners
     *
     * @param EmailComplaintContract $complaint
     */

    protected function sendEvent(EmailComplaintContract $complaint)
    {
        event(EventFactory::create('Complaint', 'EmailComplaint', $complaint->id));
    }
}
