<?php

namespace Juhasev\LaravelSes\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Juhasev\LaravelSes\Models\EmailComplaint;
use Juhasev\LaravelSes\Models\SentEmail;
use Psr\Http\Message\ServerRequestInterface;

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
        $this->validateSns($request);

        $result = json_decode(request()->getContent());

        //if amazon is trying to confirm the subscription
        if (isset($result->Type) && $result->Type == 'SubscriptionConfirmation') {
            $client = new Client;
            $client->get($result->SubscribeURL);

            return response()->json(['success' => true]);
        }

        $message = json_decode($result->Message);

        $messageId = $message
            ->mail
            ->commonHeaders
            ->messageId;

        $messageId  = str_replace('<', '', $messageId);
        $messageId = str_replace('>', '', $messageId);

        try {
            $sentEmail = SentEmail::whereMessageId($messageId)
                ->whereComplaintTracking(true)
                ->firstOrFail();

            EmailComplaint::create([
                'message_id' => $messageId,
                'sent_email_id' => $sentEmail->id,
                'type' => $message->complaint->complaintFeedbackType,
                'email' => $message->mail->destination[0],
                'complained_at' =>  Carbon::parse($message->mail->timestamp)
            ]);
        } catch (ModelNotFoundException $e) {
        }
    }
}
