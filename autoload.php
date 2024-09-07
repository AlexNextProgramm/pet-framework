<?php

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
            $file = str_replace('Pet/','', $file);
            if (file_exists(__DIR__.'/'.$file)) {
                require_once __DIR__.'/'.$file;
                return true;
            }
           
            return false;
        });
    }
}
Autoloader::register();

include_once(__DIR__.'/function.php');
?>