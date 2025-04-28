<?php

namespace Pet\Router;

use Pet\Router\Router;

abstract class Middleware
{
    public static $startIdRout;
    public static $callback;
    public static $isManyMiddle = false;
    /**
     * middleware
     * @param  mixed $callback
     * @return Router
     */
    public static function middleware(...$callback): Router
    {
        self::isMany($callback);
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
            if(self::$isManyMiddle){
                Router::$Route[$i]['isManyMiddle'] =  Middleware::$callback;
            }
        }
    }

    private static function isMany($data)
    {
        self::$isManyMiddle = count($data) > 2;
        if(!self::$isManyMiddle) $data = $data[0];
    }
}
