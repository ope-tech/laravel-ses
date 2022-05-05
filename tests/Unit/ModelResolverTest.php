<?php

namespace Juhasev\LaravelSes\Tests\Unit;

use Exception;
use Juhasev\LaravelSes\ModelResolver;
use Juhasev\LaravelSes\Models\Batch;
use Juhasev\LaravelSes\Models\EmailBounce;
use Juhasev\LaravelSes\Models\EmailComplaint;
use Juhasev\LaravelSes\Models\EmailLink;
use Juhasev\LaravelSes\Models\EmailOpen;
use Juhasev\LaravelSes\Models\SentEmail;
use Juhasev\LaravelSes\Tests\UnitTestCase;

class ModelResolverTest extends UnitTestCase
{
    public function testModelResolverThrowsExceptionForInvalidName(): void
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('Model (invalid-name) could not be resolved');

        ModelResolver::get('invalid-name');
    }

    /**
     * @dataProvider modelResolverData
     */
    public function testModelResolverGet($name, $expected): void
    {
        self::assertEquals($expected, ModelResolver::get($name));
    }

    public function modelResolverData(): array
    {
        return [
            ['Batch', Batch::class],
            ['SentEmail', SentEmail::class],
            ['EmailBounce', EmailBounce::class],
            ['EmailComplaint', EmailComplaint::class],
            ['EmailLink', EmailLink::class],
            ['EmailOpen', EmailOpen::class],
        ];
    }
}