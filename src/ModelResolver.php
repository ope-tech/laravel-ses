<?php

namespace Juhasev\LaravelSes;

use Exception;
use Illuminate\Config\Repository;

class ModelResolver
{
    /**
     * Resolve model name from config
     *
     * @param string $name
     * @return Repository|mixed
     * @throws Exception
     */

    public static function get(string $name)
    {
        $class = config('laravelses.models.'.$name);

        if (!$class) {
            throw new Exception("Model ($name) could not be resolved");
        }

        return $class;
    }
}