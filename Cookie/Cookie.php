<?php
namespace Pet\Cookie;

class Cookie{
    
    /**
     * set
     *
     * @param  array $data
     * @param  string $path
     * @param  bool $secure
     * @param  bool $http_only
     * @return void
     */
    static function set($data = [], $path = '/', $secure= false, $http_only = false){
        foreach($data as $key => $value){
            setcookie($key, $value, time()+(3600*24*30), $path, "", $secure, $http_only);
        }
    }
    
    /**
     * get
     *
     * @param  string $key
     * @return null|string
     */
    static function get($key = ''):null|string{
        if(array_key_exists($key, $_COOKIE)){
            return $_COOKIE[$key];
        }
        return null;
    }
    
    /**
     * httpOnly
     *
     * @param  array $data
     * @param  string $path
     * @return void
     */
    static function httpOnly($data = [], $path = '/'){
        ini_set('session.cookie_httponly', 1);
        self::set($data,  $path , true, true);
       
    }
    
    /**
     * delete
     *
     * @param  string $name
     * @return void
     */
    static function delete(string $name){
        setcookie($name, "", -1, '/');
    }
}