<?php

use Aws\Result;
use Aws\SesV2\SesV2Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use OpeTech\LaravelSes\Models\LaravelSesBatch;
use OpeTech\LaravelSes\Models\LaravelSesSentEmail;
use OpeTech\LaravelSes\Tests\Resources\Mailables\TestMailable;
use OpeTech\LaravelSes\Tests\Resources\Mailables\TestMailableWithBatching;
use OpeTech\LaravelSes\Tests\Resources\Notifications\TestNotifiable;
use OpeTech\LaravelSes\Tests\Resources\Notifications\TestNotification;
use OpeTech\LaravelSes\Transport\LaravelSesTransport;

it('it set up batching correctly when using withBatch method', function () {

    $mock = Mockery::mock(SesV2Client::class);

    $mock->shouldReceive('sendEmail')->andReturn(new Result(['MessageId' => '123']));

    //overwrite the mailer with mock implementation.
    Mail::mailer('laravel-ses')->setSymfonyTransport(new LaravelSesTransport($mock));

    $mock->shouldReceive('sendEmail')->andReturn(new Result(['MessageId' => '123']));

    Mail::mailer('laravel-ses')
        ->withBatch('test-batch')
        ->to('example@example.com')
        ->send(new TestMailable);

    expect(LaravelSesBatch::get())
        ->toHaveCount(1)
        ->first()
        ->toMatchArray([
            'name' => 'test-batch',
        ]);

    expect(LaravelSesSentEmail::get())
        ->toHaveCount(1)
        ->first()
        ->toMatchArray([
            'batch_id' => LaravelSesBatch::first()->id,
            'email' => 'example@example.com',
        ]);

    Mail::mailer('laravel-ses')
        ->to('example@example.com')
        ->send(new TestMailable);

});

it('it sets up batching correctly when mailable implements batching', function () {
    $mock = Mockery::mock(SesV2Client::class);
    $mock->shouldReceive('sendEmail')->andReturn(new Result(['MessageId' => '123']));

    //overwrite the mailer with mock implementation.
    Mail::mailer('laravel-ses')->setSymfonyTransport(new LaravelSesTransport($mock));

    Mail::mailer('laravel-ses')
        ->to('example@example.com')
        ->send(new TestMailableWithBatching);

    expect(LaravelSesBatch::get())
        ->toHaveCount(1)
        ->first()
        ->toMatchArray([
            'name' => 'test-batch',
        ]);

    expect(LaravelSesSentEmail::get())
        ->toHaveCount(1)
        ->first()
        ->toMatchArray([
            'batch_id' => LaravelSesBatch::first()->id,
            'email' => 'example@example.com',
        ]);
});

it('it sets up batching correctly when mailable implements batching using sendNow', function () {
    $mock = Mockery::mock(SesV2Client::class);
    $mock->shouldReceive('sendEmail')->andReturn(new Result(['MessageId' => '123']));

    //overwrite the mailer with mock implementation.
    Mail::mailer('laravel-ses')->setSymfonyTransport(new LaravelSesTransport($mock));

    Mail::mailer('laravel-ses')
        ->to('example@example.com')
        ->sendNow(new TestMailableWithBatching);

    expect(LaravelSesBatch::get())
        ->toHaveCount(1)
        ->first()
        ->toMatchArray([
            'name' => 'test-batch',
        ]);

    expect(LaravelSesSentEmail::get())
        ->toHaveCount(1)
        ->first()
        ->toMatchArray([
            'batch_id' => LaravelSesBatch::first()->id,
            'email' => 'example@example.com',
        ]);
})->skip(fn () => app()->version() < '11.0'); //sendNow is only available in Laravel 11.0+

it('it sets up batching correctly when notification implements batching', function () {
    $mock = Mockery::mock(SesV2Client::class);
    $mock->shouldReceive('sendEmail')->andReturn(new Result(['MessageId' => '123']));
    //overwrite the mailer with mock implementation.
    Mail::mailer('laravel-ses')->setSymfonyTransport(new LaravelSesTransport($mock));

    $notifiable = new TestNotifiable('test@test.com');

    Notification::sendNow($notifiable, new TestNotification());

    expect(LaravelSesBatch::get())
        ->toHaveCount(1)
        ->first()
        ->toMatchArray([
            'name' => 'test-batch',
        ]);

    expect(LaravelSesSentEmail::get())
        ->toHaveCount(1)
        ->first()
        ->toMatchArray([
            'batch_id' => LaravelSesBatch::first()->id,
            'email' => 'test@test.com',
        ]);
});

it('logs a sent email', function () {
    $mock = Mockery::mock(SesV2Client::class);
    $mock->shouldReceive('sendEmail')->andReturn(new Result(['MessageId' => '123']));
    //overwrite the mailer with mock implementation.
    Mail::mailer('laravel-ses')->setSymfonyTransport(new LaravelSesTransport($mock));

    Mail::mailer('laravel-ses')
        ->to('example@example.com')
        ->send(new TestMailable());

    expect(LaravelSesSentEmail::count())->toBe(1);

    expect(LaravelSesSentEmail::first())->toMatchArray([
        'email' => 'example@example.com',
    ]);

});
