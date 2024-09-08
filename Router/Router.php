<?php
namespace Pet\Router;

use Pet\Request\Request;
use Pet\Router\Middleware;

class Router extends Middleware{

    const PUBLIC_DIR = PUBLIC_DIR;
    static $Route = [];
    static $id = 0;

    public function __construct()
    {
       
    }


    static public function get($path, $callback):Router
    {
        Router::$Route[] = ["path" => $path, "method" => 'GET' , "callback" => $callback];
        Router::$id = array_key_last(Router::$Route);
        return new Router();
    }

    public function name($string):Router{
       Router::$Route[Router::$id]['name'] = $string;
        return $this;
    }

    public function group($string):Router{
        Router::$Route[Router::$id]['group'] = $string;
        return $this;
    }

    static function init(){

        $request =  new Request();
        $control = false;
        foreach(Router::$Route as $Rout){

            if($Rout['method'] == $request->getMethod() && $request->getURI() == $Rout['path']){
              if(empty($Rout['middleware'])) (new Essence())->open($Rout['middleware'], []);
              (new Essence())->open($Rout['callback'], []);

              $control = true;
            }

           if(!$control) http_response_code('404');
        }
    }

    

}