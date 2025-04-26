<?php
namespace Pet\Router;

use Pet\Request\Request;
use Pet\Tools\Tools;

class Response
{
    const TYPE_JSON = "Content-type: application\json;";

    static function redirect($path, $arg = [])
    {
        header("Location: $path");
        $ret = (new Request());
        $ret->attribute = $arg;
        echo print_r($arg);
    }

    public static function die($data, $type = self::TYPE_JSON)
    {
        if (gettype($data) == 'string') {
            die($data);
        } else {
            header($type);
            if ($type == self::TYPE_JSON) {
                die(json_encode($data));
            }
        }
    }
}