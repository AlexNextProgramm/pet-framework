<?php
namespace Pet\Cookie;

class Cookie{

    public function set($data = [], $path = '/', $secure= false, $http_only = false){
        foreach($data as $key => $value){
            setcookie($key, $value, time()+(3600*24*30), $path, "", $secure, $http_only);
        }
    }

    public function get($key = ''):null|string{
        if(array_key_exists($key, $_COOKIE)){
            return $_COOKIE[$key];
        }
        return null;
    }

    public function httpOnly($data = [], $path = '/'){
        ini_set('session.cookie_httponly', 1);
        $this->set($data, '/', true, true);
       
    }

    public function delete(string $name){
        setcookie($name, "", -1, '/');
    }
}