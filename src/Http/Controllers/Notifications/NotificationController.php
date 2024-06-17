<?php

namespace OpeTech\LaravelSes\Http\Controllers\Notifications;

use Aws\Sns\Message;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use OpeTech\LaravelSes\Actions\SesEvents\PersistBounceNotification;
use OpeTech\LaravelSes\Actions\SesEvents\PersistClickNotification;
use OpeTech\LaravelSes\Actions\SesEvents\PersistComplaintNotification;
use OpeTech\LaravelSes\Actions\SesEvents\PersistDeliveryNotification;
use OpeTech\LaravelSes\Actions\SesEvents\PersistOpenNotification;
use OpeTech\LaravelSes\Actions\SesEvents\PersistRejectNotification;
use OpeTech\LaravelSes\Actions\Sns\ConfirmSubscription;
use OpeTech\LaravelSes\Enums\SesEvents;

class NotificationController extends Controller
{
    public function notification(Request $request)
    {
        Log::info($request->getContent());

        $content = json_decode($request->getContent(), true);

        if ($content['Type'] == 'Notification') {
            $content['Message'] = json_decode($content['Message'], true);
        }

        $snsMessage = new Message($content);

        if ($snsMessage['Type'] == 'SubscriptionConfirmation') {
            return $this->confirmSubscription($snsMessage);
        }

        $this->persistNotification($snsMessage);

        return response()->json([
            'message' => 'Success.',
        ]);
    }

    protected function confirmSubscription(Message $message)
    {
        ConfirmSubscription::run($message);

        return response()->json([
            'message' => 'Subscription Confirmed.',
        ]);
    }

    protected function persistNotification(Message $message)
    {
        $notificationType = $message['Message']['eventType'];

        match ($notificationType) {
            SesEvents::Bounce->value => PersistBounceNotification::dispatch($message),
            SesEvents::Complaint->value => PersistComplaintNotification::dispatch($message),
            SesEvents::Open->value => PersistOpenNotification::dispatch($message),
            SesEvents::Delivery->value => PersistDeliveryNotification::dispatch($message),
            SesEvents::Click->value => PersistClickNotification::dispatch($message),
            SesEvents::Reject->value => PersistRejectNotification::dispatch($message),
            default => null,
        };
    }
}
