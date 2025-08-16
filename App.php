<?php

namespace Pet;

use Pet\Errors\AppException;
use Pet\Errors\Errors;
use Pet\Request\Request;
use Pet\Router\Router;
use Pet\Session\Session;
use Pet\Tools\Tools;

class App
{

    public const PUBLIC_DIR = PUBLIC_DIR;
    public const ROUTER_DIR = PUBLIC_DIR . DS . 'router';
    public $router;
    public $request;
    public $session;

    public function __construct()
    {
        (new Errors());
        self::isHtaccess();
        $this->session = new Session();
        $this->request = new Request();
        $this->router = new Router();
    }

    /**
     * init
     *
     * @param  mixed $router_dir
     * @return void
     */
    public static function init()
    {
        $GLOBALS['app'] = new App();
        self::initClass();
        self::initRouter(self::ROUTER_DIR);
        Router::init();
    }

    /**
     * isHtaccess
     *
     * @return void
     */
    private static function isHtaccess(): void
    {
        if (!file_exists(self::PUBLIC_DIR . '/.htaccess')) {
            throw new AppException("Not file .htaccess", E_ERROR);
        }
    }

    /**
     * initRouter
     * Запуск Роутеров
     * @param  mixed $path
     * @return void
     */
    public static function initRouter(string $path): void
    {
        if (!is_dir($path)) {
            throw new AppException("Not Folder router", E_ERROR);
        }
        Tools::scan($path, function ($dir, $file) {
            if (!empty($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php'){
                include_once($file);
            }
            if (!empty($dir)) {
                self::initRouter($dir);
            }
        }, true);
    }

    /**
     * initClass
     * Запуск класоов проекта
     * @return void
     */
    public static function initClass()
    {
        spl_autoload_register(function ($class) {
            $file = str_replace('\\', DS, $class) . '.php';
            $file = self::PUBLIC_DIR.DS.$file;
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
            return false;
        });

        //Export external
        if (defined('EXTERNAL_MODULE')) {
            spl_autoload_register(function ($class) {
                foreach (explode("||", EXTERNAL_MODULE) as $module) {
                    $path = ROOT . DS . "$module". DS;

                    $file = $path.str_replace('\\', DS, $class) . '.php';
                    if (file_exists($file)) {
                        require_once $file;
                        return true;
                    }
                    return false;
                }
            });
        }
    }
}
