<?php

namespace Pet\Request;

use Pet\Tools\Tools;

class Request
{
    public $attribute = [];
    public $parametr = [];
    public $path;


    public function __construct()
    {
        $this->attribute = $this->input();
        $this->path = $this->getURI();
    }

    function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }


    function getURI()
    {
        $path = str_contains($_SERVER['REQUEST_URI'], '?') ? explode('?', $_SERVER['REQUEST_URI'])[0] :
            $_SERVER['REQUEST_URI'];
        return Tools::strRep(strlen($path) -1, '',$path, '/');
    }


    public function input(string|null $name = null): array|null
    {
        $REQUEST = $this->parsing();
        if (!$name) return $REQUEST;
        return key_exists($name, $REQUEST) ? $REQUEST[$name] : null;
    }


    private function parsing()
    {
        $REQUEST = array_merge($_GET, $_POST);
        $decode = [];
        if (key_exists('CONTENT_TYPE', $_SERVER) == 'application/json') $decode = Tools::jsonDe(file_get_contents('php://input'));
        return array_merge($REQUEST, $decode);
    }

    public function file(string $name = null)
    {
        if (!$name) return $_FILES;
        if (key_exists($name, $_FILES)) return $_FILES[$name];
        return null;
    }
}
