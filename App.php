<?php
namespace Pet;


use Pet\Errors\Errors;
use Pet\Router\Router;

class App{

    const PUBLIC_DIR = PUBLIC_DIR;
    public $router;


    public function __construct() {
        (new Errors());
        $this->ht_exits();
        $this->router = new Router();
    }

    function init($router_dir = self::PUBLIC_DIR."/router")
    {
        $this->include_router($router_dir);

                spl_autoload_register(function ($class) {

                    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';

                    if (file_exists($file)) {
                        require_once $file;
                        return true;
                    }

                    return false;
                });
        $this->router::init();
    }

    private function ht_exits(){
        if(!file_exists(self::PUBLIC_DIR.'/.htaccess')){
            file_put_contents(self::PUBLIC_DIR.'/.htaccess',
            "RewriteEngine On \nRewriteBase / \nRewriteCond %{REQUEST_FILENAME} !-f \nDirectoryIndex index.php \n ");
        }
    }

    private function include_router($path){

        if(!is_dir($path)) echo "Нет папки router";

        foreach (scandir($path) as $dir) {
            if ($dir == ".." || $dir == '.') continue;
                $dir = "$path/$dir";
                if (is_dir($dir)) {
                    $this->include_router($dir);
                }else{
                  
                    if (is_readable($dir)) {
                        if(array_reverse(explode('.', $dir))[0] == 'php'){
                           
                            include_once ($dir);
                        }
                    }
                }
        }
    }
    
    
}
?>