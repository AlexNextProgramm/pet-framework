<?php

namespace Pet\Router;

use Pet\Router\HTTP;

class Error
{
    const STATUS_HTTP = HTTP::class;
    public static $events = [];

    public static function setHttp(int $code, ?string $massange = null): void
    {
        Header::status($code);
        if (key_exists($code, self::$events)) {
            (new Invoker())->call(self::$events[$code], $massange);
        }
    }
}