<?php

namespace Juhasev\LaravelSes\Factories;

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

        return new $class($modelName,$modelId);
    }
}
