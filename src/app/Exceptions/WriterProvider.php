<?php

namespace LaravelEnso\Cli\App\Exceptions;

use InvalidArgumentException;

class WriterProvider extends InvalidArgumentException
{
    public static function unknown(object $provider)
    {
        $class = get_class($provider);

        return new static("Unkonwn provider type: {$class}");
    }
}
