<?php

namespace Pet\Router;

use Pet\Router\Middleware;

class Router extends Middleware
{

    const PUBLIC_DIR = PUBLIC_DIR;
    static $Route = [];
    static $id = 0;

    public function __construct() {}


    static public function get($path, $callback): Router
    {
        Router::$Route[] = ["path" => $path, "method" => 'GET', "callback" => $callback];
        Router::$id = array_key_last(Router::$Route);
        return new Router();
    }

    public function name($string): Router
    {
        Router::$Route[Router::$id]['name'] = $string;
        return $this;
    }

    public function group($string): Router
    {
        Router::$Route[Router::$id]['group'] = $string;
        return $this;
    }

    static function init()
    {

        $request = request();
        $control = false;
        foreach (Router::$Route as $Rout) {

            if (
                $Rout['method'] == $request->getMethod()
                &&
                ($request->path == $Rout['path'] || Router::flexibleLink($Rout['path']))  ==  $request->path
            ) {
                if (key_exists('middleware', $Rout)) (new Essence())->open($Rout['middleware'], $request);
                (new Essence())->open($Rout['callback'], $request);

                $control = true;
            }

            if (!$control) http_response_code('404');
        }
    }

    static function flexibleLink($flex)
    {

        if (preg_match_all("|{([a-z]{1,})}|", $flex, $matches)) {

            $regular = $flex;

            foreach ($matches[0] as $name) {

                $regular = str_replace($name, "([a-zA-Z0-9?_-]{1,})", $regular);
            }

            if (preg_match("|$regular|", request()->path, $result, PREG_UNMATCHED_AS_NULL)) {

                foreach ($matches[1] as $key => $value) {
                    request()->parametr[$value] = $result[$key + 1];
                }
                return trim($result[0]);
            }
        }

        return false;
    }
}
