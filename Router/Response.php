<?php
namespace Pet\Router;

use Pet\Errors\Errors;
use Pet\Request\Request;
use Pet\Tools\Tools;

class Response
{
    const TYPE_JSON = "Content-type: application/json;";
    private static $type = 'Content-type: text/html;';

    public static function redirect($path, $arg = []): void
    {
        header("Location: $path");
        $ret = (new Request());
        $ret->attribute = $arg;
        echo print_r($arg);
    }

    public static function die($data)
    {
        if (gettype($data) == 'string') {
            die($data);
        } else {
            if (self::$type == self::TYPE_JSON) {
                header(self::$type);
                die(json_encode($data));
            }
        }
    }
    public static function set($responseHeader): void
    { 
        Response::$type = $responseHeader;
    }
}