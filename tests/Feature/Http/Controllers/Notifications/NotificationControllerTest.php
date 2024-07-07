<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Decorators\JobDecorator;
use OpeTech\LaravelSes\Actions\SesEvents\ResolveSesSnsEventClassName;
use OpeTech\LaravelSes\Enums\SesEvents;
use OpeTech\LaravelSes\Exceptions\LaravelSesSentEmailNotFoundException;
use OpeTech\LaravelSes\Models\LaravelSesEmailBounce;
use OpeTech\LaravelSes\Models\LaravelSesEmailClick;
use OpeTech\LaravelSes\Models\LaravelSesEmailComplaint;
use OpeTech\LaravelSes\Models\LaravelSesEmailDelivery;
use OpeTech\LaravelSes\Models\LaravelSesEmailOpen;
use OpeTech\LaravelSes\Models\LaravelSesEmailReject;
use OpeTech\LaravelSes\Models\LaravelSesSentEmail;

use function Pest\Laravel\withHeaders;

describe('persisting notifications', function () {

    it('persists the rejection in the database', function () {
        postNotification(SesEvents::Reject);

        expect(LaravelSesEmailReject::count())
            ->toBe(1);

        expect(LaravelSesEmailReject::first())->toMatchArray([
            'message_id' => 'EXAMPLE7c191be45-e9aedb9a-02f9-4d12-a87d-dd0099a07f8a-000000',
            'reason' => 'Bad content',
        ]);
    });

    it('persists the click in the database', function () {
        postNotification(SesEvents::Click);

        expect(LaravelSesEmailClick::count())
            ->toBe(1);

        expect(LaravelSesEmailClick::first())->toMatchArray([
            'clicked_at' => '2017-08-09T23:51:25.000000Z',
            'link' => 'http://docs.aws.amazon.com/ses/latest/DeveloperGuide/send-email-smtp.html',
            'sns_raw_data' => null,
            'message_id' => 'EXAMPLE7c191be45-e9aedb9a-02f9-4d12-a87d-dd0099a07f8a-000000',
            'link_tags' => [
                'samplekey0' => ['samplevalue0'],
                'samplekey1' => ['samplevalue1'],
            ],
        ]);
    });

    it('persists the bounce in the database', function () {
        postNotification(SesEvents::Bounce);

        expect(LaravelSesEmailBounce::count())
            ->toBe(1);

        expect(LaravelSesEmailBounce::first())->toMatchArray([
            'bounced_at' => '2016-01-27T14:59:38.000000Z',
            'type' => 'Permanent',
            'sns_raw_data' => null,
            'message_id' => '00000138111222aa-33322211-cccc-cccc-cccc-ddddaaaa0680-000000',
        ]);
    });

    it('persists the complaint to the database', function () {
        postNotification(SesEvents::Complaint);

        expect(LaravelSesEmailComplaint::count())
            ->toBe(1);

        expect(LaravelSesEmailComplaint::first())->toMatchArray([
            'complained_at' => '2017-08-05T00:41:02.000000Z',
            'type' => 'abuse',
            'sns_raw_data' => null,
            'message_id' => 'EXAMPLE7c191be45-e9aedb9a-02f9-4d12-a87d-dd0099a07f8a-000000',
        ]);
    });

    it('persists the complaint with sub type to the database', function () {
        postNotification(sesEvent: SesEvents::Complaint, variation: 'WithComplaintSubType');

        expect(LaravelSesEmailComplaint::count())
            ->toBe(1);

        expect(LaravelSesEmailComplaint::first())->toMatchArray([
            'complained_at' => '2017-08-05T00:41:02.000000Z',
            'type' => 'OnAccountSuppressionList',
            'sns_raw_data' => null,
            'message_id' => 'EXAMPLE7c191be45-e9aedb9a-02f9-4d12-a87d-dd0099a07f8a-000000',
        ]);
    });

    it('persists the open to the database', function () {
        postNotification(SesEvents::Open);

        expect(LaravelSesEmailOpen::count())
            ->toBe(1);

        expect(LaravelSesEmailOpen::first())->toMatchArray([
            'opened_at' => '2017-08-09T22:00:19.000000Z',
            'sns_raw_data' => null,
            'message_id' => 'EXAMPLE7c191be45-e9aedb9a-02f9-4d12-a87d-dd0099a07f8a-000000',
        ]);
    });

    it('persists the delivery to the database', function () {
        postNotification(SesEvents::Delivery);

        expect(LaravelSesEmailDelivery::count())
            ->toBe(1);

        expect(LaravelSesEmailDelivery::first())->toMatchArray([
            'delivered_at' => '2016-10-19T23:21:04.000000Z',
            'sns_raw_data' => null,
            'message_id' => 'EXAMPLE7c191be45-e9aedb9a-02f9-4d12-a87d-dd0099a07f8a-000000',
        ]);
    });

    it('persist action is dispatched on a custom queue if the setting is enabled', function (SesEvents $sesEvent) {
        Queue::fake();

        config(['laravelses.queues.sns_notifications' => 'custom-queue']);

        postNotification($sesEvent);

        $action = 'OpeTech\LaravelSes\Actions\SesEvents\Persist'.Str::ucfirst($sesEvent->value).'Notification';

        $action::assertPushedOn('custom-queue');

    })->with(SesEvents::cases());

    it('persist action is dispatched on the sync queue by default', function (SesEvents $sesEvent) {

        Queue::fake();

        postNotification($sesEvent);

        Queue::assertPushed(JobDecorator::class, function ($job) use ($sesEvent) {
            $action = 'OpeTech\LaravelSes\Actions\SesEvents\Persist'.Str::ucfirst($sesEvent->value).'Notification';

            return $job->connection == 'sync'
                && $job->queue == null
                && $job->decorates($action);
        });

    })->with(SesEvents::cases());

    it('dispatches the respective event', function (SesEvents $sesEvent) {
        Event::fake();
        postNotification($sesEvent);

        $event = ResolveSesSnsEventClassName::run($sesEvent);

        Event::assertDispatched($event, function ($event) use ($sesEvent) {
            $property = Str::lower($sesEvent->value);
            $messageId = getMessageIdFromSesEvent($sesEvent);

            return $event->{$property}->message_id == $messageId;
        });
    })->with(SesEvents::cases());

    it('persists raw sns message when the config option is on', function (SesEvents $sesEvent) {

        $configKey = 'laravelses.log_raw_data.'.Str::plural(Str::lower($sesEvent->value));

        $model = 'OpeTech\LaravelSes\Models\LaravelSesEmail'.ucfirst($sesEvent->value);

        config([$configKey => true]);

        postNotification($sesEvent);

        expect($model::first()->sns_raw_data)
            ->not->toBeNull();

    })->with(SesEvents::cases());

    it('throws an exception if it cannot find the sent email', function (SesEvents $sesEvent) {
        $messageId = getMessageIdFromSesEvent($sesEvent);

        //uses a different id for the sent message id.
        expect(fn () => postNotification(
            sesEvent: $sesEvent,
            messageId: '00000138111222aa-33322211-cccc-cccc-cccc-ddddaaaa0680404'
        ))->toThrow(
            LaravelSesSentEmailNotFoundException::class,
            'Sent Email with message id: '.$messageId
        );
    })->with(SesEvents::cases());

});

