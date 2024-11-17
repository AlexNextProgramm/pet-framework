<?php

namespace Pet\Request;

use Pet\Tools\Tools;

class Request
{
    public $attribute = [];
    public $parametr = [];
    public $header = [];
    public $path;


    public function __construct()
    {
        $this->attribute = $this->input();
        $this->path = $this->getURI();
        $this->parsingHeaders();
    }

    function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }


    function getURI()
    {
        $path = str_contains($_SERVER['REQUEST_URI'], '?') ? explode('?', $_SERVER['REQUEST_URI'])[0] :
            $_SERVER['REQUEST_URI'];
        return $path != '/'? Tools::strRep(strlen($path) - 1, '', $path, '/'): $path;
        
    }

    
    /**
     * input
     *
     * @param  string|null $name
     * @return array|string|null
     */
    public function input(string|null $name = null): array|string|null
    {
        $REQUEST = $this->parsing();
        if (!$name) return $REQUEST;
        return key_exists($name, $REQUEST) ? $REQUEST[$name] : null;
    }


    private function parsing()
    {
        $REQUEST = array_merge($_GET, $_POST);
        $decode = [];
        $input = file_get_contents('php://input');
        if (key_exists('CONTENT_TYPE', $_SERVER)  == 'application/json' && !empty($input)) $decode = Tools::jsonDe($input);
        return array_merge($REQUEST, $decode);
    }
    
    /**
     * file
     *
     * @param  string $name
     * @return string|null|array
     */
    public function file(string $name = null): array|string|null
    {
        if (!$name) return $_FILES;
        if (key_exists($name, $_FILES)) return $_FILES[$name];
        return null;
    }

    private function parsingHeaders(){
        $header =  getallheaders();
        foreach($header as $key => $val) $this->header[strtolower($key)] = strtolower($val);
    }
}
