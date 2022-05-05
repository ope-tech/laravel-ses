<?php

namespace Juhasev\LaravelSes;

use Exception;
use Illuminate\Database\Eloquent\Model;

class ModelResolver
{
    /**
     * Resolve model name from config
     *
     * @param string $name
     * @throws Exception
     */
    public static function get($name)
    {
        $class = config('laravelses.models.'.ucfirst($name));

        if (! $class) {
            throw new Exception("Model ($name) could not be resolved");
        }

        return $class;
    }
}