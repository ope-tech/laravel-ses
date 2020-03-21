<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/laravel-ses')->group(function () {
    //receive SNS notifications
    Route::post('notification/bounce', 'Juhasev\LaravelSes\Controllers\BounceController@bounce');
    Route::post('notification/delivery', 'Juhasev\LaravelSes\Controllers\DeliveryController@delivery');
    Route::post('notification/complaint', 'Juhasev\LaravelSes\Controllers\ComplaintController@complaint');

    //user tracking
    Route::get('beacon/{beaconIdentifier}', 'Juhasev\LaravelSes\Controllers\OpenController@open');
    Route::get('link/{linkId}', 'Juhasev\LaravelSes\Controllers\LinkController@click');

    //package api
    Route::get('api/has/bounced/{email}', 'Juhasev\LaravelSes\Controllers\BounceController@hasBounced');
    Route::get('api/has/complained/{email}', 'Juhasev\LaravelSes\Controllers\ComplaintController@hasComplained');
    Route::get('api/stats/batch/{name}', 'Juhasev\LaravelSes\Controllers\StatsController@statsForBatch');
    Route::get('api/stats/email/{email}', 'Juhasev\LaravelSes\Controllers\StatsController@statsForEmail');
});
