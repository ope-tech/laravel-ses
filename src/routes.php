<?php


use Illuminate\Support\Facades\Route;

Route::prefix('/laravel-ses')->group(function () {
    Route::post('notification/bounce', 'oliveready7\LaravelSes\Controllers\BounceController@bounce');
    Route::post('notification/delivery', 'oliveready7\LaravelSes\Controllers\DeliveryController@delivery');
    Route::post('notification/complaint', 'oliveready7\LaravelSes\Controllers\ComplaintController@complaint');

    Route::get('beacon/{beaconIdentifier}', 'oliveready7\LaravelSes\Controllers\OpenController@open');
    Route::get('link/{linkId}', 'oliveready7\LaravelSes\Controllers\LinkController@click');

    Route::get('api/has/bounced/{email}', 'oliveready7\LaravelSes\Controllers\BounceController@hasBounced');
    Route::get('api/has/complained/{email}', 'oliveready7\LaravelSes\Controllers\ComplaintController@hasComplained');
});
