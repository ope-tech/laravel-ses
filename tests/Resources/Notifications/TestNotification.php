<?php

namespace OpeTech\LaravelSes\Tests\Resources\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use OpeTech\LaravelSes\Notifications\MailMessageWithBatching;

class TestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
            'mail',
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessageWithBatching)
            ->from('hello@example.com')
            ->line('Test message')
            ->subject('Test subject')
            ->batch('test-batch');
    }
}
