<?php

namespace Pet\Router;

use Pet\Router\HTTP;

class Error
{
    const STATUS_HTTP = HTTP::class;
    public static $events = [];

    public static function setHttp($code, $massange = null)
    {
        http_response_code($code);
        if (key_exists($code, self::$events)) {
            (new EssenceClass)->open(self::$events[$code], [$massange]);
        }
    }
}