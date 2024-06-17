<?php

namespace OpeTech\LaravelSes\Tests\Resources\Mailables;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use OpeTech\LaravelSes\Contracts\Batchable;
use OpeTech\LaravelSes\Mailables\Batching;

class TestMailableWithBatching extends Mailable implements Batchable
{
    use Batching;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Mailable',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.simple-email',
        );
    }

    public function getBatch(): string
    {
        return 'test-batch';
    }
}
