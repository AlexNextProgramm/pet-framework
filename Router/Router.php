<?php

namespace Pet\Router;

use Pet\Router\Middleware;

use function PHPSTORM_META\type;

class Router extends Middleware
{

    const PUBLIC_DIR = PUBLIC_DIR;
    public static $Route = [];
    public static $id = 0;

    public function __construct() {
    }


    public static function get($path, $callback): Router {
        Router::$Route[] = ["path" => $path, "method" => 'GET', "callback" => $callback];
        Router::$id = array_key_last(Router::$Route);
        return new Router();
    }

    public static function post($path, $callback): Router {
        Router::$Route[] = ["path" => $path, "method" => 'POST', "callback" => $callback];
        Router::$id = array_key_last(Router::$Route);
        return new Router();
    }

    public static function delete($path, $callback): Router {
        Router::$Route[] = ["path" => $path, "method" => 'DELETE', "callback" => $callback];
        Router::$id = array_key_last(Router::$Route);
        return new Router();
    }

    public static function put($path, $callback): Router {
        Router::$Route[] = ["path" => $path, "method" => 'PUT', "callback" => $callback];
        Router::$id = array_key_last(Router::$Route);
        return new Router();
    }

    public function name($string): Router {
        Router::$Route[Router::$id]['name'] = $string;
        return $this;
    }

    public function group($string): Router {
        Router::$Route[Router::$id]['group'] = $string;
        return $this;
    }

    public static function init()
    {

        $request = request();
        $control = false;

        foreach (Router::$Route as $Rout) {
            if ($Rout['method'] != $request->getMethod()) continue;
            if ($control) continue;

            // Проверка на гибкие ссылки
            $fLink = Router::flexibleLink($Rout['path']);
            $isFlexLink  = $fLink ? $fLink === $request->path : false;

            if ($request->path != $Rout['path'] && !$isFlexLink) continue;

            //Заглушка в middleware
            if (key_exists('middleware', $Rout)) {
                $resultMiddleware = (new EssenceClass())->open($Rout['middleware'], $request);
                if ($resultMiddleware === false) break;
            }

            //Прямое направление через action при ajax
            if (!empty($request->header['action'])) {
                $Rout['callback'][1] = $request->header['action'];
            }

            $controller = (new EssenceClass())->open($Rout['callback'], $request);

            // если контроллер что-то хочет вернуть
            if (!empty($controller) || gettype($controller) == 'array') echo json_encode($controller, JSON_UNESCAPED_UNICODE);
            $control = true;
        }

        // Если по итогу роутер не найден
        if (!$control) http_response_code('404');
    }

    private static function flexibleLink($flex)
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