describe('subscription confirmation', function () {

    beforeEach(function () {
        Http::fake();
        postSubscriptionConfirmation();
    });

    it('returns the correct response', function () {

        expect($this->response->status())
            ->toBe(200);

        expect($this->response->json())
            ->message
            ->toBe('Subscription Confirmed.');
    });

    it('hits the subscription confirmation endpoint', function () {
        Http::assertSent(function ($request) {
            $data = json_decode(file_get_contents(__DIR__.'/../../../../Resources/Sns/SnsConfirmationExample.json'), true);

            return $request->url() == $data['SubscribeURL'];
        });
    });
});

function postSubscriptionConfirmation()
{
    $rawContent = file_get_contents(__DIR__.'/../../../../Resources/Sns/SnsConfirmationExample.json');

    test()->response = withHeaders([
        'x-amz-sns-message-type' => 'SubscriptionConfirmation',
    ])
        ->call(
            method: 'post',
            uri: '/laravel-ses/sns-notification',
            content: $rawContent,
        );
}

function postNotification(SesEvents $sesEvent, ?string $messageId = null, ?string $variation = null)
{
    $rawContent = rawSesEventContent($sesEvent, $variation);
    $messageId = $messageId ?? getMessageIdFromSesEvent($sesEvent);

    LaravelSesSentEmail::factory()->create([
        'email' => 'recipient@example.com',
        'message_id' => $messageId,
    ]);

    test()->response = test()->call(
        method: 'post',
        uri: '/laravel-ses/sns-notification',
        content: $rawContent,
        server: [
            'HTTP_X_AMZ_SNS_MESSAGE_TYPE' => 'Notification',
        ]
    );
}

function getMessageIdFromSesEvent(SesEvents $sesEvent)
{
    $rawContent = rawSesEventContent($sesEvent);

    $messageId = $messageId ?? json_decode(json_decode($rawContent)->Message)->mail->messageId;

    return $messageId;
}

function rawSesEventContent(SesEvents $sesEvent, ?string $variation = null)
{
    $rawContent = file_get_contents(__DIR__.'/../../../../Resources/Sns/Sns'.$sesEvent->value.($variation ? $variation : '').'Example.json');

    return $rawContent;
}
