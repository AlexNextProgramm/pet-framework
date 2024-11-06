<?php
namespace Pet\Router;

use Pet\Request\Request;

class Response
{


    static function redirect($path, $arg = [])
    {
        header("Location: $path");
        $ret = (new Request());
        $ret->attribute = $arg;
        echo print_r($arg);
    }
}