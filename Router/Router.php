<?php

namespace Pet\Router;

use Pet\Request\Request;
use Pet\Router\Middleware;

use function PHPSTORM_META\type;

class Router extends Middleware
{

    const PUBLIC_DIR = PUBLIC_DIR;
    public static $Route = [];
    public static int $id = 0;
    public static $event = [];
    private static $ajaxtype = ['POST'];
    public function __construct() {
    }


    public static function get($path, ...$callback): Router {
        Router::$Route[] = ["path" => $path, "method" => 'GET', "callback" => $callback];
        Router::$id = array_key_last(Router::$Route);
        return new Router();
    }

    public static function post($path, ...$callback): Router {
        Router::$Route[] = ["path" => $path, "method" => 'POST', "callback" => $callback];
        Router::$id = array_key_last(Router::$Route);
        return new Router();
    }

    public static function delete($path, ...$callback): Router {
        Router::$Route[] = ["path" => $path, "method" => 'DELETE', "callback" => $callback];
        Router::$id = array_key_last(Router::$Route);
        return new Router();
    }

    public static function put($path, ...$callback): Router {
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
             //Прямое направление через event при ajax
            $resultAjax = 'Нет ответных данных';
            if (in_array($request->getMethod(), self::$ajaxtype) && self::ajax($request, $resultAjax)) {
                Response::die($resultAjax);
            }
            if ($Rout['method'] != $request->getMethod()) continue;
            if ($control) continue;

            // Проверка на гибкие ссылки
            if (self::isLinkRouter($request,$Rout)) continue;

            //Заглушка в middleware
            if (key_exists('middleware', $Rout)) {
                if (key_exists('isManyMiddle', $Rout)) {

                    foreach ($Rout['middleware'] as $middleware) {
                        $resultMiddleware = (new EssenceClass())->open($middleware, $request);
                        if ($resultMiddleware === false) break;
                    }
                } else {
                    $resultMiddleware = (new EssenceClass())->open($Rout['middleware'], $request);
                }
                if ($resultMiddleware === false) break;
            }
            $controller = [];
            if (count($Rout['callback']) == 1) {
                $controller[] = (new EssenceClass())->open($Rout['callback'][0], $request);
            } elseif (count($Rout['callback']) > 1) {
                foreach ($Rout['callback'] as $callbackRout) {
                    $controller[] = (new EssenceClass())->open($callbackRout, $request);
                }
            }

            // если контроллер что-то хочет вернуть
            if (gettype($controller) == 'array') {
                $controller = array_diff($controller, [null, false, '']);
            }
            if (!empty($controller)){
                echo json_encode((count($controller) == 1 ? $controller[0] : $controller), JSON_UNESCAPED_UNICODE);
            }
            $control = true;
        }

        // Если по итогу роутер не найден
        if (!$control) http_response_code('404');
    }

    private static function ajax($request, &$result) : bool
    {
        // Проверка роутера на ложный запрос. ajax не может выполнен 
        foreach (Router::$Route as $Rout) {
            if ($Rout['method'] != "GET") continue;
            if (self::isLinkRouter($request, $Rout)) continue;
            foreach (self::$event as $key => $action) {
                if (!empty($request->header[$key])) {
                    $result = (new EssenceClass())->open($action, $request);
                    return true;
                }
            }
        }
        return false;
    }

    private static function isLinkRouter($request, $Rout):bool
    {
        $fLink = Router::flexibleLink($Rout['path']);
        $isFlexLink  = $fLink ? $fLink === $request->path : false;
        return $request->path != $Rout['path'] && !$isFlexLink;
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
                    Request::$parametr[$value] = $result[$key + 1];
                }
                return trim($result[0]);
            }
        }

        return false;
    }
}
