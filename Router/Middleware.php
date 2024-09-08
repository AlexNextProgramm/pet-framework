<?php

namespace Pet\Router;

use Pet\Router\Router;

abstract class Middleware
{
    static $idM;
    static $callback;

    static function middleware($callback): Router
    {
        Middleware::$callback = $callback;
        Middleware::$idM = Router::$id;
        return new Router();
    }

    public function setRoutes($Routes)
    {
        for ($i = Middleware::$idM; $i < (Router::$id + 1); $i++) {
            Router::$Route[$i]['middleware'] =  Middleware::$callback;
        }
    }
}
