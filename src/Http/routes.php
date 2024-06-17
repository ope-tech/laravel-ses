<?php

use Illuminate\Support\Facades\Route;
use OpeTech\LaravelSes\Http\Controllers\Notifications\NotificationController;

Route::post('/laravel-ses/sns-notification', [NotificationController::class, 'notification']);
