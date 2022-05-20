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

        if ($result === null) {
            Log::error('Failed to parse AWS SES Complaint request '. json_last_error_msg());
            return response()->json(['success' => false], 422);
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
            throw new Exception("Result message failed to decode: ".json_last_error_msg()."! ". print_r($result,true));
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
            $this->logMessage('Message ID ('.$messageId.') not found in the SentEmail, this email is likely sent without Laravel SES. Skipping delivery processing...');
            return;
        }

        $complaintFeedbackType = property_exists($message->complaint, 'complaintFeedbackType')
            ? $message->complaint->complaintFeedbackType
            : 'unknown';

        try {
            $emailComplaint = ModelResolver::get('EmailComplaint')::create([
                'sent_email_id' => $sentEmail->id,
                'type' => $complaintFeedbackType,
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
