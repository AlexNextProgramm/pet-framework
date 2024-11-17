<?php

namespace Pet\Router;

use Pet\Router\Router;

abstract class Middleware
{
    static $startIdRout;
    static $callback;
    
    /**
     * middleware
     *  
     * @param  mixed $callback
     * @return Router
     */
    static function middleware($callback): Router
    {
        Middleware::$callback = $callback;
        Middleware::$startIdRout = count(Router::$Route);
        return new Router();
    }

    public function set($Routes)
    {
        $countRoute = count(func_get_args()) + Middleware::$startIdRout;
        if($countRoute == 0 ) return;
        for ($i = Middleware::$startIdRout; $i < $countRoute; $i++) {
            Router::$Route[$i]['middleware'] =  Middleware::$callback;
        }
    }
}
