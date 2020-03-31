<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/laravel-ses')->group(function () {

    //receive SNS notifications
    Route::post('notification/bounce', 'Juhasev\LaravelSes\Controllers\BounceController@bounce');
    Route::post('notification/delivery', 'Juhasev\LaravelSes\Controllers\DeliveryController@delivery');
    Route::post('notification/complaint', 'Juhasev\LaravelSes\Controllers\ComplaintController@complaint');
    Route::post('notification/reject', 'Juhasev\LaravelSes\Controllers\RejectController@reject');

    //user tracking
    Route::get('beacon/{beaconIdentifier}', 'Juhasev\LaravelSes\Controllers\OpenController@open');
    Route::get('link/{linkId}', 'Juhasev\LaravelSes\Controllers\LinkController@click');
});
