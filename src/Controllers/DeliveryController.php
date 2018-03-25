<?php
namespace oliveready7\LaravelSes\Controllers;

use Illuminate\Http\Request;
use Psr\Http\Message\ServerRequestInterface;
use oliveready7\LaravelSes\Models\SentEmail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use GuzzleHttp\Client;

class DeliveryController extends BaseController
{
    public function delivery(ServerRequestInterface $request)
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

        $deliveryTime =  Carbon::parse($message->delivery
            ->timestamp);

        try {
            $sentEmail = SentEmail::whereMessageId($messageId)
                ->whereDeliveryTracking(true)
                ->firstOrFail();
            $sentEmail->setDeliveredAt($deliveryTime);
        } catch (ModelNotFoundException $e) {
            //delivery won't be logged if this hits
        }
    }
}
