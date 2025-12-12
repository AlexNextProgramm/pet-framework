<?php

namespace Pet\Request;

use Pet\Tools\Tools;

class Request
{
    public static array $attribute = [];
    public static array $parametr = [];
    public $header = [];
    public $path;


    public function __construct()
    {
        self::$attribute = $this->input();
        $this->path = $this->getURI();
        $this->parsingHeaders();
    }

    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }


    public function getURI()
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
        if(!empty(self::$attribute)){
            return key_exists($name, self::$attribute) ? self::$attribute[$name]: self::$attribute;
        }
        $REQUEST = $this->parsing();
        if (!$name) return $REQUEST;
        return key_exists($name, $REQUEST) ? $REQUEST[$name] : null;
    }


    private function parsing()
    {
        $REQUEST = array_merge($_GET, $_POST, $_FILES);
        $decode = [];
        $input = file_get_contents('php://input');
        $ctype = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
        if (str_contains($ctype, 'json') && !empty($input)) $decode = Tools::jsonDe($input);
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


    public function ip(): string|false {
        $ip_keys = [
            'REMOTE_ADDR',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
        ];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return false;
    }
}
