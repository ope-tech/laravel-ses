<?php

namespace Juhasev\LaravelSes\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        if ($this->debug()) return;

        $messageId = $this->parseMessageId($message);

        try {
            $sentEmail = ModelResolver::get('SentEmail')::whereMessageId($messageId)
                ->whereComplaintTracking(true)
                ->firstOrFail();

            ModelResolver::get('EmailComplaint')::create([
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
}
