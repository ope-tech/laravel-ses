<?php

namespace OpeTech\LaravelSes\Actions\SesEvents;

use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\JobDecorator;

class ConfigureJob
{
    use AsAction;

    public function handle(JobDecorator $job): void
    {
        $snsNotificationsQueue = config('laravelses.queues.sns_notifications');

        if (! $snsNotificationsQueue) {
            $job->onConnection('sync');

            return;
        }

        $job
            ->onConnection(config('laravelses.queue_connection'))
            ->onQueue($snsNotificationsQueue);

    }
}
