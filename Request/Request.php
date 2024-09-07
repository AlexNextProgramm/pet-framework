<?php
namespace Pet\Request;

class Request{
    
    public function __construct() {
       
    }

    function getMethod():string
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    function getURI(){
        return str_contains($_SERVER['REQUEST_URI'], '?')? explode('?', $_SERVER['REQUEST_URI'])[0]:
        $_SERVER['REQUEST_URI'];
    }


    
}