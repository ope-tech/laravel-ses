<?php


use Illuminate\Support\Facades\Route;

Route::prefix('/laravel-ses')->group(function () {
    //receive SNS notifications
    Route::post('notification/bounce', 'oliveready7\LaravelSes\Controllers\BounceController@bounce');
    Route::post('notification/delivery', 'oliveready7\LaravelSes\Controllers\DeliveryController@delivery');
    Route::post('notification/complaint', 'oliveready7\LaravelSes\Controllers\ComplaintController@complaint');

    //user tracking
    Route::get('beacon/{beaconIdentifier}', 'oliveready7\LaravelSes\Controllers\OpenController@open');
    Route::get('link/{linkId}', 'oliveready7\LaravelSes\Controllers\LinkController@click');

    //package api
    Route::get('api/has/bounced/{email}', 'oliveready7\LaravelSes\Controllers\BounceController@hasBounced');
    Route::get('api/has/complained/{email}', 'oliveready7\LaravelSes\Controllers\ComplaintController@hasComplained');
    Route::get('api/stats/batch/{name}', 'oliveready7\LaravelSes\Controllers\StatsController@statsForBatch');
    Route::get('api/stats/email/{email}', 'oliveready7\LaravelSes\Controllers\StatsController@statsForEmail');
});
