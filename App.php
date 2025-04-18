<?php
namespace Pet;


use Pet\Errors\Errors;
use Pet\Request\Request;
use Pet\Router\Router;

class App
{

    const PUBLIC_DIR = PUBLIC_DIR;
    public $router;
    public $request;

    public function __construct() {
        (new Errors());
        $this->htExits();
        $this->request = new Request();
        $this->router = new Router();
    }

    /**
     * init
     *
     * @param  mixed $router_dir
     * @return void
     */
    public static function init($router_dir = self::PUBLIC_DIR . "/router")
    {
        $GLOBALS['app'] = $app = new App();
        $app->includeRouter($router_dir);
        self::initProjectFile();
        $app->router::init();
    }

    private function htExits()
    {
        if (!file_exists(self::PUBLIC_DIR . '/.htaccess')) {
            file_put_contents(
                self::PUBLIC_DIR . '/.htaccess',
                "RewriteEngine On \nRewriteBase / \nRewriteCond %{REQUEST_FILENAME} !-f \nDirectoryIndex index.php \n "
            );
        }
    }

    private function includeRouter($path) {

        if (!is_dir($path)) echo "Not Folder router";

        foreach (scandir($path) as $dir) {
            if ($dir == ".." || $dir == '.') continue;
            $dir = "$path/$dir";
            if (is_dir($dir)) {
                $this->includeRouter($dir);
            } else {

                if (is_readable($dir)) {
                    if (pathinfo($dir, PATHINFO_EXTENSION) === 'php') {
                        include_once($dir);
                    }
                }
            }
        }
    }

    public static function initProjectFile()
    {
        spl_autoload_register(function ($class) {
            $file = str_replace('\\', DS, $class) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
            return false;
        });
    }
}

?>