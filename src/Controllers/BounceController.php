<?php

namespace Juhasev\LaravelSes\Controllers;

use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Juhasev\LaravelSes\Contracts\EmailBounceContract;
use Juhasev\LaravelSes\Factories\EventFactory;
use Juhasev\LaravelSes\ModelResolver;
use Psr\Http\Message\ServerRequestInterface;

class BounceController extends BaseController
{
    /**
     * Bounce controller
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
     * @throws Exception
     */

    public function bounce(ServerRequestInterface $request)
    {
        $this->validateSns($request);

        $content = request()->getContent();

        $this->logResult($content);

        $result = json_decode($content);

        if ($this->isSubscriptionConfirmation($result)) {

            $this->confirmSubscription($result);

            return response()->json([
                'success' => true,
                'message' => 'Bounce subscription confirmed'
            ]);
        }

        $message = json_decode($result->Message);
        
        $this->persistBounce($message);

        $this->logMessage("Bounce processed for: " . $message->mail->destination[0]);

        return response()->json([
            'success' => true,
            'message' => 'Bounce processed'
        ]);
    }

    /**
     * Persis bounce
     *
     * @param $message
     * @throws Exception
     */

    protected function persistBounce($message): void
    {
        $messageId = $this->parseMessageId($message);

        try {
            $sentEmail = ModelResolver::get('SentEmail')::whereMessageId($messageId)
                ->whereBounceTracking(true)
                ->firstOrFail();

        } catch (ModelNotFoundException $e) {
            Log::error("Could not find sent email ($messageId). Email bounce failed to record!");
            return;
        }

        try {
            $bounce = ModelResolver::get('EmailBounce')::create([
                'sent_email_id' => $sentEmail->id,
                'type' => $message->bounce->bounceType,
                'bounced_at' => Carbon::parse($message->mail->timestamp)
            ]);

            $this->sendEvent($bounce);

        } catch (QueryException $e) {
            Log::error("Failed inserting EmailBounce, got error: " . $e->getMessage());
        }
    }

    /**
     * Sent event to listeners
     *
     * @param EmailBounceContract $bounce
     */

    protected function sendEvent(EmailBounceContract $bounce)
    {
        event(EventFactory::create('Bounce', 'EmailBounce', $bounce->id));
    }
}
