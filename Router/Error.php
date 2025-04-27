<?php

namespace Pet\Router;

class Error
{
    const FORBIDDEN = 403;
    public static $events = [];

    public static function setHttp($code, $massange)
    {
        http_response_code($code);
        if (key_exists($code, self::$events)) {
            (new EssenceClass)->open(self::$events[$code], $massange);
        }
    }
}