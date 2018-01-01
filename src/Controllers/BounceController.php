<?php
namespace oliveready7\LaravelSes\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use oliveready7\LaravelSes\Models\SentEmail;
use oliveready7\LaravelSes\Models\EmailBounce;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use GuzzleHttp\Client;


class BounceController extends Controller {
    public function bounce() {
        $result = json_decode(request()->getContent());

        //if amazon is trying to confirm the subscription
        if(isset($result->Type) && $result->Type == 'SubscriptionConfirmation') {
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
        }catch(ModelNotFoundException $e) {
            //bounce won't be logged if this is hit
        }

    }

}
