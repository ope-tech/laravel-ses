<?php

namespace Juhasev\LaravelSes\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Juhasev\LaravelSes\ModelResolver;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

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

        $result = json_decode(request()->getContent());

        if (!$result) {
            Log::warning("Request contained no JSON");
            return response()->json(['success' => true]);
        }

        $this->logResult($result);

        if ($this->isSubscriptionConfirmation($result)) {

            $this->confirmSubscription($result);

            return response()->json([
                'success' => true,
                'message' => 'Complaint subscription confirmed'
            ]);
        }

        $this->logResult($result);

        // TODO: This can fail
        $message = json_decode($result->Message);

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
     * @param stdClass $message
     * @throws Exception
     */

    private function persistComplaint(stdClass $message)
    {
        $messageId = $this->parseMessageId($message);

        try {
            $sentEmail = ModelResolver::get('SentEmail')::whereMessageId($messageId)
                ->whereComplaintTracking(true)
                ->firstOrFail();

        } catch (ModelNotFoundException $e) {
            Log::error("Could not find sent email ($messageId). Email complaint failed to record!");
        }

        try {
            ModelResolver::get('EmailComplaint')::create([
                'sent_email_id' => $sentEmail->id,
                'type' => $message->complaint->complaintFeedbackType,
                'complained_at' => Carbon::parse($message->mail->timestamp)
            ]);

        } catch (QueryException $e) {
            Log::error("Failed inserting EmailComplaint, got error: " . $e->getMessage());
        }
    }
}
