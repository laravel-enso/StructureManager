<?php

namespace LaravelEnso\Cli\App\Services\Writers\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Helpers\App\Classes\Obj;

class Segments
{
    private static Collection $segments;
    private static bool $ucfirst;

    public static function get(bool $full = true)
    {
        $segments = $full ? self::$segments : self::$segments->slice(0, -1);

        return self::$ucfirst
            ? $segments->map(fn ($segment) => Str::ucfirst($segment))
            : $segments;
    }

    public static function count()
    {
        return self::$segments->count();
    }

    public static function set(?Obj $group)
    {
        if ($group) {
            self::$segments = new Collection(
                explode('.', $group->get('name'))
            );
        }
    }

    public static function ucfirst(bool $ucfirst = true)
    {
        self::$ucfirst = $ucfirst;
    }
}
