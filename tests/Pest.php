<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use OpeTech\LaravelSes\Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)
    ->beforeEach(function () {
        Http::preventStrayRequests();
    })
    ->in('Unit', 'Feature');
