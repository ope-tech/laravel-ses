<?php
namespace Juhasev\LaravelSes\Controllers;

use Illuminate\Http\Request;
use Psr\Http\Message\ServerRequestInterface;
use Juhasev\LaravelSes\Models\SentEmail;
use Juhasev\LaravelSes\Models\EmailBounce;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use GuzzleHttp\Client;

class BounceController extends BaseController
{
    public function hasBounced($email)
    {
        $emailBounces =  EmailBounce::whereEmail($email)->get();

        if ($emailBounces->isEmpty()) {
            return response()->json([
                'success' => true,
                'bounced' => false
            ]);
        }

        return response()->json([
            'success' => true,
            'bounced' => true,
            'bounces' => $emailBounces
        ]);
    }


    public function bounce(ServerRequestInterface $request)
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
                ->whereBounceTracking(true)
                ->firstOrFail();
            EmailBounce::create([
                'message_id' => $messageId,
                'sent_email_id' => $sentEmail->id,
                'type' => $message->bounce->bounceType,
                'email' => $message->mail->destination[0],
                'bounced_at' => Carbon::parse($message->mail->timestamp)
            ]);
        } catch (ModelNotFoundException $e) {
            //bounce won't be logged if this is hit
        }
    }
}
