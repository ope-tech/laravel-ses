<?php

namespace Juhasev\LaravelSes\Factories;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class EventFactory
{
    /**
     * Create processor class
     *
     * @param string $eventName
     * @param string $modelName
     * @param int $modelId
     * @return EventInterface
     */
    public static function create(string $eventName, string $modelName, int $modelId): EventInterface
    {
        $class = 'Juhasev\\LaravelSes\\Factories\\Events\\Ses' . $eventName. 'Event';

        if (!class_exists($class)) {
            throw new InvalidArgumentException('Class '.$class.' not found in SES EventFactory!');
        }

        Log::debug("Event factory created " . $class);

        return new $class($modelName,$modelId);
    }
}
